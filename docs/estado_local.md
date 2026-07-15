# Estado local del proyecto

Actualizado el 2026-07-15.

## Contexto actual

- Proyecto Laravel + Vue/Vite ejecutado en local con Docker Compose.
- Rama principal local: `main`, alineada con `origin/main`.
- Remoto activo único: `origin` -> `https://github.com/eliseumarmon/proxi-markt.git`.
- El despliegue AWS/Azure/Traefik se considera historico. El objetivo nuevo es VPS.
- El proyecto se esta reorientando como portfolio hacia un marketplace de confianza: compra trazable, chat por pedido, pagos de prueba, perfil publico de vendedor, metricas y calendario agricola.

## Git local

Estado de `main`:

- `main` esta en el commit `aae4c1b` y coincide con `origin/main`.
- El working tree sigue sucio con cambios locales pendientes.
- Antes de tocar codigo, ejecutar:

```bash
git status --short --branch
```

Cambios locales pendientes relevantes:

- Cambios traidos desde `origin/despliegue` que no eran puramente de despliegue.
- Documentacion nueva/actualizada:
  - `AGENTS.md`
  - `docs/backlog_producto.md`
  - `docs/estado_local.md`
  - `docs/flujo_git.md`
  - `docs/roadmap_portfolio.md`
- Frontend con cambios en componentes, `vite.config.js`, `App.vue`, `api/axios.js`, assets y utilidades.
- Backend con cambios en `MensajesController.php`.
- `docker-compose.yaml` permite sobrescribir el puerto con `BACKEND_PORT`, pero el puerto local habitual vuelve a ser `8080`.

Worktrees/ramas locales creadas para separar cambios procedentes de `origin/codi_comentat`:

- `/tmp/proxi-markt-feature-favoritos` -> `feature/favoritos`, commit `6c328fa`.
- `/tmp/proxi-markt-feature-incidencias` -> `feature/incidencias`, commit `b060b64`.
- `/tmp/proxi-markt-feature-admin-categorias` -> `feature/admin-categorias`, commit `85a4fcb`.

Estas ramas locales todavia no estan pusheadas por falta de credenciales GitHub en esta sesion.

Ramas remotas visibles:

- `origin/main`
- `origin/despliegue`
- `origin/codi_comentat`
- `origin/develop`
- `origin/gh-pages`

Notas:

- `origin/develop` ya estaba integrado en `origin/main`; se considero seguro borrarla, pero no se pudo eliminar desde esta sesion por falta de credenciales.
- `origin/codi_comentat` no deberia borrarse hasta que las ramas locales limpias creadas desde ella esten pusheadas o respaldadas.
- No usar `develop` como flujo principal. Ver [flujo_git.md](flujo_git.md).

## Direccion de producto

La prioridad actual esta documentada en [roadmap_portfolio.md](roadmap_portfolio.md).

Resumen:

1. Seguridad critica, dependencias vulnerables y tests minimos de autorizacion.
2. Despliegue VPS limpio y documentado.
3. Refactor de chat/compraventa como flujo profesional: chat por pedido, estados, cierre, acciones rapidas y widget.
4. Tiempo real con Reverb/WebSockets, Redis, presencia y notificaciones.
5. Pagos de prueba con Stripe.
6. Perfil publico de vendedor, valoraciones y metricas de confianza.
7. Componentes UI reutilizables, responsive y mini design system.
8. Dashboard con metricas utiles.
9. Calendario agricola y reservas anticipadas como feature diferencial.

El backlog completo vive en [backlog_producto.md](backlog_producto.md).

## Ejecucion local

Backend y base de datos:

```bash
docker compose up -d --build
docker compose exec php composer install
docker compose exec php php artisan key:generate --force
```

Frontend:

```bash
cd frontend
VITE_BACKEND_URL=http://127.0.0.1:8080 npm run dev -- --host 127.0.0.1
```

URLs locales habituales:

- Frontend Vite: `http://127.0.0.1:5173/`
- Backend API/Nginx: `http://127.0.0.1:8080/`

Si el puerto `8080` vuelve a estar ocupado, se puede levantar el backend con otro puerto usando `BACKEND_PORT`, por ejemplo `BACKEND_PORT=8081 docker compose up -d --build`.

## Base de datos y seeders

La BD local usa MySQL en Docker:

- Base de datos: `proxi_markt`
- Usuario: `alumno`
- Password: `alumno`

Importante: las tablas principales de la app (`usuarios`, `categorias`, `puntos_entrega`, `productos`, `chats`, `mensajes`, `compraventas`, `valoraciones`) vienen de `docker/database/base.sql`, no de las migraciones de Laravel. No usar `php artisan migrate:fresh` sin revisar antes, porque puede dejar la BD sin el esquema real de la app.

Seeder completo ejecutado en local:

```bash
docker compose exec php php artisan db:seed --force
```

Resultado tras el seed del 2026-07-15:

- `usuarios`: 13
- `categorias`: 10
- `puntos_entrega`: 26
- `productos`: 78
- `chats`: 0
- `mensajes`: 0
- `compraventas`: 0
- `valoraciones`: 0

El `DatabaseSeeder` llama a `CategoriaSeeder`, crea 10 usuarios con `UserFactory`, y luego crea puntos de entrega y productos para todos los usuarios existentes.

Usuarios de prueba:

- Los usuarios generados por `UserFactory` tienen password `1234`.
- Usuario demo creado manualmente: `demo@proximarkt.test` / `1234`.
- Los usuarios antiguos `e@e.com` y `el@e.com` ya existian en la BD y no tienen password `1234`.

Para consultar usuarios:

```bash
docker compose exec mysql mysql -ualumno -palumno -D proxi_markt -e "SELECT id, nombre_usuario, email, created_at FROM usuarios ORDER BY id;"
```
