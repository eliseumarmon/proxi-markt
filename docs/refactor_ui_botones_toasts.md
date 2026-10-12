# Refactor de botones y notificaciones reutilizables

## Identificación del cambio

- Rama: `refactor/ui-components`
- Commit: `7054e46 refactor(ui): extrae botones y notificaciones reutilizables`
- Alcance: frontend Vue/Vite.
- Archivos incluidos en el commit: 20.
- Balance del commit: 609 líneas añadidas y 821 eliminadas.
- Dependencias nuevas: ninguna.

El objetivo del cambio es eliminar implementaciones visuales y de estado repetidas, crear una API coherente para las acciones de usuario y disponer de un único sistema global de notificaciones.

## Resumen exacto

Se han realizado estos cambios:

1. Se ha creado el componente reutilizable `BaseButton`.
2. Se han migrado 31 botones distribuidos entre 13 componentes.
3. Se ha creado un sistema global de notificaciones basado en `useToast` y `ToastViewport`.
4. Se han eliminado las 11 implementaciones locales de toast existentes.
5. Se han unificado 43 mensajes mediante `toast.success()`, `toast.error()`, `toast.warning()` y `toast.info()`.
6. Se han sustituido los tres usos de `alert()` que existían en el flujo de publicación.
7. Se han creado tokens CSS compartidos para colores, radios, sombras y foco.
8. Se ha añadido un estado específico de publicación para impedir envíos repetidos mientras se crea un producto.
9. Se ha corregido la alineación vertical del icono, el texto y el cierre de los toasts.
10. Se ha eliminado CSS local duplicado de botones y notificaciones en los componentes migrados.

## 1. Componente `BaseButton`

Archivo creado: `frontend/src/components/ui/BaseButton.vue`.

### Qué hace

El componente encapsula el marcado, los estados, la accesibilidad y el aspecto visual de un botón de acción.

API disponible:

| Propiedad | Valores | Valor inicial | Finalidad |
|---|---|---|---|
| `variant` | `primary`, `secondary`, `danger`, `ghost` | `primary` | Define la importancia y el significado visual. |
| `size` | `sm`, `md`, `lg` | `md` | Controla altura, padding y tamaño de texto. |
| `type` | `button`, `submit`, `reset` | `button` | Conserva la semántica HTML del botón. |
| `loading` | booleano | `false` | Muestra un indicador y bloquea nuevas pulsaciones. |
| `disabled` | booleano | `false` | Deshabilita la interacción. |
| `block` | booleano | `false` | Hace que el botón ocupe todo el ancho disponible. |

También expone los slots `default`, `icon-left` e `icon-right`.

### Cómo se ha implementado

- El elemento raíz sigue siendo un `<button>` real.
- El tipo predeterminado es `button` para evitar envíos accidentales cuando se usa dentro de un formulario.
- El atributo `disabled` se activa cuando `disabled` o `loading` son verdaderos.
- Durante una operación se añade `aria-busy` y se muestra un spinner CSS.
- Los atributos y listeners como `class`, `title`, `aria-label` y `@click` llegan al elemento raíz mediante el comportamiento normal de Vue.
- El foco de teclado utiliza `:focus-visible` y el token `--focus-ring`.
- Las animaciones respetan `prefers-reduced-motion`.
- Los estilos visuales viven dentro del componente; cada pantalla conserva solamente reglas de layout como `flex`, ancho o margen.

### Por qué se ha hecho así

- Mantiene la semántica y accesibilidad nativas.
- Evita repetir estados `hover`, `disabled`, `loading` y `focus` en cada pantalla.
- Reduce el riesgo de dobles envíos.
- Permite cambiar el aspecto general desde un único archivo.
- Una API reducida evita convertir el componente en una abstracción difícil de mantener.

### Ejemplos resultantes

```vue
<BaseButton type="submit" size="lg" block :loading="loading">
    Guardar
</BaseButton>

<BaseButton variant="danger" size="sm" @click="eliminar">
    Eliminar
</BaseButton>
```

## 2. Estado global de notificaciones

Archivo creado: `frontend/src/composables/useToast.js`.

### Qué hace

Expone una API única para publicar y cerrar notificaciones desde cualquier componente:

```js
const toast = useToast();

toast.success('Producto actualizado');
toast.error('No se pudo guardar');
toast.warning('Revisa los campos obligatorios');
toast.info('Producto eliminado de favoritos');
```

También admite la API general:

```js
toast.show({
    message: 'Mensaje',
    title: 'Título opcional',
    type: 'info',
    duration: 4000
});
```

### Cómo se ha implementado

- El estado reactivo vive en el módulo, fuera de los componentes, por lo que todas las llamadas comparten la misma cola.
- El consumidor recibe la colección como solo lectura.
- Cada notificación obtiene un identificador incremental.
- La duración predeterminada es de 4 segundos.
- Una duración igual a `0` crea una notificación persistente.
- Los temporizadores se guardan en un `Map` y se limpian al cerrar cada notificación.
- La cola muestra un máximo de tres elementos; al añadir un cuarto se elimina el más antiguo.
- `dismiss(id)` permite el cierre programático y manual.
- Los tipos desconocidos se normalizan a `info`.
- Los mensajes vacíos no generan una notificación.

### Por qué se ha hecho así

- El proyecto no necesita Pinia únicamente para gestionar notificaciones.
- El estado compartido conserva los mensajes durante cambios de ruta.
- La cola evita que un mensaje sobrescriba a otro.
- Centralizar los temporizadores elimina once copias de la misma lógica con `ref()` y `setTimeout()`.
- El límite de tres evita cubrir una parte excesiva de la interfaz.

## 3. Renderizado global de toasts

Archivo creado: `frontend/src/components/ui/ToastViewport.vue`.

### Qué hace

Renderiza la cola global y proporciona diseño, transiciones, iconos, cierre manual y comportamiento responsive.

### Cómo se ha implementado

- Se monta una sola vez desde `App.vue`.
- Usa `Teleport` para renderizarse directamente en `body`, evitando problemas de recorte por `overflow` o contextos de apilado de las vistas.
- Usa `TransitionGroup` para animar entrada, salida y movimiento de la cola.
- Utiliza Bootstrap Icons, que ya era una dependencia del proyecto.
- Asigna un icono distinto a éxito, error, aviso e información.
- Los errores usan `role="alert"`; el resto utiliza `role="status"`.
- El contenedor usa `aria-live="polite"` y el botón de cierre tiene un nombre accesible.
- Solo las tarjetas aceptan interacción; el contenedor no bloquea elementos situados detrás.
- En escritorio aparece abajo a la derecha y en móvil conserva 16 píxeles respecto a ambos laterales.
- El icono tiene una caja fija de `1.25rem`, `line-height: 1` y centrado flex para alinearse con el bloque de texto.
- La cuadrícula alinea verticalmente el icono, el contenido y el cierre.
- Las transiciones se desactivan cuando el sistema solicita movimiento reducido.

### Por qué se ha hecho así

- Un único viewport evita crear una instancia por pantalla.
- `Teleport` hace que la notificación sea independiente del layout de la ruta actual.
- Los roles permiten que lectores de pantalla diferencien errores urgentes de mensajes informativos.
- La alineación mediante grid funciona tanto con mensajes de una línea como con títulos o textos largos.

## 4. Tokens visuales

Archivo creado: `frontend/src/styles/tokens.css`.

El archivo define variables compartidas para:

- Verde principal, verde oscuro y color de acento.
- Colores de error, aviso e información.
- Texto principal, texto secundario, superficie y borde.
- Radios pequeño, medio y grande.
- Sombra de las notificaciones.
- Anillo de foco.

Se importa desde `frontend/src/main.js`, después de los estilos de Bootstrap, Bootstrap Icons y Leaflet.

La finalidad es evitar que valores como `#4CA626` continúen duplicándose en decenas de componentes y proporcionar una base para futuros componentes UI.

## 5. Integración global

### `frontend/src/App.vue`

- Se importa `ToastViewport`.
- Se conserva `router-view`.
- Se añade un único `<ToastViewport />` como hermano del contenido de la ruta.

Esto permite mostrar una notificación desde cualquier pantalla y conservarla si la operación navega a otra ruta.

### `frontend/src/main.js`

- Se importa `./styles/tokens.css`.
- No se ha registrado globalmente `BaseButton`; cada componente lo importa explícitamente.

Los imports explícitos hacen visibles las dependencias de cada componente y evitan registrar elementos que una pantalla no utiliza.

## 6. Migración exacta por componente

### `AnadirCategoria.vue`

- Se elimina el estado local del toast, su temporizador, su plantilla y su CSS.
- La creación correcta usa `toast.success()`.
- El error del backend usa `toast.error()`.
- Se corrigen las tildes visibles de “Categoría”.
- El botón de guardar se convierte en `BaseButton` de tipo `submit`.
- El CSS local queda limitado a la colocación del botón.

### `Comandas.vue`

- Se eliminan el toast local y sus estilos.
- Los errores de actualización y valoración usan `toast.error()`.
- La valoración correcta usa `toast.success()`.
- Los botones Finalizar, Aceptar, Rechazar y Valorar se migran a `BaseButton`.
- Rechazar usa la variante `danger`; el resto usa la variante principal y tamaño pequeño.
- Se elimina el CSS duplicado de `.btn-accion`, `.aceptar`, `.finalizar`, `.rechazar` y `.valorar`.

### `DetalleProducto.vue`

- Se elimina el toast local y su CSS.
- La solicitud correcta y el alta de favorito usan `success`.
- Los errores de solicitud y favoritos usan `error`.
- La compra creada sin poder enviar el mensaje usa `warning` porque la operación principal sí se completó.
- La retirada de favoritos usa `info`, ya que no representa un error.

### `EditarProducto.vue`

- Se elimina el toast local y su CSS.
- Actualización correcta y error usan `success` y `error` respectivamente.
- Cancelar se convierte en `BaseButton secondary`.
- Actualizar se convierte en `BaseButton` de tipo `submit`, tamaño grande y estado `loading`.
- Se elimina el CSS visual duplicado de ambos botones y solo se conserva su reparto flexible en el formulario.

### `Incidencias.vue`

- Se elimina el toast local y su CSS.
- Envío correcto y fallo usan `success` y `error`.
- El botón de envío se convierte en `BaseButton` de tipo `submit`.

### `IncidenciasAdmin.vue`

- Se elimina el toast local y su CSS.
- Los errores de carga y actualización usan `error`.
- La revisión correcta usa `success`.
- Guardar se convierte en `BaseButton` pequeño.

### `LoginForm.vue`

- Se elimina el toast local y su CSS.
- Los campos incompletos usan `warning`.
- Las credenciales incorrectas usan `error`.
- El botón de acceso usa `BaseButton`, tipo `submit`, tamaño grande, ancho completo y estado de carga.
- Se elimina todo el CSS visual específico del antiguo botón.

### `MiCuenta.vue`

- Se elimina el toast local y su CSS.
- Se clasifican siete mensajes entre `success`, `error` y `warning` para creación, validación y eliminación de puntos y productos.
- Crear un punto, Guardar, Cerrar y Borrar se migran a `BaseButton`.
- Borrar usa `danger`; Cerrar usa `secondary`.
- Se eliminan los estilos duplicados de confirmar, cancelar y borrar.
- El `router-link` para cambiar la ubicación se mantiene como enlace.
- Las cinco pestañas se conservan como botones nativos porque pertenecen a una futura abstracción de pestañas, no a botones de acción normales.

### `Principal.vue`

- Los CTA “Comenzar ahora” y “Crear cuenta gratis” se migran a `BaseButton` grande.
- El segundo usa la variante secundaria para conservar el contraste sobre el fondo verde.
- Se elimina el CSS duplicado de ambos CTA.

### `Publicar.vue`

- Se elimina el toast local y su CSS.
- Se sustituyen los tres `alert()` nativos por notificaciones `warning` o `error`.
- Se normalizan también los mensajes de producto creado, error de creación y error al cargar puntos.
- Se migran seis acciones a `BaseButton`: crear punto, crear uno ahora, cancelar publicación, publicar, cancelar modal y guardar punto.
- Se usan las variantes `ghost`, `secondary` y principal según la importancia de cada acción.
- Guardar punto refleja `cargandoNuevoPunto` mediante `loading`.
- Se añade `publicando`, separado de `cargando`, para representar exclusivamente el envío del producto.
- `publicando` se activa antes de la petición y se restablece en `finally`, tanto si la petición funciona como si falla.
- El botón queda bloqueado durante el envío, evitando publicaciones duplicadas.
- Se elimina el CSS visual de los seis botones migrados.

### `RegistroForm.vue`

- Se elimina el toast local y su CSS.
- Las tres validaciones del formulario usan `warning`.
- El alta correcta usa `success` y el fallo de conexión usa `error`.
- El botón de registro usa `BaseButton`, tipo `submit`, tamaño grande, ancho completo y estado de carga.
- Se elimina el CSS visual duplicado del botón.

### `SolicitarCompra.vue`

- Limpiar se convierte en `BaseButton secondary` de tipo `reset`.
- Enviar solicitud se convierte en `BaseButton` de tipo `submit`.
- Se conserva la condición que deshabilita la compra de un producto propio.
- Las proporciones `flex: 1` y `flex: 2` permanecen como reglas locales de layout.
- Se elimina el CSS visual de ambos botones.

### `Ubicacion.vue`

- Se elimina el toast local y su CSS.
- Validación de dirección y falta de geolocalización usan `warning`.
- Guardado correcto usa `success`.
- Errores de guardado y geolocalización usan `error`.
- Cancelar, Mi ubicación, Guardar y Detectar ubicación se migran a `BaseButton`.
- Cancelar usa `danger`, Mi ubicación usa `secondary` y las acciones principales usan la variante predeterminada.
- Guardar utiliza `loading` durante la operación.
- Se conserva únicamente el CSS local necesario para flex, ancho mínimo y margen.

### `ValoracionForm.vue`

- Enviar y Volver se migran a `BaseButton` de ancho completo.
- Enviar conserva el bloqueo hasta que exista una puntuación.
- Volver usa la variante secundaria y tamaño pequeño.
- Se elimina el CSS visual duplicado de ambos botones.

## 7. CSS eliminado

Se han eliminado bloques repetidos que definían de nuevo:

- Gradientes verdes.
- Colores de peligro.
- Radios y padding de botones.
- Estados `hover`, `active` y `disabled`.
- Cursores y transiciones.
- Posición fija, fondo, sombra y animación de toasts.
- Animaciones `subida` duplicadas.

El resultado neto del commit elimina 212 líneas más de las que añade, aun incorporando cuatro archivos reutilizables completos.

## 8. Accesibilidad incorporada

- Botones basados en HTML semántico.
- Tipo `button` seguro por defecto.
- Estado `disabled` real durante carga o bloqueo.
- `aria-busy` durante operaciones asíncronas.
- Foco visible por teclado.
- Botón de cierre de toast con `aria-label`.
- `role="alert"` para errores y `role="status"` para el resto.
- Región viva para anunciar notificaciones.
- Compatibilidad con reducción de movimiento.

## 9. Comprobaciones realizadas

Se ejecutaron estas comprobaciones antes del commit:

1. `npm run build`: compilación de producción correcta con Vite.
2. Transformación correcta de 197 módulos.
3. Prueba directa del composable con Node:
   - Inserción de cuatro notificaciones.
   - Verificación del límite de tres.
   - Verificación de eliminación de la más antigua.
   - Verificación de cierre manual.
4. `git diff --check`: sin errores de espacios ni conflictos de formato.
5. Búsqueda global:
   - Cero implementaciones locales con `toastVisible`, `toastMensaje`, `lanzarToast` o `.toast-notificacion`.
   - Cero llamadas a `alert()`.

No se añadieron tests automatizados con Vitest porque el proyecto todavía no tiene configurada una infraestructura de tests frontend.

## 10. Decisiones de alcance

### No se añadió Pinia

El sistema solo necesita una pequeña cola global. Un módulo con estado reactivo compartido ofrece el comportamiento necesario sin sumar una dependencia ni una capa arquitectónica adicional.

### No se registró `BaseButton` globalmente

Los imports explícitos facilitan saber qué componentes dependen de él y mantienen clara la frontera entre elementos UI y componentes de dominio.

### No se convirtieron todos los botones nativos

Quedan 21 botones nativos de aplicación. Varios no son simples acciones y deben tratarse con una abstracción específica:

- Paginadores de productos, compras y ventas.
- Pestañas de Mi Cuenta.
- Navegación y menú móvil.
- Controles propios de `ModalRadio`.
- Botones de icono.
- Envío del chat.

Forzarlos dentro de `BaseButton` mezclaría responsabilidades y dificultaría la accesibilidad de patrones como tabs y paginación.

### No se sustituyeron los `confirm()`

Permanecen dos confirmaciones nativas:

- Eliminación de un punto de entrega en `MiCuenta.vue`.
- Salida con cambios sin guardar en `Ubicacion.vue`.

Requieren un `ConfirmDialog` o `BaseModal` con control de foco, cierre con Escape, backdrop y resultado asíncrono. Se han dejado fuera para mantener este commit centrado en botones y notificaciones.

### No se modificó el backend

No se han cambiado endpoints, contratos de API, autenticación, modelos ni lógica Laravel. El refactor conserva los flujos funcionales existentes y se limita a presentación y feedback del frontend.

## 11. Trabajo posterior

El seguimiento detallado se mantiene en `docs/tareas_pendientes_ui.md`. Los siguientes bloques previstos son:

1. Revisión visual responsive de todas las variantes.
2. Tests con Vitest y Vue Test Utils.
3. `ConfirmDialog` reutilizable.
4. `BasePagination`.
5. Componente accesible para las pestañas de Mi Cuenta.
6. Revisión de botones de icono y navegación.

La lista de tareas y este documento se crearon después del commit `7054e46`; por tanto, no forman parte de dicho commit salvo que se añadan explícitamente en un commit de documentación posterior.
