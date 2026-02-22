# Documentación ampliada de componentes y funciones (Frontend)

Este documento está orientado a estudio y defensa oral. No solo enumera funciones: también explica qué problema resuelve cada fichero, cómo se conecta con otros y qué podrías responder en un examen.

## 1) Arquitectura general del frontend

- Stack: Vue 3 (`<script setup>`), Vue Router, Axios, Leaflet.
- Estado global de sesión: `src/composables/useAuth.js`.
- Cliente HTTP común: `src/api/axios.js`.
- Flujo típico:

1. El usuario entra en una ruta.
2. El componente llama a `fetchUsuario()` (si requiere sesión).
3. Se consumen endpoints con `api`.
4. Se renderiza UI reactiva con `ref`, `computed`, `watch`.
5. En formularios, se valida y se envía al backend.

Si te preguntan “¿dónde está la lógica central de autenticación?”: en `useAuth` + `router.beforeEach` + interceptor de Axios.

---

## 2) Núcleo de aplicación

## `src/main.js`

**Qué hace**

- Punto de entrada de Vue.
- Crea la app, registra el router y monta en `#app`.
- Carga CSS global de Bootstrap, Bootstrap Icons y Leaflet.

**Funciones**

- No define funciones propias.

**Qué decir en examen**

- “Aquí arranca toda la app. Si algo no se monta o no navega, este archivo es el primer checkpoint.”

## `src/App.vue`

**Qué hace**

- Componente raíz mínimo con `<router-view>`.

**Funciones**

- No define funciones.

**Qué decir en examen**

- “`App.vue` delega toda la navegación al router; no tiene lógica de negocio.”

## `src/routes/routes.js`

**Qué hace**

- Declara rutas y componentes asociados.
- Aplica protección de rutas con `beforeEach`.

**Funciones clave**

- `router.beforeEach(async (to) => {...})`
  - Permite rutas públicas (`/`, `/auth`).
  - Si la ruta es privada y no hay token, redirige a `/auth`.

**Qué decir en examen**

- “La seguridad de navegación está en guardas del router. No reemplaza seguridad backend, pero evita acceso UI sin sesión.”

## `src/api/axios.js`

**Qué hace**

- Crea una instancia Axios con `baseURL` de la API.
- Centraliza headers y manejo automático de token.

**Funciones/handlers**

- `api.interceptors.request.use(...)`
  - Añade `Authorization: Bearer <token>` si existe.
- `api.interceptors.response.use(...)`
  - Si recibe `401`, elimina token local.

**Qué decir en examen**

- “Con interceptores evito repetir headers en cada componente y unifico control de sesión expirada.”

## `src/composables/useAuth.js`

**Qué hace**

- Estado compartido de autenticación para todos los componentes.

**Estado interno**

- `_usuario`, `_loading`, `_error`, `_token`.

**Funciones**

- `useAuth()`
  - Expone API reactiva: `usuario`, `loading`, `error`, `estarAutenticado`, etc.
- `setLoading(estado)`
  - Cambia estado global de carga.
- `fetchUsuario()`
  - Llama a `/datosuser`, guarda datos de usuario actual.
- `login(nuevoToken, datosUsuario)`
  - Guarda token en localStorage y usuario en estado.
- `logout()`
  - Limpia token y usuario.

**Qué decir en examen**

- “`useAuth` evita duplicar lógica de sesión en componentes. Es un estado global simple sin Pinia.”

## `src/utils/geo.js`

**Qué hace**

- Utilidad matemática para distancia geográfica.

**Funciones**

- `calcularDistancia(lat1, lon1, lat2, lon2)`
  - Implementa Haversine en km.

**Qué decir en examen**

- “Se usa para lógica de proximidad; es determinista y desacoplable de UI.”

---

## 3) Autenticación y landing

## `src/views/AuthView.vue`

**Objetivo**

- Pantalla combinada de login/registro.

**Estado**

- `eleccionActual`: pestaña activa (`login` o `register`).

**Funciones/hooks**

- `onMounted(...)`
  - Lee `history.state?.modo` para abrir directamente la pestaña solicitada.

**Cómo explicarlo**

- “Uso una vista unificada para auth, y con estado de navegación decido si mostrar login o registro.”

## `src/components/LoginForm.vue`

**Objetivo**

- Autenticar usuario.

**Funciones**

- `lanzarToast(mensaje)`
  - Feedback rápido al usuario.
- `enviarInfo()`
  - Valida campos obligatorios.
  - POST `/login`.
  - Guarda sesión con `login(token, user)`.
  - Redirige:
    - Si no tiene dirección: `/ubicacion`.
    - Si sí tiene: `/cuenta`.

**Qué pueden preguntarte**

- “¿Por qué rediriges a `/ubicacion`?”
  - Para completar requisito de geolocalización y poder filtrar por proximidad.

## `src/components/RegistroForm.vue`

**Objetivo**

- Alta de usuario nuevo.

**Funciones**

- `lanzarToast(mensaje)`
- `enviarInfo()`
  - Valida completos, coincidencia de contraseñas y longitud mínima.
  - POST `/register`.
  - Resetea formulario.
  - Redirige a login tras éxito.

**Idea clave para examen**

- “La validación mínima se hace en frontend para UX, pero la validación definitiva debe estar en backend.”

## `src/components/Principal.vue`

**Objetivo**

- Landing principal pública.

**Funciones**

- `irAuth(modo)`
  - Si no hay token, navega a auth con modo (`login/register`).
  - Si hay token, lleva a `/cuenta`.

## `src/components/Footer.vue`

**Funciones**

- No tiene funciones; solo estructura visual de pie de página.

---

## 4) Navegación, radio y notificaciones

## `src/components/NavBar.vue`

**Objetivo**

- Menú principal, control de sesión, notificaciones y radio de búsqueda.

**Funciones**

- `cerrarSesion()`
  - Limpia polling, hace logout y redirige a inicio.
- `confirmarNuevoRadio(valor)`
  - Guarda valor en estado + localStorage, emite `cambiar-radio`.
- `comprobarNotificaciones()`
  - GET `/mis-chats`, detecta no leídos.
  - GET `/mis-comandas`, detecta pendientes del vendedor.
- `irAuth(modo)`
  - Navega a auth.
- `onMounted(...)`
  - Carga usuario y arranca polling cada 3s.
- `onUnmounted(...)`
  - Detiene polling.

**Qué decir en examen**

- “La barra centraliza señales globales (sesión, avisos, radio), pero delega la lógica de datos persistidos a backend/localStorage.”

## `src/components/ModalRadio.vue`

**Objetivo**

- Configurar distancia máxima de búsqueda.

**Funciones/reactividad**

- `normalizarEntrada(valor)`: adapta `Infinity`, mínimos y límites.
- `confirmarSeleccion()`: emite el valor final al padre.
- `maximoSlider`, `etiquetas`, `esIlimitado`, `valorAMostrar`, `tamanoFondo` (computed): soporte visual y semántico del slider.
- `watch(propiedades.mostrar, ...)`: al abrir modal, sincroniza valor desde props.

**Qué decir en examen**

- “Represento ‘sin límite’ como un valor especial para UI (`Infinity`) y lo traduzco cuando llamo a API.”

---

## 5) Catálogo y detalle de productos

## `src/components/Productos.vue`

**Objetivo**

- Pantalla principal de catálogo con filtros, paginación y radio.

**Funciones**

- `calcularDistanciaReal(latV, lngV)`
  - Distancia entre usuario y punto del producto (Haversine simplificado).
- `mostrarProductos(pagina = 1)`
  - GET `/productos` con `km`, `search`, `categorias`, `page`.
- `cargarCategorias()`
  - GET `/categorias`.
- `manejarCambioRadio(nuevoRadio)`
  - Actualiza radio y recarga catálogo.
- `toggleMenu()`
  - Muestra/oculta filtro de categorías.

**Reactividad**

- `watch([textoBusqueda, categoriasSeleccionadas], ...)`
  - Reconsulta al cambiar filtros.
- `productosFiltrados` (computed)
  - Segunda capa de filtrado por distancia real.

**Qué decir en examen**

- “Hay filtrado doble: backend (por `km` enviado) y frontend (por cálculo local), útil para coherencia visual.”

## `src/components/MostrarProductosMain.vue`

**Objetivo**

- Render de tarjetas de producto del catálogo general.

**Funciones**

- `productosAjenos` (computed)
  - Evita mostrar productos del propio usuario.
- `calcularKm(...)`
  - Distancia para etiqueta ‘A X km de ti’.

## `src/components/DetalleProducto.vue`

**Objetivo**

- Ver producto individual y crear solicitud de compra.

**Funciones**

- `lanzarToast(mensaje)`
- `obtenerProducto()`
  - GET `/productos/:id`.
- `crearCompraventa(datosCompra)`
  - Construye payload de compraventa.
  - Opcional: POST `/enviar-mensaje` si hay mensaje inicial.
  - POST `/compraventa/:id` para solicitud.

**Hook**

- `onMounted(...)`: primero usuario, luego producto.

## `src/components/SolicitarCompra.vue`

**Objetivo**

- Formulario hijo para solicitar compra.

**Funciones**

- `enviarSolicitud()`
  - Emite al padre `{ cantidad, fecha_prevista, mensaje }`.
  - Limpia formulario.

**Qué decir en examen**

- “Separé el formulario de compra como componente presentacional y el padre conserva la lógica de API.”

---

## 6) Publicación, edición y gestión de cuenta

## `src/components/Publicar.vue`

**Objetivo**

- Crear un nuevo producto con imagen, categoría y punto de entrega.

**Funciones**

- `lanzarToast(mensaje)`
- `guardarImagen(event)`
  - Gestiona selección y preview de imagen.
- `abrirModalYMapa()`
  - Abre modal y prepara mapa para nuevo punto.
- `iniciarMapa()`
  - Inicializa Leaflet + click para reverse geocoding (Nominatim).
- `guardarNuevoPunto()`
  - POST `/puntos`, recarga lista y selecciona punto creado.
- `intentarPublicar()`
  - Validaciones de negocio.
  - Si no hay puntos, obliga a crear uno.
- `insertarProducto()`
  - POST `/productos` con `FormData`.
- `cargarPuntos()`
  - GET `/usuarios/:id/puntos`.
- `cargarCategorias()`
  - GET `/categorias`.

**Hooks**

- `onMounted(...)` carga usuario + datos base.
- `onBeforeUnmount(...)` limpia mapa y blobs de imagen.

**Qué decir en examen**

- “El flujo obliga a tener punto de entrega para mantener integridad de la lógica de proximidad.”

## `src/components/EditarProducto.vue`

**Objetivo**

- Modificar producto existente.

**Funciones**

- `lanzarToast(mensaje)`
- `seleccionarArchivo(e)`
  - Guarda nueva imagen temporal.
- `cargarProducto()`
  - GET `/productos/:id`.
- `editarProducto()`
  - Envío `FormData` con `_method='PUT'`.
- `cargarPuntos()`
  - Puntos disponibles para reasignar entrega.

**Hook**

- `onMounted(...)` carga usuario y paraleliza peticiones.

## `src/components/MiCuenta.vue`

**Objetivo**

- Panel de perfil: puntos, productos, compras, ventas, valoraciones.

**Funciones mapa/puntos**

- `guardarPuntoEntrega()`
  - Activa modo mapa e inicializa recursos.
- `cargarMarcadores()`
  - Dibuja puntos actuales.
- `onMapClick(e)`
  - Captura coordenadas y obtiene dirección.
- `crearPunto()`
  - POST `/puntos`.
- `cargarPuntos()`
  - GET `usuarios/:id/puntos`.
- `esconderMapa()`
  - Destruye instancia de mapa.
- `eliminarPunto(id)`
  - DELETE `/puntos/:id`.
- `irAlPunto(punto)`
  - `flyTo` para centrar mapa en punto.

**Funciones productos/secciones**

- `cargarProductosUser(pagina)`
  - GET `/usuarios/:id/productos` paginado.
- `eliminarProducto(id)`
  - DELETE `/productos/:id`.
- `cambiarSeccion(seccion)`
  - Cambia pestaña activa y cierra mapa si estaba abierto.

**Hooks**

- `onMounted(...)` carga datos base.
- `onUnmounted(...)` limpia mapa.

**Qué decir en examen**

- “Es un contenedor orquestador: integra subcomponentes de CRUD y mapa dentro de una misma experiencia de ‘mi cuenta’.”

## `src/components/MostrarProductos.vue`

**Objetivo**

- Tabla/tarjetas de productos del usuario (modo gestión).

**Funciones**

- `eliminarproducto(id)` -> emite `borrar`.
- `cambiarpagina(pagina)` -> emite `cambiarPagina`.

---

## 7) Mensajería y comandas

## `src/components/Mensaje.vue`

**Objetivo**

- Vista maestra de chats.

**Computed**

- `chatActivo`: chat seleccionado.
- `idReceptorDinamico`: calcula contraparte según si eres comprador/vendedor.
- `hayNotificacionesGlobales`: detecta no leídos.

**Funciones**

- `obtenerChats()`
  - GET `/mis-chats`.
- `seleccionarChat(chatId)`
  - Selecciona visualmente.
  - Marca localmente como leído y llama PUT `/chats/:id/leer`.

**Hooks**

- `onMounted(...)`: fetch usuario, carga chats y polling.
- `onUnmounted(...)`: limpia intervalo.

## `src/components/Chat.vue`

**Objetivo**

- Vista detalle de conversación.

**Funciones**

- `formatearFecha(fechaRaw)`
  - Formato `hh:mm`.
- `hacerScrollAlFinal()`
  - Auto-scroll al último mensaje.
- `cargarMensajes()`
  - GET `/chat/:chatid`.
  - Si detecta nuevos, hace scroll.
- `enviarMensaje()`
  - POST `/enviar-mensaje` y refresca.
- `iniciarPolling()`
  - Intervalo cada 2s.

**Reactividad/hooks**

- `watch(props.chatid, ...)`: cambia contexto de chat sin recargar página.
- `onMounted/onUnmounted`: inicia/limpia polling.

## `src/components/Comandas.vue`

**Objetivo**

- Gestión de transacciones de compra/venta y cierre del ciclo con valoración.

**Funciones**

- `lanzarToast(mensaje)`
- `obtenerComandas()`
  - GET `/mis-comandas`.
- `actualizarComanda(id, nuevoEstado)`
  - PUT `/mis-comandas/:id`.
- `abrirModalValoracion(id)`
  - Selecciona compraventa para valorar.
- `getColoresEstado(estado)`
  - Color por estado.
- `formatearFecha(fecha)`
- `getNombreContraparte(comanda)`
  - Decide nombre de la otra parte.
- `getUrlImagen(rutaRelativa)`
- `postValoracion(idCompraventa, datos)`
  - Envía valoración (flujo posterior a compra completada).

**Computed**

- `comandasPendientes`: estados activos.
- `historialComandas`: estados cerrados.

**Hook**

- `onMounted(...)`.

**Qué decir en examen**

- “Este componente implementa máquina de estados de la comanda: pendiente → en curso → completado/cancelado.”

## `src/components/MisCompras.vue`

- `misCompras(pagina)`
  - GET `/mis-compras` paginado.
- `formatearFecha(fechaRaw)`.
- `onMounted(...)` carga inicial.

## `src/components/MisVentas.vue`

- `misVentas(pagina)`
  - GET `/mis-ventas` paginado.
- `formatearFecha(fechaRaw)`.
- `onMounted(...)` carga inicial.

---

## 8) Valoraciones

## `src/components/ValoracionForm.vue`

- `enviarValoracion()`
  - Emite `{ valoracion, comentario }`.

## `src/components/ValoracionEstrellas.vue`

- `enviarPuntuacion(estrella)`
  - Actualiza `v-model` de puntuación.
- `obtenerClaseEstrella(estrella)`
  - Decide icono visual según hover/valor actual.

## `src/components/ValoracionCard.vue`

- `fechaFormateada` (computed)
  - Convierte `created_at` a formato corto.
- `nombreProducto` (computed)
  - Lectura segura de producto asociado.

## `src/components/ValoracionView.vue`

- `obtenerValoraciones()`
  - GET `/valoraciones`.
- `onMounted(...)`.

**Qué decir en examen**

- “La valoración es un subdominio separado: captura (`Form`), representación (`Card`) y visualización de estrellas (`Estrellas`).”

---

## 9) Mapa y geolocalización

## `src/components/Mapa.vue`

**Objetivo**

- Mostrar puntos de entrega cercanos y cargar productos al pulsar marcador.

**Funciones**

- `seleccionarPunto(idPunto)`
  - GET `/puntos/:id/productos` y muestra lista.
- `cargarMarcadores()`
  - Dibuja marcadores y bind click/tooltip.
- `actualizarPuntos(nuevoRadio)`
  - GET `/puntos_radio/:radio` usando coords del usuario.
- `inicializarMapa()`
  - Config inicial de Leaflet + carga inicial por radio.

## `src/components/Ubicacion.vue`

**Objetivo**

- Configurar ubicación principal del usuario.

**Funciones**

- `lanzarToast(mensaje)`
- `inicializarMapa()`
  - Crea mapa y marcador inicial.
- `guardarUbicacion()`
  - PUT `usuarios/:id/ubicacion`.
- `cancelar()`
  - Confirma salida con cambios no guardados.
- `obtenerUbicacionActual()`
  - Usa API de geolocalización del navegador + reverse geocoding.

**Hook**

- `onMounted(...)`.

**Qué decir en examen**

- “Hay dos fuentes de ubicación: clic manual en mapa o geolocalización del navegador; ambos convergen en lat/lng + dirección.”

---

## 10) Dashboard

## `src/components/Dashboard.vue`

**Objetivo**

- Resumen ejecutivo de actividad (productos, stock, ventas, ingresos, compras).

**Computed**

- `stockTotal`: suma stock.
- `ingresos`: suma importe ventas completadas.
- `comprasCompletadas`, `ventasCompletadas`: filtros por estado.
- `productosOrdenados`: orden estable por id.

**Funciones**

- `cargarDatosDashboard()`
  - GET `/dashboard` y carga dataset completo.
- `cargarProductosUser()`, `obtenerCompras()`, `obtenerVentas()`
  - Funciones alternativas separadas (actualmente no usadas en montaje principal).

**Hook**

- `onMounted(...)`.

---

## 11) Preguntas típicas de examen y cómo “tirar del hilo”

1. “¿Cómo protegéis rutas privadas?”

- Responde: “Con `router.beforeEach`; si no hay token redirigimos a `/auth`. Además Axios añade token automáticamente por interceptor.”

1. “¿Dónde está el estado del usuario?”

- Responde: “En `useAuth`, con refs privadas y computed públicos (`usuario`, `loading`, `estarAutenticado`).”

1. “¿Cómo gestionáis proximidad?”

- Responde: “El radio se guarda en localStorage y se usa en peticiones (`/productos`, `/puntos_radio`). También calculamos distancia local para mostrar/filtrar.”

1. “¿Cómo funciona el chat en tiempo real?”

- Responde: “No hay websockets; usamos polling cada 2-3 segundos en `Mensaje.vue` y `Chat.vue`.”

1. “¿Cómo evitáis fugas con Leaflet?”

- Responde: “Destruimos mapa en `onUnmounted`/`onBeforeUnmount` y limpiamos recursos temporales de imágenes.”

1. “¿Dónde validáis formularios?”

- Responde: “Validación de UX en frontend (`intentarPublicar`, `enviarInfo`...), y el backend valida de forma definitiva.”

---

## 12) Mapa rápido de endpoints usados por la UI

- Auth/usuario:
  - `POST /login`
  - `POST /register`
  - `GET /datosuser`
  - `PUT usuarios/:id/ubicacion`

- Productos/categorías:
  - `GET /productos`
  - `GET /productos/:id`
  - `POST /productos`
  - `POST /productos/:id` (override PUT)
  - `DELETE /productos/:id`
  - `GET /categorias`
  - `GET /usuarios/:id/productos`

- Puntos/mapa:
  - `GET /usuarios/:id/puntos`
  - `POST /puntos`
  - `DELETE /puntos/:id`
  - `GET /puntos_radio/:radio`
  - `GET /puntos/:id/productos`

- Compraventa/comandas:
  - `POST /compraventa/:id`
  - `GET /mis-comandas`
  - `PUT /mis-comandas/:id`
  - `GET /mis-compras`
  - `GET /mis-ventas`

- Mensajería:
  - `GET /mis-chats`
  - `GET /chat/:id`
  - `POST /enviar-mensaje`
  - `PUT /chats/:id/leer`

- Dashboard/valoraciones:
  - `GET /dashboard`
  - `GET /valoraciones`
  - `POST /valoraciones/:idCompraventa`

---

## 13) Resumen corto para memorizar

- `useAuth` = sesión global.
- `routes.js` = control de acceso.
- `axios.js` = token automático.
- `Productos/Mapa` = lógica de proximidad y descubrimiento.
- `Detalle + SolicitarCompra + Comandas` = ciclo transaccional completo.
- `Mensaje + Chat` = comunicación comprador-vendedor por polling.
- `MiCuenta/Publicar/Editar` = CRUD personal del vendedor.
- `Dashboard` = métricas.
