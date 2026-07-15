# ProxiMarkt - notas para agentes

Este archivo debe mantenerse ligero. La documentación extensa vive en `docs/`.

## Lectura obligatoria

- Estado local, ejecución y seeders: [docs/estado_local.md](docs/estado_local.md)
- Flujo Git y patrón de commits: [docs/flujo_git.md](docs/flujo_git.md)
- Roadmap priorizado para portfolio: [docs/roadmap_portfolio.md](docs/roadmap_portfolio.md)
- Backlog detallado de producto/deuda técnica: [docs/backlog_producto.md](docs/backlog_producto.md)

## Contexto mínimo

- Proyecto Laravel + Vue/Vite ejecutado en local con Docker Compose.
- `origin` correcto: `https://github.com/eliseumarmon/proxi-markt.git`.
- El remoto antiguo del fork AWS fue eliminado; queda solo `origin`.
- `main` debe ser estable, demostrable y desplegable.
- Hay cambios locales pendientes traidos desde `origin/despliegue` que no son puramente de despliegue. Revisar siempre `git status --short --branch` antes de tocar nada.
- El despliegue AWS/Azure/Traefik se considera histórico; el nuevo objetivo es VPS.

## Normas rápidas

- Crear ramas cortas desde `main`:
  - `feature/*` para nuevas funcionalidades.
  - `fix/*` para correcciones.
  - `deploy/*` para despliegue/infraestructura.
  - `refactor/*` para reorganización sin cambio funcional.
  - `docs/*` para documentación.
- No mezclar seguridad, diseño, despliegue y features grandes en una misma rama.
- Usar Conventional Commits: `tipo(scope): resumen breve`.
- Antes de mergear a `main`, ejecutar las comprobaciones razonables del cambio: build frontend, tests backend y auditorías si aplica.

## Prioridad actual

1. Seguridad crítica, dependencias vulnerables y tests mínimos de autorización.
2. Despliegue VPS limpio y documentado.
3. Refactor de chat/compraventa como flujo profesional: chat por pedido, estados, cierre, acciones rápidas y widget.
4. Tiempo real con Reverb/WebSockets, Redis, presencia y notificaciones.
5. Pagos de prueba con Stripe.
6. Perfil público de vendedor, valoraciones y métricas de confianza.
7. Componentes UI reutilizables, responsive y mini design system.
8. Dashboard con métricas útiles.
9. Calendario agrícola y reservas anticipadas como feature diferencial.

## Ejecución local rápida

Backend:

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

URLs habituales:

- Frontend: `http://127.0.0.1:5173/`
- Backend: `http://127.0.0.1:8080/`
