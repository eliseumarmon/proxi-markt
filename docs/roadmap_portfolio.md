# Roadmap de portfolio

## Direccion del proyecto

El objetivo es convertir ProxiMarkt en un marketplace de proximidad presentable en portfolio: no solo un CRUD de productos, sino un flujo completo de confianza entre comprador y vendedor.

Prioridad narrativa:

- Marketplace local con productos reales, ubicacion, puntos de entrega y stock.
- Compra trazable: solicitud, comanda, chat asociado, estados, pago, cierre y valoracion.
- Confianza del vendedor: perfil publico, valoraciones, tiempo medio de respuesta y productos activos.
- Diferencial agricola: temporada, proximas cosechas y reservas anticipadas.
- Calidad profesional visible: seguridad, tests, despliegue VPS, documentacion y demo preparada.

## Fase 0 - Limpieza de rama y base local

- Revisar cambios locales pendientes y separarlos en commits/ramas coherentes.
- Decidir que cambios traidos de `origin/despliegue` se quedan en `main`.
- Eliminar o archivar referencias antiguas de despliegue AWS/Azure/Traefik.
- Mantener `main` estable y crear ramas cortas para cada bloque.
- Actualizar `README` minimo con ejecucion local, usuario demo y estado del proyecto.
- Preparar seeds realistas para demo: usuarios, productos, chats, compraventas y valoraciones.

## Fase 1 - Seguridad critica y consistencia de dominio

- Corregir autorizacion de productos: solo propietario puede editar/borrar.
- Corregir autorizacion de ubicacion: solo el usuario autenticado puede actualizar su ubicacion.
- Corregir autorizacion de compraventas: solo participantes validos pueden cambiar estados.
- Validar compra contra producto real: vendedor, punto de entrega, comprador y stock.
- Usar transacciones y bloqueo al reservar stock para evitar carreras.
- Proteger chat: solo participantes pueden ver, enviar y marcar mensajes como leidos.
- Proteger creacion de categorias con rol admin.
- Anadir rate limiting a login/registro.
- Ejecutar `composer update` controlado y dejar `composer audit` sin vulnerabilidades.
- Crear tests Feature minimos para autorizacion, compra, stock, chat y categorias.
- Verificar `npm run build` y `php artisan test`.

## Fase 2 - Despliegue VPS limpio

- Crear rama `deploy/vps`.
- Separar Docker local y Docker produccion.
- Preparar imagen PHP sin Xdebug y con `php.ini-production`.
- No exponer MySQL publicamente.
- Configurar Nginx, HTTPS, limites de subida y cabeceras basicas.
- Definir `.env.example` realista para produccion.
- Documentar despliegue paso a paso.
- Anadir healthcheck basico.
- Validar despliegue con build frontend y API funcionando.

## Fase 3 - Chat/compraventa como flujo profesional

Esta es una de las piezas principales de portfolio. El chat debe dejar de ser una conversacion generica y pasar a ser el hilo operativo de cada pedido.

- Refactorizar frontend en arquitectura modular: `ChatView`, `ChatList`, `ChatListItem`, `ChatWindow`, `ChatHeader`, `MessageList`, `MessageBubble`, `MessageComposer`, `ChatEmptyState` y composable `useChat`.
- Asociar cada chat a una compraventa concreta.
- Permitir varias compras del mismo producto sin mezclar mensajes: cada solicitud debe tener su propio hilo.
- Definir cierre/archivo automatico del chat cuando la compraventa llegue a estados finales (`completado`, `cancelado`, `valorado`).
- Mostrar cabecera contextual del pedido dentro del chat: producto, cantidad, precio total, fecha prevista, estado y punto de entrega.
- Anadir acciones rapidas segun rol y estado: aceptar, cancelar, preparar, confirmar recogida y valorar.
- Anadir mensajes de sistema: solicitud creada, cambio de estado, pedido completado, chat cerrado e incidencia abierta.
- Separar chats activos, pendientes y archivados/cerrados.
- Mostrar estados visuales: abierto, pendiente de respuesta, cerrado, con incidencia y pago pendiente.
- Calcular tiempo medio de respuesta del usuario/vendedor y mostrarlo como metrica de confianza.
- Mostrar ultima actividad y avisos de pendiente de respuesta en conversaciones y perfil publico.
- Permitir crear una incidencia desde el chat con contexto de producto, compraventa y mensajes.
- Evaluar adjuntos limitados relacionados con la entrega.
- Iniciar cada chat con un resumen estructurado del pedido.
- Bloquear nuevos mensajes en chats cerrados, permitiendo reabrir una incidencia si procede.
- Anadir deep links entre comanda, chat, producto y perfil del vendedor.
- Crear widget flotante de chat reutilizando los componentes modulares.

## Fase 4 - Tiempo real y notificaciones

- Sustituir polling de chat/notificaciones por Laravel Reverb/WebSockets.
- Configurar canales privados para chats y notificaciones.
- Configurar canales de presencia para estado conectado/no conectado.
- Usar Redis para presencia y mensajes de "escribiendo...".
- Crear notificaciones por eventos: nueva solicitud, nuevo mensaje, cambio de estado, chat cerrado y valoracion pendiente.
- Configurar workers/colas para broadcasts y tareas asincronas.
- Restringir origins de Reverb para local y produccion.
- Crear tests de autorizacion de canales y mensajes.

## Fase 5 - Pagos de prueba con Stripe

- Integrar Stripe en modo test.
- Crear flujo de pago para una compraventa/reserva.
- Definir estados de pago separados del estado logistico.
- Implementar webhook con validacion de firma.
- Registrar eventos de pago relevantes.
- Mostrar feedback claro en frontend: pago pendiente, confirmado, fallido o reembolsado.
- Conectar estados de pago con chat/comanda y notificaciones.
- Documentar tarjetas de prueba y flujo demo.

## Fase 6 - Confianza del vendedor y perfil publico

- Crear vista publica de perfil de usuario/vendedor.
- Mostrar nombre, puntuacion total, valoraciones recibidas y productos disponibles.
- Mostrar tiempo medio de respuesta, ultima actividad y ventas completadas si el dato es fiable.
- Mostrar productos activos del vendedor con enlace al detalle.
- Enlazar desde `DetalleProducto` al perfil publico del vendedor.
- Mostrar valoracion media del vendedor junto a su nombre en `DetalleProducto`.
- Evitar exponer email, telefono, direccion privada o coordenadas sensibles.
- Preparar base para perfiles de cooperativa.

## Fase 7 - Componentes UI y experiencia base

- Crear mini design system: `BaseButton`, `BaseCard`, `BaseInput`, `BaseModal`, `BaseToast`, `BaseBadge`, `MetricCard`, `ProductCard`, `EmptyState`.
- Separar componentes UI/base y componentes de dominio.
- Extraer CSS repetido a estilos compartidos.
- Sustituir `alert()` y `confirm()` por modales/toasts.
- Corregir responsive tras eliminar `zoom: 0.7`.
- Eliminar `body { min-width: 400px; }` de componentes.
- Mejorar estados de carga, error y vacio.
- Anadir debounce en busqueda/filtros.
- Mejorar accesibilidad basica: labels, foco visible, aria labels y teclado.
- Anadir controles de mapa para mostrar/ocultar puntos propios y ver solo tus puntos.

## Fase 8 - Dashboard y analitica util

- Redisenar dashboard para vendedor/comprador.
- Crear endpoint backend de metricas.
- Mostrar ventas por periodo, ingresos, stock bajo, comandas pendientes y productos mas vendidos.
- Mostrar valoracion media, tiempo medio de respuesta y conversaciones pendientes.
- Mostrar productos publicados frente a vendidos.
- Mostrar tasa de cancelacion y conversion de solicitudes a ventas completadas.
- Anadir filtros de fecha/categoria/producto/punto de entrega.
- Enlazar cada metrica a la vista accionable.
- Crear tests de calculo de metricas clave.

## Fase 9 - Calendario agricola y reservas anticipadas

- Disenar modelo de cosechas/campanas.
- Permitir publicar "proxima cosecha" sin stock disponible inmediato.
- Mostrar productos de temporada y proximas recolecciones.
- Permitir reservas anticipadas con cupo.
- Definir estados de reserva anticipada.
- Notificar cuando una cosecha reservada este disponible.
- Anadir filtros "disponible ahora", "de temporada" y "proximamente".
- Revisar reglas de pago para reservas anticipadas.
- Documentar esta feature como diferencial del portfolio.

## Fase 10 - Portfolio y presentacion

- Preparar README profesional con descripcion, stack, arquitectura, capturas y roadmap.
- Anadir capturas o GIFs de flujos clave: buscar, publicar, comprar, chat/comanda, pago, perfil vendedor y dashboard.
- Documentar decisiones tecnicas importantes y tradeoffs.
- Anadir enlace a demo desplegada.
- Crear datos seed realistas para demo.
- Preparar una ruta de demo clara: buscar producto, solicitar compra, hablar por chat, cambiar estado, pagar, cerrar y valorar.
- Limpiar ramas antiguas y dejar historial comprensible.
- Preparar una breve explicacion para entrevistas: problema, solucion, decisiones, seguridad, despliegue y siguientes pasos.

## Evolucion posterior

- Backoffice/admin.
- Cooperativas.
- PWA/app movil.
- SEO publico.
- Observabilidad avanzada.
- Soporte/incidencias completo.
- Legal/privacidad completo.
- Busqueda avanzada y recomendaciones.
- Soft deletes y archivo/restauracion avanzado si no se ha abordado antes.
