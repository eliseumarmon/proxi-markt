# ProxiMarkt

ProxiMarkt es una plataforma web para conectar productores locales con consumidores, facilitando la venta de productos de proximidad, la gestión de puntos de entrega, solicitudes de compra, chat, valoraciones y seguimiento de comandas.

## Estado actual del proyecto

Este proyecto nació originalmente como un trabajo académico desarrollado en equipo durante el ciclo de Desarrollo de Aplicaciones Web. Actualmente se mantiene como proyecto personal de portfolio, con el objetivo de evolucionarlo hacia un marketplace más completo, mantenible y desplegable en producción.

La versión actual está siendo revisada y reorganizada para mejorar:

- seguridad y autorización en backend;
- despliegue en VPS;
- arquitectura del chat y flujo de compraventa;
- pagos de prueba con Stripe;
- perfil público de vendedores y métricas de confianza;
- componentes reutilizables y experiencia responsive;
- documentación técnica y datos de demo.

## Origen académico

La primera versión de ProxiMarkt fue desarrollada como proyecto intermodular de 2º curso del Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web.

Participantes del proyecto original:

- Álvarez Calle, Inés
- Camarena Ureña, David
- Grau Andrés, Jordi
- Martínez Monrabal, Eliseu
- Mogort Brines, Carlos

Esa etapa sirvió para construir la base funcional: autenticación, productos, mapas, puntos de entrega, compras, chat, valoraciones, dashboard y documentación inicial.

A partir de esta fase, el proyecto continúa como una evolución individual orientada a portfolio profesional y mejora técnica progresiva.

## Funcionalidades actuales

- Registro, login y sesión de usuario con Laravel Sanctum.
- Catálogo de productos de proximidad.
- Publicación y edición de productos con imagen, categoría, precio y stock.
- Puntos de entrega asociados a vendedores.
- Mapa con Leaflet para buscar puntos cercanos.
- Solicitudes de compra y gestión de comandas.
- Chat entre comprador y vendedor.
- Sistema de valoraciones.
- Favoritos.
- Gestión básica de categorías e incidencias.
- Dashboard inicial de actividad.

## Stack técnico

Backend:

- PHP 8.2
- Laravel 12
- Laravel Sanctum
- Laravel Reverb instalado
- MySQL 8
- Eloquent ORM
- PHPUnit
- Laravel Pint

Frontend:

- Vue 3
- Vite 7
- Vue Router
- Axios
- Bootstrap 5
- Bootstrap Icons
- Leaflet

Infraestructura local:

- Docker Compose
- Nginx
- PHP-FPM
- MySQL

## Ejecución local

Backend y base de datos:

```bash
docker compose up -d --build
docker compose exec php composer install
docker compose exec php php artisan key:generate --force
docker compose exec php php artisan storage:link
```

Frontend:

```bash
cd frontend
npm install
VITE_BACKEND_URL=http://127.0.0.1:8080 npm run dev -- --host 127.0.0.1
```

URLs habituales:

- Frontend: `http://127.0.0.1:5173/`
- Backend/API: `http://127.0.0.1:8080/`

## Base de datos

La base de datos local se inicializa con MySQL desde:

```text
docker/database/base.sql
```

Importante: las tablas principales de la aplicación todavía dependen de ese SQL inicial, no de migraciones Laravel completas. No ejecutar `php artisan migrate:fresh` sin revisar antes el estado real del esquema.

Seeders:

```bash
docker compose exec php php artisan db:seed --force
```

Usuario demo local:

```text
demo@proximarkt.test / 1234
```

Los usuarios generados por `UserFactory` usan la contraseña `1234`.

## Roadmap actual

El objetivo es convertir ProxiMarkt en un marketplace de confianza. La prioridad actual es:

1. Seguridad crítica, dependencias vulnerables y tests mínimos de autorización.
2. Despliegue VPS limpio y documentado.
3. Refactor de chat/compraventa como flujo profesional: chat por pedido, estados, cierre, acciones rápidas y widget.
4. Tiempo real con Reverb/WebSockets, Redis, presencia y notificaciones.
5. Pagos de prueba con Stripe.
6. Perfil público de vendedor, valoraciones y métricas de confianza.
7. Componentes UI reutilizables, responsive y mini design system.
8. Dashboard con métricas útiles.
9. Calendario agrícola y reservas anticipadas como feature diferencial.

Más detalle en:

- [docs/roadmap_portfolio.md](docs/roadmap_portfolio.md)
- [docs/backlog_producto.md](docs/backlog_producto.md)
- [docs/estado_local.md](docs/estado_local.md)
- [docs/flujo_git.md](docs/flujo_git.md)

## Documentación

La documentación técnica vive en `docs/` y puede publicarse con MkDocs.

Configuración principal:

```text
mkdocs.yml
```

Publicación manual:

```bash
mkdocs gh-deploy
```

La rama `gh-pages` se usa para servir la documentación generada por GitHub Pages.

## Flujo Git

El flujo recomendado para esta etapa del proyecto es:

- `main`: versión estable, demostrable y desplegable.
- `feature/*`: nuevas funcionalidades.
- `fix/*`: correcciones.
- `deploy/*`: despliegue e infraestructura.
- `refactor/*`: reorganización interna sin cambio funcional.
- `docs/*`: documentación.

No se usa `develop` como flujo principal.

## Licencia y uso

Este repositorio se mantiene como proyecto personal de portfolio y aprendizaje. Si se reutiliza código, documentación o ideas del proyecto, se recomienda citar el origen y respetar el trabajo del equipo original.
