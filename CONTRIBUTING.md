# Guía de contribución

¡Gracias por querer mejorar ACF Repeater for Elementor! Este documento explica cómo reportar bugs, proponer mejoras y enviar pull requests.

---

## Tabla de contenidos

- [Código de conducta](#código-de-conducta)
- [Reportar un bug](#reportar-un-bug)
- [Proponer una mejora](#proponer-una-mejora)
- [Configurar el entorno local](#configurar-el-entorno-local)
- [Proceso de pull request](#proceso-de-pull-request)
- [Estándares de código](#estándares-de-código)
- [Convenciones de commits](#convenciones-de-commits)

---

## Código de conducta

Se espera un trato profesional y respetuoso en todas las interacciones del repositorio.

---

## Reportar un bug

1. **Busca primero** en los [Issues abiertos](../../issues) para evitar duplicados.
2. Abre un nuevo Issue usando la plantilla **Bug report**.
3. Incluye siempre:
   - Versión del plugin, WordPress, Elementor y ACF PRO.
   - Pasos exactos para reproducir el problema.
   - Comportamiento esperado vs. comportamiento actual.
   - Capturas de pantalla o mensajes de error si aplica.
   - Resultado de activar **WP_DEBUG** si hay errores PHP.

---

## Proponer una mejora

1. Abre un Issue con la etiqueta **enhancement** antes de escribir código.
2. Describe el caso de uso concreto y por qué aporta valor.
3. Espera feedback del equipo para validar la dirección antes de invertir tiempo.

---

## Configurar el entorno local

### Opción A — wp-env (recomendado)

```bash
# Requiere Node.js y Docker
npm install -g @wordpress/env
wp-env start
```

Esto levanta WordPress en `http://localhost:8888` con el plugin cargado.

### Opción B — Local / XAMPP / Lando

1. Instala WordPress localmente.
2. Instala y activa **Elementor** y **ACF PRO**.
3. Clona el repositorio en `wp-content/plugins/acf-repeater-blocks`.

```bash
git clone git@github.com:TU-ORG/acf-repeater-blocks.git wp-content/plugins/acf-repeater-blocks
```

4. Activa el plugin desde el panel de WordPress.

---

## Proceso de pull request

1. **Crea una rama** desde `main` con un nombre descriptivo:
   ```bash
   git checkout -b fix/imagen-sin-alt
   git checkout -b feat/soporte-campo-gallery
   ```

2. **Escribe código limpio** siguiendo los estándares de abajo.

3. **Prueba manualmente** los tres modos (Sub-campos, HTML, Plantilla) y los Dynamic Tags.

4. **Actualiza `CHANGELOG.md`** bajo la sección `[Unreleased]`.

5. Abre la PR contra la rama `main`:
   - Título claro y conciso.
   - Descripción del cambio y por qué es necesario.
   - Enlaza el Issue relacionado con `Closes #123`.

6. Espera revisión. Puede pedirse cambios antes de aprobar.

---

## Estándares de código

El plugin sigue las [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).

Puntos clave:

- **Escapado obligatorio** en todo output: `esc_html()`, `esc_url()`, `esc_attr()`, `wp_kses_post()`.
- **Sanitización** de toda entrada: `sanitize_text_field()`, `sanitize_key()`, `absint()`, etc.
- **Prefijo `arb_` / clase `ARB_`** en todas las funciones, clases y constantes públicas para evitar conflictos.
- **No usar funciones deprecadas** de Elementor o ACF.
- Comentarios en español son aceptables; el código en inglés también.

### Verificar con PHP_CodeSniffer

```bash
composer require --dev wp-coding-standards/wpcs dealerdirect/phpcodesniffer-composer-installer
./vendor/bin/phpcs --standard=WordPress includes/ acf-repeater-blocks.php
```

---

## Convenciones de commits

Usa el formato [Conventional Commits](https://www.conventionalcommits.org/):

```
tipo(scope): descripción corta en imperativo
```

| Tipo | Cuándo usarlo |
|---|---|
| `fix` | Corrección de un bug |
| `feat` | Nueva funcionalidad |
| `refactor` | Cambio de código sin cambio de comportamiento |
| `style` | Formato, espacios, punto y coma (sin lógica) |
| `docs` | Solo documentación |
| `chore` | Tareas de mantenimiento (deps, CI…) |

**Ejemplos:**

```
fix(widget): evitar error fatal si ACF devuelve null en campo Group
feat(tags): añadir dynamic tag para campos de tipo File
docs(readme): actualizar instrucciones de instalación
```
