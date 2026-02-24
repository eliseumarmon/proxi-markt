# Documentación del Backend (Laravel)

Este documento detalla la estructura, las reglas de negocio y los Endpoints de la API REST que conforma el Backend de Proxi Markt.

---

## 1. Patrón de arquitectura

El Backend de la aplicación está construido con **Laravel 11** y opera exclusivamente como una **API REST Stateless** (Sin estado).

* **Autenticación con Token (Sanctum):** La aplicación no utiliza cookies de sesión estandar de PHP. La seguridad recae sobre `Laravel Sanctum`. Tras un login exitoso, la API emite un *Bearer Token* (JWT) que el cliente debe incluir en las cabeceras HTTP (`Authorization: Bearer <TKN>`) de cada petición posterior que requiera autenticación.
* **Transacciones DB:** Gran parte de las operaciones críticas (gestión de stock, estados de chats) utilizan el motor de transacciones propio de Laravel (`DB::beginTransaction()`, `DB::commit()`, `DB::rollBack()`). Si alguna inserción falla en el proceso, la base de datos deshace todos los cambios, garantizando integridad referencial ante fallos imprevistos.

---

## 2. Modelos de Eloquent (tablas principales)

El ORM de Laravel (*Eloquent*) gestiona la representación y relaciones de las tablas de MySQL:

* `User.php` (`usuarios`): Representa cualquier actor del sistema, actúe de vendedor o comprador. La columna `contrasenya` se enmascara automáticamente en la respuesta HTTP (`$hidden`). En él residen numerosas relaciones como el catálogo de productos u ofertas asociadas.
* `Producto.php` (`productos`): Unidad principal de venta. Depende obligatoriamente de un usuario propietario (productor) y una categoría clasificatoria.
* `Categoria.php` (`categorias`): Taxonomía sencilla de los productos.
* `PuntoEntrega.php` (`puntos_entrega`): Refleja puntos en un mapa asociados a un productor mediante coordenadas.
* `CompraVenta.php` (`compraventas`): Tabla pivote transaccional. Registra el interés de compra de un usuario B sobre un artículo de un usuario A.
* `Chat.php` y `Mensajes.php` (`chats`, `mensajes`): El modelo Chat es el "sobre" o sala que une a 2 usuarios interesados en un producto particular, mientras que la tabla Mensajes almacena el historial cronológico enviado entre ambos.
* `Valoracion.php` (`valoraciones`): Tabla que persiste asíncronamente un "Rate" tras la finalización de una compraventa.

---

## 3. Validación inicial (`FormRequests`)

Para mantener los controladores limpios y con una única responsabilidad, la validación de los datos entrantes (Request payload JSON) se realiza previamente dentro del directorio `app/Http/Requests`.

* `LoginUserRequest.php`: Valida campos básicos (`email`, `contrasenya`).
* `UserRequest.php`: Efectúa las comprobaciones de registro como la confirmación redundante de contraseñas y, sobre todo, asegura de antemano que el email entrante no exista con un filtro `unique:usuarios,email`.
* `PuntosEntregaRequest.php`: Forza que la longitud y latitud enviadas mantengan un tipo flotante válido EPSG y no desajusten las queries matemáticas visuales al mapa.
* `StoreValoracionRequest.php`: Revisa que las estrellas evaluativas cumplan el rango exigido para el sistema.

---

## 4. Lógicas complejas explicadas

Para comprender correctamente el código del controlador, se detallan los algoritmos específicos o acercamientos técnicos y el porqué se tomaron esas decisiones.

### Sistema de "doble stock" en `CompraVentaController.php`

Cuando un usuario crea una compra, la API **no resta** directamente la cantidad seleccionada en la columna general de `stock_total` del producto. Simplemente suma esa cifra a la columna intermedia aislada `stock_reserva`.

* **Motivo Técnico:** Esto soluciona problemas de cancelación cruzada. El `stock_reserva` inhabilita que otro usuario pueda agotar esa mercancía y una columna extra visual MySQL define el `stock_real`. Solo si el vendedor concluye y pulsa Completado en la transacción, el sistema descontará para siempre todo ese material apartándolo tanto de reserva como del almacén maestro (`stock_total`).

### Radios de búsqueda geográfica (`PuntoEntregaController.php`)

Cuando el cliente solicita ver puntos alrededor de su finca (`puntosRadio`), el controlador utiliza una consulta genérica pura en Raw-SQL usando `6371 * acos(cos...`.

* **Motivo Técnico:** Implementa nativamente la **Fórmula de Haversine**. Filtrar radialmente calculando todas las distancias desde lenguaje de servidor (PHP) hacia millones de registros agotaría de forma ineficiente y perjudicial el ancho de banda y memoria RAM base de la instancia. Depositar este trabajo numérico estricto del perímetro sobre el motor interno de MySQL es inmensamente más fluido.

### Búsqueda de salas bi-direccionales (`MensajesController.php`)

En la función de guardado `store()`, existe un voluminoso bloque con sentencias asimétricas anidadas tipo OR: O Tú fuiste "Comprador" y Él "Vendedor" o a la inversa Él "Comprador" y Tú "Vendedor".

* **Motivo Técnico:** Garantiza la sala *única* de mensajería para esa combinación `Producto - Vendedor - Interesado`. El sistema no diferencia de cara al listado de qué parte de la mesa propuso arrancar formalmente a tipear el mensaje inicial.

### Volcado del gestor multipart file (`ProductoController.php`)

Las capturas guardadas durante el Upload son transferidas como imágenes físicas dentro del directorio `/public/storage/productos` y en BBDD solo se almacena un String referencial de la URL.

* **Motivo Técnico:** Convertir e incorporar blobs puros base64 incrustados en celdas de MySQL encarece y ralentiza las lecturas. Externalizar esta gestión a la lectura rápida de un servidor Web como Apache es la práctica estándar en la industria.

---

## 5. Listado y catálogo REST API

### Endpoints (rutas públicas)

| Método | Endpoint | Acción / Descripción |
| :--- | :--- | :--- |
| **POST** | `/api/login` | Recibe `email`, `contrasenya` y devuelve el JWT de Sanctum. |
| **POST** | `/api/register` | Recibe payload validado por UserRequest y crea cuenta nueva. |
| **GET** | `/api/productos` | Devuelve listado estructurado general. Adminte parámetros en URI (`?search=Tomate`, `?categorias=Verde`). |
| **GET** | `/api/productos/{id}` | Información unificada visual detallada de un producto en base de datos. |
| **GET** | `/api/categorias` | Enumeración general al catálogo taxonómico. |
| **GET** | `/api/puntos/{id}/productos` | Listado delimitado a qué unidades persisten dentro de este punto exclusivo. |

### Endpoints privados protectos (precisan cabecera: `Authorization: Bearer <TKN>`)

| Método | Endpoint | Acción / Descripción |
| :--- | :--- | :--- |
| **GET** | `/api/datosuser` | Objeto User general del token + puntuación agregada asíncrona generada dinámicamente (`loadAvg`). |
| **PUT** | `/api/usuarios/{usuario}/ubicacion` | Define y formaliza las long/lat base de referencia geoidal del usuario inicial. |
| **POST** | `/api/productos` | Creación C.R.U.D nueva alta inventario. Configurado para aglutinar formato Multipart Form y archivos en su petición. |
| **PUT** | `/api/productos/{id}` | Edición de variables numéricas, stockaje y permuta de foto. |
| **DELETE** | `/api/productos/{id}` | Purga C.R.U.D de persistencia física lógica en base. |
| **GET** | `/api/usuarios/{usuario}/productos` | Extracción individual personal limitando visuales ajenos. |
| **GET** | `/api/puntos_radio/{radio}` | Extracción perimetral de resultados próximos utilizando directivas de localización matemáticas referenciadas contra input de URIs. |
| **POST** | `/api/puntos` | Añade delegación operativa espacial nueva al dueño genérico. |
| **DELETE** | `/api/puntos/{id}` | Destrucción de nodo. |
| **POST** | `/api/compraventa/{producto}` | Abertura transaccional. Inicia estado *Pendiente* y desplaza inventario hacia `stock_reserva`. |
| **PUT** | `/api/mis-comandas/{comanda_id}` | Cambia flag del enumerado. Completar transacciones aniquila inventariado. |
| **GET** | `/api/mis-comandas` | Tabulación masiva mixta donde Vendedor / Comprador resulta unificado sobre listado general indexado. |
| **POST** | `/api/enviar-mensaje` | Agrega nueva cadena JSON a Inbox generativo o unificado paralelo. Si integra red tipo Pusher eleva Websocket difusivo (`MessageSent`). |
| **GET** | `/api/chat/{id}` | Obtener Blob de sala ordenado cronológicamente. |
| **PUT** | `/api/chats/{id}/leer` | Depuración y limpieza de contadores temporales locales (Notificaciones Badge rojas). |
| **GET** | `/api/mis-chats` | Tablera Listada rápida (solo el último `message.text` por elemento). |
| **POST** | `/api/valoraciones/{compraventa}` | Puntuador de ratio de confianza (Se requiere que haya cruzado con anterioridad el flag estado como `Completado`). |
| **GET** | `/api/dashboard` | Pantalla Métrica Estadística sumatoria. |
