# Backlog de producto y deuda técnica

Este documento conserva el backlog detallado. `AGENTS.md` debe permanecer ligero y enlazar aquí cuando haga falta contexto amplio.

## Repo y despliegue

- Decidir que cambios locales no relacionados con despliegue se commitean en `main`.
- Preparar una rama limpia para despliegue VPS.
- Sustituir o archivar la documentacion antigua de AWS/Azure/Traefik.
- Separar Docker local y Docker de produccion para el VPS.
- No exponer MySQL publicamente en produccion; dejarlo solo en red interna Docker/VPS.
- Quitar Xdebug de la imagen de produccion PHP.
- Usar `php.ini-production` en la imagen de produccion PHP.
- Revisar `APP_ENV`, `APP_DEBUG`, `APP_URL`, logs, cache y sesiones para produccion.
- Anadir healthchecks, backups de base de datos y logs persistentes para el VPS.
- Configurar HTTPS, cabeceras de seguridad y limites de subida/peticion en Nginx.

## Seguridad backend

- Anadir Policies/FormRequests para `Producto`, `CompraVenta`, `Chat`, `User`, `PuntoEntrega` y `Categoria`.
- Proteger `ProductoController@update` y `ProductoController@destroy` para que solo el propietario pueda editar o borrar sus productos.
- Proteger `UserController@updateLocation` para que un usuario solo pueda actualizar su propia ubicacion.
- Proteger `CompraVentaController@actualizarEstado` para que solo comprador/vendedor autorizados puedan cambiar estados.
- Definir transiciones validas de estados de compraventa (`pendiente`, `en curso`, `cancelado`, `completado`, `valorado`) y bloquear cambios incoherentes.
- Corregir `CompraVentaController@store` para no confiar en `id_vendedor` ni `id_punto` enviados por el frontend; derivarlos/validarlos desde el producto y el punto real.
- Validar que el comprador no pueda comprar su propio producto.
- Validar que el punto de entrega pertenezca al vendedor del producto.
- Usar transacciones con bloqueo (`lockForUpdate`) al reservar stock para evitar carreras de stock.
- Proteger `ChatController@marcarLeido` para comprobar que el usuario pertenece al chat antes de marcar mensajes como leidos.
- Revisar `MensajesController@store` para validar que el vendedor indicado coincide con el producto al crear un chat nuevo.
- Mover `POST /api/categorias` a rutas protegidas y restringirlo a rol administrador.
- Crear sistema de roles/permisos como minimo para usuario normal y admin.
- Anadir rate limiting a `/api/login`, `/api/register` y endpoints sensibles.
- Definir expiracion de tokens Sanctum y estrategia de revocacion/limpieza de tokens antiguos.
- Valorar migrar autenticacion SPA a cookies HttpOnly con Sanctum para no guardar tokens en `localStorage`.
- Usar API Resources para evitar exponer datos personales innecesarios en respuestas publicas.
- Revisar respuestas publicas de productos para no devolver email, telefono, direccion o coordenadas privadas del vendedor si no hace falta.
- Endurecer validaciones de registro: longitud minima real de password en backend, confirmacion, formato de telefono y normalizacion de email.
- Evitar devolver excepciones completas al cliente en controladores; registrar el detalle en logs y devolver mensajes genericos.

## Dependencias y auditoria

- Ejecutar `composer update` controlado para corregir las vulnerabilidades detectadas por `composer audit`.
- Repetir `composer audit` hasta dejar 0 advisories antes del despliegue VPS.
- Mantener `npm audit` en CI; el frontend estaba limpio en la revision actual.
- Anadir comprobaciones automatizadas: `composer validate`, `composer audit`, `npm audit`, `npm run build`, `php artisan test`.
- Evitar dependencias de desarrollo en imagenes de produccion cuando no sean necesarias (`phpunit`, `tinker`, herramientas dev).

## Base de datos y datos de prueba

- Convertir `docker/database/base.sql` en migraciones Laravel reales o documentar formalmente que el esquema vive en SQL.
- Hacer los seeders idempotentes para evitar categorias duplicadas al relanzar `db:seed`.
- Crear seeders especificos para chats, mensajes, compraventas y valoraciones si se necesitan para probar flujos completos.
- Ajustar `PuntoEntregaFactory` y `UserFactory` para generar coordenadas realistas cerca del area de uso de la app.
- Anadir imagenes de prueba compatibles con `ProductoFactory` (`productos/foto1.jpg` a `productos/foto8.jpg`) o cambiar el factory para usar una imagen existente.
- Revisar indices y constraints de BD para reglas de negocio: producto-vendedor-punto, unicidad de valoraciones, chats unicos y stock no negativo.
- Integrar soft deletes en las entidades que deban ser recuperables o auditables (`productos`, `puntos_entrega`, `usuarios`, `chats`, `mensajes`, `compraventas`), anadiendo `deleted_at`, `SoftDeletes` en modelos y reglas claras para restaurar/ocultar archivados.

## Frontend

- Revisar variables de entorno del frontend (`env.development`, `env.production`, `.env.*`) y dejar una convencion unica.
- Unificar `VITE_BACKEND_URL` y `VITE_API_URL`; ahora `vite.config.js` usa una y los env antiguos usan otra.
- No confiar en guards frontend como seguridad real; mantenerlos solo para UX y reforzar siempre en backend.
- Mejorar el manejo de errores de API para no mostrar mensajes genericos cuando el backend devuelva validaciones concretas.
- Extraer el CSS repetido de los componentes Vue a un archivo general compartido y dejar los estilos locales solo para casos especificos.
- Crear componentes compartidos para formularios, botones, toasts, estados vacios, paginacion y modales.
- Aislar los elementos visuales reutilizables en componentes Vue: tarjetas, badges, metric cards, headers de seccion, acciones, loaders y empty states.
- Crear una carpeta clara para componentes UI/base y otra para componentes de dominio.
- Revisar responsive tras quitar el `zoom: 0.7` global y corregir pantallas que dependieran de ese escalado.
- Evitar estilos globales dentro de componentes scoped que intenten afectar a `body`.

## UI/UX

- Revisar toda la experiencia responsive en movil y tablet; hay varios componentes con `body { min-width: 400px; }` y layouts que pueden provocar scroll horizontal.
- Sustituir zonas fijas pesadas, como la cabecera fija de filtros en productos, por layouts sticky/responsive que no tapen contenido ni fuercen paddings grandes.
- Crear un sistema visual basico: tokens de color, espaciado, tipografia, sombras, radios, estados hover/focus y tamanos de iconos.
- Convertir lo visualmente cuidado en piezas reutilizables: `BaseButton`, `BaseCard`, `BaseInput`, `BaseModal`, `BaseToast`, `BaseBadge`, `MetricCard`, `ProductCard` y `EmptyState`.
- Unificar el estilo de tarjetas de producto, comandas, compras/ventas, dashboard y valoraciones.
- Unificar toasts/notificaciones en un componente global con variantes de exito, error, aviso e informacion.
- Sustituir `alert()` y `confirm()` por modales/toasts propios para mantener una experiencia consistente.
- Mejorar estados de carga: skeletons o loaders por seccion en productos, dashboard, chat, comandas, mapas y formularios.
- Mejorar estados vacios con copy accionable y botones utiles, por ejemplo "Publicar producto", "Crear punto de entrega" o "Ampliar radio".
- Mejorar errores de formularios mostrando mensajes concretos junto al campo y resumen de errores cuando haya varios.
- Deshabilitar botones durante acciones asincronas para evitar dobles envios en publicar, comprar, valorar, crear puntos y enviar mensajes.
- Anadir debounce a busqueda y filtros de productos para evitar peticiones en cada pulsacion.
- Revisar paginacion y extraer un componente comun con estados disabled, aria labels y mejor comportamiento movil.
- Mejorar accesibilidad: labels asociados, `aria-label` en botones de icono, foco visible, navegacion por teclado y roles adecuados en modales.
- Revisar textos `alt`: iconos decorativos con `alt=""` y productos/logos con descripciones utiles.
- Convertir elementos clicables que son `div` en `button` o `router-link` semanticos cuando correspondan.
- Anadir cierre de menus/modales con Escape y click fuera, especialmente nav movil, menu de usuario, filtros y modales.
- Mejorar el chat: estado enviando, error de envio visible, agrupacion por fecha, separador de mensajes no leidos y scroll controlado.
- Refactorizar el chat en una arquitectura modular: vista `ChatView`, componentes reutilizables (`ChatList`, `ChatListItem`, `ChatWindow`, `ChatHeader`, `MessageList`, `MessageBubble`, `MessageComposer`, `ChatEmptyState`) y composable `useChat`.
- Asociar cada chat a una compraventa concreta para que varias compras del mismo producto generen hilos independientes y no se mezclen mensajes de pedidos distintos.
- Definir cierre/archivo automatico de chats cuando la compraventa llegue a estados finales (`completado`, `cancelado`, `valorado`), manteniendo historial sin tratarlos como chats activos.
- Mostrar cabecera contextual del pedido dentro del chat: producto, cantidad, precio total, fecha prevista, estado de la compraventa y punto de entrega.
- Anadir acciones rapidas en el chat segun rol y estado: aceptar/cancelar/preparar como vendedor, cancelar/confirmar recogida/valorar como comprador.
- Anadir mensajes de sistema en el chat para eventos importantes: solicitud creada, estado cambiado, pedido completado, chat cerrado o incidencia abierta.
- Separar chats activos, pendientes y archivados/cerrados con filtros o pestanas claras.
- Mostrar estados visuales del chat: abierto, pendiente de respuesta, cerrado, con incidencia y pago pendiente.
- Calcular y mostrar tiempo medio de respuesta del usuario/vendedor como metrica de confianza, similar a marketplaces profesionales.
- Mostrar avisos de pendiente de respuesta y ultima actividad en conversaciones y perfil publico del vendedor.
- Permitir crear una incidencia desde el chat, arrastrando automaticamente el contexto de producto, compraventa y mensajes relevantes.
- Evaluar adjuntos limitados en chat, por ejemplo foto del producto preparado o justificante relacionado con la entrega.
- Iniciar cada chat de compraventa con un resumen estructurado del pedido, no solo con texto libre del comprador.
- Bloquear nuevos mensajes en chats cerrados, permitiendo solo reabrir una incidencia si hay un problema posterior.
- Crear notificaciones por eventos del chat y de la compraventa: nueva solicitud, nuevo mensaje, cambio de estado, chat cerrado y valoracion pendiente.
- Anadir deep links entre comanda, chat, producto y perfil del vendedor para navegar directamente al contexto correcto.
- Crear un widget flotante de chat reutilizando los componentes modulares, con modo minimizado/abierto en escritorio y drawer/pantalla completa en movil.
- Sustituir polling visible de chat/notificaciones por estados en tiempo real cuando entre Reverb; mientras tanto, evitar parpadeos y refrescos que interrumpan al usuario.
- Mejorar mapa: indicar estado sin ubicacion, permisos de ubicacion, cantidad de puntos encontrados y CTA para cambiar radio.
- Anadir controles de visualizacion para puntos de entrega propios: toggle para mostrarlos/ocultarlos y modo de mapa para ver solo tus puntos.
- Mejorar formulario de publicacion: preview de imagen con opcion de quitar/cambiar, validacion de tamano/tipo y ayuda sobre precio/stock.
- Mostrar la valoracion media del vendedor junto a su nombre en `DetalleProducto`, reutilizando `ValoracionEstrellas` y haciendo que el endpoint de producto devuelva la puntuacion del usuario.
- Actualizar el stock visible en `DetalleProducto` tras crear una solicitud de compra sin recargar la pagina, idealmente usando el producto actualizado devuelto por backend.
- Revisar dashboard para mostrar metricas con contexto, rangos de fechas y enlaces directos a ventas, productos y comandas.
- Revisar flujos de compra y comanda con confirmaciones claras, estados comprensibles y acciones permitidas segun rol.
- Revisar microcopy y consistencia idiomatica: "comandas", "compras", "ventas", "puntos de entrega" y estados deben significar siempre lo mismo.
- Preparar una pequena guia de componentes/pantallas para mantener consistencia antes de crecer hacia PWA/app movil.

## Dashboard y analitica

- Redisenar el dashboard para mostrar metricas utiles y accionables, no solo listados.
- Separar dashboards por rol/contexto: comprador, vendedor, administrador y futura cooperativa.
- Mostrar ventas por periodo: dia, semana, mes y rango personalizado.
- Mostrar ingresos estimados, ingresos confirmados y ventas pendientes.
- Mostrar productos mas vendidos y productos con bajo stock.
- Mostrar comandas pendientes, en curso, completadas, canceladas y valoradas.
- Calcular tasa de cancelacion y tasa de conversion de solicitudes a ventas completadas.
- Mostrar tiempo medio de respuesta en chat y conversaciones pendientes.
- Mostrar valoracion media, numero de valoraciones y evolucion en el tiempo.
- Mostrar clientes recurrentes y compradores nuevos.
- Mostrar productos publicados frente a productos vendidos.
- Mostrar puntos de entrega o zonas con mas actividad.
- Anadir filtros de fecha, categoria, producto y punto de entrega en metricas.
- Anadir enlaces directos desde cada metrica a la vista accionable correspondiente.
- Preparar endpoint(s) de analitica en backend en lugar de calcular todo en frontend.
- Crear tests para calculo de metricas clave del dashboard.

## Backoffice y administracion

- Crear panel de administracion separado para gestionar el marketplace.
- Gestionar usuarios: busqueda, detalle, bloqueo/desbloqueo y revision de actividad.
- Moderar productos: revisar, ocultar, eliminar o marcar productos problematicos.
- Gestionar categorias desde administracion, no desde endpoint publico.
- Revisar reportes/incidencias de usuarios, productos, compras y chats.
- Gestionar comandas problematicas y cambios de estado excepcionales.
- Ver metricas globales del marketplace: usuarios activos, productos, ventas, ingresos, cancelaciones y valoraciones.
- Crear roles/permisos de administrador y moderador.
- Registrar auditoria de acciones administrativas importantes.

## Confianza y seguridad de marketplace

- Permitir reportar productos, usuarios, mensajes y compraventas.
- Crear flujo de moderacion para reportes con estados y resolucion.
- Moderar imagenes y descripciones de productos antes o despues de publicarlas.
- Crear una vista publica de perfil de usuario/vendedor con nombre, puntuacion total, valoraciones recibidas y productos disponibles a la venta, sin exponer email, telefono, direccion privada ni coordenadas sensibles.
- Mostrar historial publico basico del vendedor: antiguedad, valoracion, ventas completadas y tasa de respuesta.
- Evaluar verificacion opcional de vendedor/cooperativa.
- Definir reglas contra abuso, spam, productos falsos o contenido no permitido.
- Mejorar reputacion: estrellas, comentarios, numero de operaciones y badges de confianza.
- Permitir bloquear o silenciar usuarios en chat si se detecta abuso.
- Crear notificaciones internas para reportes y acciones de moderacion.

## Busqueda y descubrimiento

- Ordenar productos por distancia, precio, fecha de publicacion, valoracion y disponibilidad.
- Anadir filtros por stock disponible, categoria, vendedor/cooperativa, punto de entrega y rango de precio.
- Permitir guardar busquedas/filtros frecuentes.
- Mejorar la busqueda para tolerar tildes, plural/singular y errores simples.
- Mostrar productos similares en detalle de producto.
- Mostrar productos recomendados segun ubicacion, categorias vistas o compras anteriores.
- Crear secciones como "Cerca de ti", "Novedades", "Mejor valorados" y "Bajo stock/ofertas".
- Revisar paginacion/infinite scroll segun pantalla y caso de uso.
- Preparar indices de busqueda si el catalogo crece.

## Calendario agricola y reservas anticipadas

- Crear un calendario de temporada para frutas y verduras por zona/region.
- Mostrar que productos estan actualmente de temporada.
- Mostrar proximas cosechas/recolecciones previstas por vendedor o cooperativa.
- Permitir publicar productos como "proxima cosecha" aunque todavia no haya stock disponible.
- Permitir reservar cantidad de una proxima cosecha antes de que este disponible.
- Diferenciar claramente producto disponible, producto de temporada, proxima cosecha y reserva anticipada.
- Anadir fechas estimadas de recoleccion, disponibilidad y entrega.
- Permitir que el vendedor actualice fechas/cantidad estimada segun evolucione la cosecha.
- Notificar a compradores cuando una proxima cosecha reservada pase a estar disponible.
- Gestionar cupos de reserva para no sobreprometer produccion.
- Definir estados de reserva anticipada: solicitada, confirmada, disponible, cancelada, completada.
- Permitir listas de espera si se supera la cantidad prevista.
- Mostrar recomendaciones de temporada en home, busqueda y detalle de producto.
- Anadir filtros por "de temporada", "disponible ahora" y "proximamente".
- Crear una vista tipo calendario mensual/estacional con productos esperados.
- Permitir al vendedor planificar campanas/cosechas con cantidad estimada, unidad, fechas y puntos de entrega.
- Revisar reglas de pago para reservas anticipadas: sin pago, paga y senal, o pago cuando este disponible.
- Crear tests para reservas anticipadas, cambios de disponibilidad y notificaciones de cosecha.

## Operacion y soporte

- Crear pagina o flujo de contacto/soporte dentro de la app.
- Crear sistema de incidencias sobre compras, ventas, pagos, productos y usuarios.
- Definir estados de incidencia: abierta, en revision, resuelta, rechazada.
- Enviar emails/notificaciones transaccionales para registro, compra, cambio de estado, chat y valoracion.
- Crear plantillas de mensajes para vendedor/comprador en acciones frecuentes.
- Crear FAQ o ayuda contextual en publicacion, compra, puntos de entrega y pagos.
- Registrar historial de eventos relevantes de cada compraventa.
- Definir procedimiento de soporte para disputas entre comprador y vendedor.

## Legal y privacidad

- Crear terminos y condiciones.
- Crear politica de privacidad.
- Crear politica de cookies si aplica.
- Documentar tratamiento de ubicacion y finalidad de uso.
- Anadir consentimiento para notificaciones push/email.
- Permitir eliminar cuenta y solicitar eliminacion/exportacion de datos.
- Revisar retencion de datos personales, mensajes, ubicaciones y compraventas.
- Preparar textos legales especificos si se integran pagos con Stripe.
- Revisar requisitos de facturacion o justificantes si la app llega a operar pagos reales.

## SEO y presencia publica

- Crear URLs publicas amigables para productos, vendedores y futuras cooperativas.
- Anadir metadatos OpenGraph/Twitter para compartir productos.
- Crear sitemap y robots.txt.
- Revisar landing publica para explicar claramente ProxiMarkt y llevar a busqueda/productos.
- Crear paginas publicas de vendedor/cooperativa con productos, puntos y valoraciones.
- Optimizar rendimiento Lighthouse: carga inicial, imagenes, accesibilidad, SEO y buenas practicas.
- Definir estrategia de imagenes responsive y lazy loading.
- Revisar textos publicos para mejorar conversion y confianza.

## Observabilidad y operacion tecnica

- Centralizar logs de backend, Nginx, colas, Reverb y MySQL.
- Anadir monitorizacion de errores de frontend y backend.
- Crear alertas si cae API, VPS, base de datos, colas o Reverb.
- Monitorizar uso de disco, CPU, memoria y espacio de backups.
- Monitorizar jobs/colas pendientes y fallidos.
- Verificar backups automaticos y restauracion periodica.
- Anadir endpoint de healthcheck para API y dependencias criticas.
- Registrar trazabilidad de acciones importantes: login, publicacion, compra, cambio de estado, pago, reporte y acciones admin.
- Definir runbook basico para incidencias de produccion.

## Tests y calidad

- Crear una suite de tests para backend y frontend, empezando por autenticacion, productos, compras/ventas, chat y flujos criticos de usuario.
- Crear tests Feature para impedir editar/borrar productos ajenos.
- Crear tests Feature para impedir modificar ubicacion de otro usuario.
- Crear tests Feature para impedir cambiar estados de compraventas ajenas.
- Crear tests Feature para validar que una compra usa vendedor y punto correctos.
- Crear tests Feature para chat: solo participantes pueden ver, enviar y marcar mensajes como leidos.
- Crear tests Feature para categorias: solo admin puede crear.
- Crear tests de subida de imagenes: tipo, tamano, almacenamiento y borrado seguro de imagen anterior.
- Crear tests para stock y concurrencia basica de reservas.
- Anadir linters/formateadores: Laravel Pint para backend y ESLint/Prettier para frontend.
- Verificar login, publicacion, compra/venta, chat y mapa con datos seed antes de preparar el despliegue VPS.

## Tiempo real, pagos y producto

- Modificar chat y notificaciones para usar Laravel Reverb/WebSockets en tiempo real.
- Usar Redis para presencia de usuarios: mostrar estado conectado/no conectado y mensajes de "escribiendo...".
- Anadir una pasarela de pago de pruebas con Stripe para validar el flujo de compra sin pagos reales.
- Restringir `allowed_origins` de Reverb; no dejar `*` en produccion.
- Anadir workers/colas para broadcasts, notificaciones y tareas asincronas.
- Definir canales privados y de presencia para chat, notificaciones y estado online.
- Implementar webhooks de Stripe en modo test con validacion de firma.
- Definir estados de pago separados de estados logisticos de compraventa.

## Cooperativas

- Disenar modelo de cooperativas: varios usuarios/vendedores unidos bajo un perfil comun de venta.
- Definir roles dentro de una cooperativa: propietario/admin, miembro vendedor, gestor de pedidos y visor.
- Permitir que una cooperativa publique productos asociados al perfil comun, manteniendo trazabilidad del usuario que los crea.
- Permitir compras a una cooperativa con reparto interno de vendedor/producto/punto de entrega.
- Definir reglas de permisos para editar productos, gestionar stock y aceptar comandas dentro de una cooperativa.
- Crear vista publica de cooperativa con perfil, productos, puntos de entrega, miembros visibles y valoraciones agregadas.
- Definir invitaciones y solicitudes para entrar/salir de cooperativas.
- Evaluar valoraciones por cooperativa ademas de valoraciones por usuario individual.
- Crear tests para reglas de cooperativas.

## App movil / PWA

- Evaluar si desarrollar una app movil ademas de la web o convertir la web en una PWA muy cuidada.
- Comparar opciones: PWA, Capacitor/Ionic, React Native, Flutter o app nativa.
- Definir funcionalidades que justifican movil/PWA: notificaciones push, ubicacion en segundo plano, geofencing, camara para productos, acceso rapido y experiencia offline.
- Si se elige PWA, cuidar instalabilidad, service worker, cache, estados offline, manifest, iconos, splash screen y push notifications.
- Si se elige app movil, definir API estable/versionada para compartir backend con web y movil.
- Revisar permisos de ubicacion y privacidad antes de reintroducir geofencing.
- Disenar flujo movil para comprador y vendedor: publicar producto, recibir comanda, chat, notificaciones y mapa.
- Crear estrategia de despliegue: web/PWA, Play Store/App Store si aplica, y entornos de test.
