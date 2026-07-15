# Flujo Git

## Normas de ramas

- No usar `develop` como flujo principal mientras el proyecto sea individual; mete complejidad innecesaria.
- `main` debe ser siempre estable, demostrable y desplegable.
- Usar ramas cortas por tarea concreta.
- Usar `feature/*` para nuevas funcionalidades.
- Usar `fix/*` para correcciones y bugs.
- Usar `deploy/*` para trabajo de despliegue e infraestructura.
- Usar `refactor/*` para reorganizacion interna sin cambio funcional.
- Usar `docs/*` para documentacion.
- Cada rama debe poder explicarse en una frase: "esta rama arregla X" o "esta rama anade Y".
- No mezclar en una misma rama seguridad, diseno, despliegue y features grandes.
- Antes de empezar una rama: `git switch main`, actualizar desde `origin/main` y crear la rama desde ahi.
- Antes de mergear a `main`, ejecutar las comprobaciones razonables del cambio: build frontend, tests backend y auditorias si aplica.

## Patrón de commits

- Usar Conventional Commits en castellano o ingles, pero mantener el idioma consistente dentro de una rama.
- Formato:

```text
tipo(scope): resumen breve en imperativo
```

Tipos permitidos:

- `feat`: nueva funcionalidad.
- `fix`: correccion de bug.
- `refactor`: cambio interno sin modificar comportamiento.
- `style`: cambios visuales/CSS/formato sin cambio funcional.
- `test`: tests.
- `docs`: documentacion.
- `build`: dependencias, build, Docker o tooling.
- `ci`: integracion continua.
- `chore`: mantenimiento sin impacto directo en app.
- `perf`: mejora de rendimiento.
- `security`: correcciones de seguridad/autorizacion.
- `deploy`: cambios de despliegue/infraestructura.

Scopes recomendados:

- `backend`
- `frontend`
- `auth`
- `productos`
- `compraventas`
- `chat`
- `dashboard`
- `ui`
- `docker`
- `vps`
- `docs`
- `tests`
- `deps`

Ejemplos:

```text
security(productos): protege la edicion de productos ajenos
fix(compraventas): valida vendedor y punto antes de reservar stock
feat(chat): emite mensajes en tiempo real con Reverb
feat(pagos): integra Stripe en modo pruebas
refactor(ui): extrae BaseButton y BaseCard
test(auth): cubre login y registro con Sanctum
deploy(vps): separa compose de produccion
docs(readme): anade guia de ejecucion local
```

## Reglas de higiene

- Mantener commits pequenos y revisables.
- No usar mensajes genericos como `cambios`, `fix`, `update`, `cosas` o `wip` en commits que vayan a `main`.
- Si una rama tiene commits temporales `wip`, limpiarlos con squash/rebase antes de mergear.
- Para cambios con ruptura o migraciones importantes, anadir cuerpo al commit explicando impacto y pasos:

```text
feat(cosechas): anade reservas anticipadas

Crea el modelo de campanas/cosechas y permite reservar stock estimado
antes de disponibilidad real. Requiere ejecutar migraciones nuevas.
```

## Uso de `git worktree`

Usar `git worktree` cuando la rama actual tenga cambios sin commitear y sea necesario preparar otra rama sin tocar ese estado. Es especialmente util para rescatar cambios de ramas antiguas, comparar features o preparar commits separados.

Patron recomendado para este repo:

```bash
git worktree add -b feature/nombre-rama /tmp/proxi-markt-feature-nombre-rama origin/main
```

Ejemplos reales usados:

```bash
git worktree add -b feature/favoritos /tmp/proxi-markt-feature-favoritos origin/main
git worktree add -b feature/incidencias /tmp/proxi-markt-feature-incidencias origin/main
git worktree add -b feature/admin-categorias /tmp/proxi-markt-feature-admin-categorias origin/main
```

Para ver worktrees activos:

```bash
git worktree list
```

Para trabajar dentro de uno:

```bash
cd /tmp/proxi-markt-feature-favoritos
git status --short --branch
```

Para traer archivos concretos desde otra rama sin hacer merge completo:

```bash
git checkout origin/codi_comentat -- ruta/al/archivo
```

Para commitear dentro del worktree:

```bash
git add ruta/al/archivo
git commit -m "feat(scope): resumen breve"
```

Para publicar la rama si hay credenciales:

```bash
git push -u origin feature/nombre-rama
```

Para limpiar un worktree cuando ya no haga falta:

```bash
git worktree remove /tmp/proxi-markt-feature-nombre-rama
```

Si el directorio fue borrado manualmente o queda una referencia rota:

```bash
git worktree prune
```

Reglas:

- No editar la misma rama desde dos worktrees a la vez.
- No borrar un worktree con cambios sin revisar.
- Antes de eliminarlo, comprobar `git status --short`.
- Usar `/tmp/proxi-markt-*` para worktrees temporales.
- Mantener commits pequenos y tematicos igual que en el workspace principal.
