# ACF Repeater for Elementor

**Plugin de WordPress** que permite mostrar campos Repeater y Group de **Advanced Custom Fields PRO** directamente en el editor de **Elementor**, sin escribir código.

---

## Características

- **Modo Sub-campos** — selecciona y estiliza cada sub-campo individualmente desde el panel de Elementor.
- **Modo HTML libre** — escribe una plantilla HTML por fila usando tokens `{{campo}}`, `{{campo:url}}`, `{{campo:kses}}`, `{{_index}}`.
- **Modo Plantilla** — renderiza una plantilla de la biblioteca de Elementor por cada fila del repeater.
- **Dynamic Tags** — etiquetas dinámicas (texto, imagen, URL, número) para usar dentro de cualquier widget de Elementor.
- **Layouts**: Grid responsivo, Lista, Tabla o Texto inline.
- **Filtros y orden**: ordena por sub-campo, limita filas, salta N primeras.
- **Fuentes de datos flexibles**: post actual, página de opciones, usuario actual, autor del post, término actual o cualquier post por ID.

---

## Requisitos

| Dependencia | Versión mínima |
|---|---|
| PHP | 7.4 |
| WordPress | 5.9 |
| Elementor (free o Pro) | 3.x |
| Advanced Custom Fields PRO | 6.x |

> **Nota:** se requiere ACF **PRO** para acceder a los campos Repeater y Group.

---

## Instalación

1. Descarga o clona este repositorio.
2. Sube la carpeta `acf-repeater-blocks` a `/wp-content/plugins/`.
3. Activa el plugin desde **WordPress › Plugins**.
4. Asegúrate de que **Elementor** y **ACF PRO** estén activos.

### Instalación vía ZIP

```bash
# Genera el ZIP desde la raíz del repositorio
zip -r acf-repeater-blocks.zip acf-repeater-blocks/
```

Luego: **Plugins › Añadir nuevo › Subir plugin**.

---

## Uso rápido

### Modo Sub-campos

1. Abre Elementor y busca el widget **"ACF Repeater"** en la categoría *🔁 ACF Repeater*.
2. Selecciona el campo Repeater/Group en **Fuente de datos**.
3. En el panel, selecciona **Modo: Sub-campos** y añade los sub-campos que quieras mostrar.
4. Ajusta layout, colores y tipografía desde el panel de estilo.

### Modo HTML libre

Usa tokens en tu plantilla HTML:

```html
<div class="card">
  <img src="{{foto:url}}" alt="{{nombre}}">
  <h3>{{nombre}}</h3>
  <p>{{descripcion:kses}}</p>
  <span>Fila {{_index}}</span>
</div>
```

| Token | Escape aplicado |
|---|---|
| `{{campo}}` | `esc_html` |
| `{{campo:url}}` | `esc_url` |
| `{{campo:kses}}` | `wp_kses_post` |
| `{{campo:attr}}` | `esc_attr` |
| `{{_index}}` | índice de fila (0, 1, 2…) |

### Modo Plantilla

1. Ve a **Elementor › Mis Plantillas › Nueva plantilla** (tipo *Sección* o *Contenedor*).
2. Diseña el ítem de la lista.
3. En cada widget usa el icono **⚡ Dynamic Tags** y elige *ACF Repeater › campo*.
4. Guarda y selecciona esa plantilla en el widget.

---

## Estructura del proyecto

```
acf-repeater-blocks/
├── acf-repeater-blocks.php          # Bootstrap del plugin
├── assets/
│   └── frontend.css                 # Estilos base (grid, list, table)
└── includes/
    ├── class-arb-acf-helpers.php    # Helpers estáticos para ACF
    ├── class-arb-widget.php         # Widget de Elementor + ARB_Loop_Context
    └── dynamic-tags/
        └── class-arb-tags.php       # Dynamic tags (Text, Image, URL, Number)
```

---

## Seguridad

- Todos los outputs usan `esc_html`, `esc_url`, `esc_attr` o `wp_kses_post`.
- El modo HTML filtra atributos `on*` y etiquetas `<script>`.
- La opción `{{campo:raw}}` solo está disponible para usuarios con `edit_posts`.
- Ver [`SECURITY.md`](SECURITY.md) para reportar vulnerabilidades.

---

## Contribuir

¡Las contribuciones son bienvenidas! Lee [`CONTRIBUTING.md`](CONTRIBUTING.md) antes de abrir una PR.

---

## Changelog

Ver [`CHANGELOG.md`](CHANGELOG.md).

---

## Licencia

Distribuido bajo la licencia **GNU GPL v2.0 o posterior**. Ver [`LICENSE`](LICENSE).

> Este plugin puede comercializarse como producto premium bajo los términos de la GPL.
> La GPL permite la venta, pero obliga a distribuir el código fuente a quienes reciban el software.
