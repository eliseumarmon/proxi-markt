# Tareas pendientes de UI

Lista independiente de seguimiento para continuar el mini sistema de diseño después de la extracción inicial de botones y notificaciones.

## Antes de integrar la primera fase

- [ ] Revisar visualmente los toasts de éxito, error, aviso e información.
- [ ] Comprobar varios toasts simultáneos y mensajes largos.
- [ ] Revisar el comportamiento responsive, especialmente en móvil.
- [ ] Comprobar con teclado el foco, la activación de botones y el cierre de notificaciones.
- [ ] Añadir Vitest y Vue Test Utils al frontend.
- [ ] Probar variantes, tamaños, carga y estado deshabilitado de `BaseButton`.
- [ ] Probar cola, límite, temporizador y cierre de `useToast`.
- [ ] Probar los roles accesibles `status` y `alert` de las notificaciones.

## Siguientes componentes

- [ ] Crear un `ConfirmDialog` reutilizable.
- [ ] Sustituir la confirmación de salida con cambios sin guardar.
- [ ] Sustituir la confirmación de eliminación de puntos de entrega.
- [ ] Revisar los botones nativos restantes y migrar las acciones normales a `BaseButton`.
- [ ] Extraer la paginación repetida a `BasePagination`.
- [ ] Extraer las pestañas de Mi Cuenta a un componente específico.
- [ ] Revisar los botones de icono y añadir nombres accesibles.
- [ ] Mantener los enlaces de navegación como `router-link` en lugar de convertirlos en botones.

## Mejoras opcionales de notificaciones

- [ ] Pausar el temporizador mientras el usuario mantiene el foco o el cursor sobre un toast.
- [ ] Valorar la agrupación de notificaciones duplicadas.
- [ ] Valorar títulos predeterminados por variante.
