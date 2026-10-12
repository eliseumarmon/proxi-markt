# Cambios de seguridad y reglas de dominio

Rama: `fix/security-domain-rules`  
Commit: `60c6991 fix(security): refuerza reglas de dominio`

## Objetivo

Esta tanda de cambios empieza la primera prioridad del roadmap: cerrar reglas de autorizacion y dominio en backend antes de seguir con funcionalidades nuevas.

El problema general era que varias operaciones confiaban demasiado en datos enviados por el frontend o no verificaban suficientemente que el usuario autenticado pudiera actuar sobre el recurso.

## Archivos modificados

- `backend/app/Http/Controllers/ProductoController.php`
- `backend/app/Http/Controllers/CompraVentaController.php`
- `backend/app/Http/Controllers/ChatController.php`
- `backend/app/Http/Controllers/PuntoEntregaController.php`
- `backend/app/Http/Controllers/UserController.php`
- `backend/app/Http/Controllers/ValoracionController.php`
- `backend/tests/Feature/SecurityDomainRulesTest.php`

## Productos

Antes, un usuario autenticado podia intentar actualizar o eliminar productos de otro usuario si conocia el ID. Tambien podia enviar un `id_puntoentrega` que no le perteneciera.

Ahora el backend comprueba propietario antes de modificar:

```php
if ((int) $producto->id_usuario !== (int) $request->user()->id) {
    return response()->json(['message' => 'No autorizado para actualizar este producto.'], 403);
}
```

Tambien se valida que el punto de entrega pertenezca al usuario autenticado:

```php
if (!PuntoEntrega::where('id', $data['id_puntoentrega'])
    ->where('id_usuario', $request->user()->id)
    ->exists()) {
    return response()->json([
        'message' => 'El punto de entrega seleccionado no pertenece al usuario autenticado.'
    ], 422);
}
```

Ademas, al reemplazar imagen se evita borrar la imagen por defecto:

```php
if ($producto->imagen && $producto->imagen !== 'productos/default.png') {
    Storage::disk('public')->delete($producto->imagen);
}
```

## Compraventas

El cambio mas importante esta en la creacion de reservas. Antes el frontend enviaba `id_vendedor` e `id_punto`, y el backend los aceptaba tras validarlos como existentes. Eso permitia manipular una compra para asociarla a otro vendedor o a otro punto de entrega.

Ahora el backend solo acepta datos propios de la solicitud:

```php
$datosValidados = $request->validate([
    'fecha_prevista' => 'required|date',
    'cantidad' => 'required|integer|min:1',
]);
```

El vendedor, producto, punto y precio se derivan del producto real:

```php
$compraVenta = CompraVenta::create([
    'id_comprador' => $compradorId,
    'id_vendedor' => $productoBloqueado->id_usuario,
    'id_producto' => $productoBloqueado->id,
    'id_punto' => $productoBloqueado->id_puntoentrega,
    'cantidad' => $datosValidados['cantidad'],
    'precio' => $productoBloqueado->precio,
    'fecha_prevista' => $datosValidados['fecha_prevista'],
]);
```

La fila del producto se bloquea durante la reserva para reducir problemas de concurrencia con el stock:

```php
$productoBloqueado = Producto::whereKey($producto->id)
    ->lockForUpdate()
    ->firstOrFail();
```

Tambien se impide comprar un producto propio:

```php
if ((int) $productoBloqueado->id_usuario === (int) $compradorId) {
    return response()->json([
        'message' => 'No puedes comprar tu propio producto.'
    ], 422);
}
```

Y se comprueba el stock real dentro de la transaccion:

```php
if ((int) $datosValidados['cantidad'] > (int) $productoBloqueado->stock_real) {
    return response()->json([
        'message' => 'No hay stock suficiente para reservar esa cantidad.'
    ], 422);
}
```

## Estados de compraventa

Antes cualquier usuario autenticado podia intentar cambiar el estado de una compraventa si conocia el ID. Ahora solo pueden hacerlo comprador o vendedor:

```php
if ((int) $compraventa->id_comprador !== (int) $userId
    && (int) $compraventa->id_vendedor !== (int) $userId) {
    return response()->json(['message' => 'No autorizado para actualizar esta compraventa.'], 403);
}
```

Tambien se evita repetir el mismo estado para no duplicar efectos sobre stock:

```php
if ($nuevoEstado === $estadoActual) {
    return response()->json(['message' => 'La compraventa ya está en ese estado.'], 200);
}
```

Las transiciones permitidas quedan centralizadas:

```php
private function transicionPermitida(CompraVenta $compraventa, string $nuevoEstado, int $userId): bool {
    $esVendedor = (int) $compraventa->id_vendedor === (int) $userId;
    $esComprador = (int) $compraventa->id_comprador === (int) $userId;

    return match ($compraventa->estado) {
        'pendiente' => ($esVendedor && in_array($nuevoEstado, ['en curso', 'cancelado'], true))
            || ($esComprador && $nuevoEstado === 'cancelado'),
        'en curso' => ($esVendedor && in_array($nuevoEstado, ['completado', 'cancelado'], true))
            || ($esComprador && $nuevoEstado === 'cancelado'),
        'completado' => $nuevoEstado === 'valorado',
        default => false,
    };
}
```

## Stock

La actualizacion de stock al completar o cancelar se hace con bloqueo de fila:

```php
$producto = Producto::whereKey($compraventa->id_producto)
    ->lockForUpdate()
    ->firstOrFail();
```

Esto ayuda a evitar inconsistencias si dos peticiones relacionadas con el mismo producto llegan casi a la vez.

## Chat

El marcado de mensajes como leidos ahora exige que el usuario participe en el chat:

```php
$chat = Chat::findOrFail($id);

if ((int) $chat->id_comprador !== (int) $usuarioId
    && (int) $chat->id_vendedor !== (int) $usuarioId) {
    return response()->json(['error' => 'No autorizado'], 403);
}
```

## Ubicacion de usuario

Antes la ruta de ubicacion podia actualizar el usuario recibido por ruta. Ahora solo se puede actualizar la ubicacion propia:

```php
if ((int) $request->user()->id !== (int) $usuario->id) {
    return response()->json(['message' => 'No autorizado para actualizar esta ubicación.'], 403);
}
```

Se añadieron reglas de validacion para coordenadas:

```php
$request->validate([
    'direccion' => 'nullable|string|max:255',
    'latitud' => 'required|numeric|between:-90,90',
    'longitud' => 'required|numeric|between:-180,180',
]);
```

## Puntos de entrega

La consulta de puntos de entrega de un usuario ahora comprueba que el usuario autenticado coincida con el usuario de la ruta:

```php
if ((int) $request->user()->id !== (int) $usuario->id) {
    return response()->json(['message' => 'No autorizado para consultar estos puntos de entrega.'], 403);
}
```

## Valoraciones

El `StoreValoracionRequest` ya comprobaba que el usuario participara en la compraventa. Se añadieron dos reglas de dominio:

1. Solo se pueden valorar compraventas completadas.
2. Cada usuario solo puede valorar una vez la misma compraventa.

```php
if ($compraventa->estado !== 'completado') {
    return response()->json([
        'message' => 'Solo se pueden valorar compraventas completadas.'
    ], 422);
}
```

```php
$valoracionExistente = Valoracion::where('id_venta', $compraventa->id)
    ->where('id_valorador', $idEmisor)
    ->exists();

if ($valoracionExistente) {
    return response()->json([
        'message' => 'Ya has valorado esta compraventa.'
    ], 422);
}
```

## Tests añadidos

Se creo `backend/tests/Feature/SecurityDomainRulesTest.php` con pruebas de feature centradas en las reglas nuevas.

El test fuerza SQLite en memoria para no tocar la base local:

```php
config([
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => ':memory:',
]);

DB::purge('sqlite');
DB::reconnect('sqlite');
```

Casos cubiertos:

```php
public function test_user_cannot_update_another_users_product(): void
```

Comprueba que un usuario no puede modificar productos ajenos.

```php
public function test_purchase_uses_product_seller_and_point_instead_of_client_payload(): void
```

Comprueba que la compraventa ignora `id_vendedor` e `id_punto` enviados por el cliente y usa los datos reales del producto.

```php
public function test_purchase_can_only_be_rated_once_after_completion(): void
```

Comprueba que no se puede valorar antes de completar la compraventa y que no se puede valorar dos veces.

## Verificacion

Comandos ejecutados:

```bash
php -l backend/app/Http/Controllers/ProductoController.php
php -l backend/app/Http/Controllers/CompraVentaController.php
php -l backend/app/Http/Controllers/UserController.php
php -l backend/app/Http/Controllers/PuntoEntregaController.php
php -l backend/app/Http/Controllers/ChatController.php
php -l backend/app/Http/Controllers/ValoracionController.php
php -l backend/tests/Feature/SecurityDomainRulesTest.php
git diff --check
docker compose exec php php artisan test
```

Resultado final:

```text
Tests: 5 passed (10 assertions)
```

## Incidencia detectada durante los tests

Al ejecutar por primera vez los tests dentro del contenedor, PHPUnit estaba usando la MySQL local del proyecto en lugar de SQLite en memoria. Ese intento afecto a las tablas `compraventas` y `valoraciones`.

Se repararon recreandolas con el esquema de `docker/database/base.sql` y se comprobo que existen de nuevo.

Despues de eso, el test nuevo quedo aislado forzando SQLite en memoria desde su propio `setUp`, para evitar que vuelva a tocar la base local.

## Pendiente relacionado

- Añadir mas tests de autorizacion para incidencias, categorias, favoritos y puntos de entrega.
- Revisar si el estado `valorado` debe seguir existiendo o si basta con `completado` + `ya_valorado`.
- Sustituir respuestas de error genericas por respuestas consistentes mediante excepciones o `FormRequest`.
- Extraer reglas de transicion de compraventas a una clase de dominio si el flujo crece con pagos, incidencias o chat por pedido.
