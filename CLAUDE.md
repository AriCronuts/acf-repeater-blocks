# Instrucciones para rutinas automáticas

## Rutina de revisión diaria (frontend / seguridad)

Cada sesión de revisión diaria **debe terminar con el PR mergeado**, no simplemente abierto.

### Flujo obligatorio al final de cada revisión

1. **Antes de crear el PR**, comprueba si hay PRs abiertos contra `main` que toquen los mismos archivos (`assets/accordion.js`, `assets/frontend.css`, `includes/class-arb-accordion-widget.php`, `includes/class-arb-widget.php`). Si los hay:
   - Ciérralos con `gh pr close <num>` o via API.
   - Borra sus ramas.
   - Luego crea tu PR desde `main` actualizado.

2. **Después de crear el PR**, comprueba `mergeable_state`:
   - Si es `clean` → mergea inmediatamente con squash y borra la rama:
     ```
     gh pr merge <num> --squash --delete-branch
     ```
   - Si es `dirty` (conflicto) → significa que main cambió entre el checkout y el PR. Haz rebase de tu rama sobre main, resuelve los conflictos, y vuelve a intentar el merge.

3. **El objetivo es que al finalizar la sesión no quede ningún PR de revisión diaria abierto.** Un PR abierto sin mergear es un conflicto garantizado para la siguiente revisión.

### Por qué es importante

Cuando varias revisiones diarias acumulan PRs abiertos sin mergear, todos tocan las mismas zonas de `accordion.js` y `frontend.css`. El segundo PR ya no puede mergearse limpiamente. A partir del tercero la situación es irrecuperable sin intervención manual.

### Rama de trabajo

Siempre crea la rama desde el `HEAD` actualizado de `main`:
```bash
git fetch origin
git checkout -b fix/frontend-$(date +%Y-%m-%d) origin/main
```

Nunca reutilices una rama anterior ni crees la rama desde un commit desactualizado.
