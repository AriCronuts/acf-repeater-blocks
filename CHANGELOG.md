# Changelog

Todos los cambios notables de este proyecto se documentan aquí.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es/1.0.0/)
y el proyecto usa [Semantic Versioning](https://semver.org/lang/es/).

---

## [Unreleased]

*(Los cambios en desarrollo se añaden aquí antes de cada release.)*

---

## [1.0.0] — 2026-03-20

### Añadido

- Widget **ACF Repeater** con tres modos de visualización: Sub-campos, HTML libre y Plantilla.
- **Dynamic Tags**: `ARB_Tag_Text`, `ARB_Tag_Image`, `ARB_Tag_URL`, `ARB_Tag_Number`.
- Soporte de fuentes de datos: post actual, página de opciones, usuario actual, autor, término o post por ID.
- Layouts: Grid responsivo, Lista, Tabla y Texto inline.
- Ordenación por sub-campo (ASC/DESC), límite y offset de filas.
- Tokens en modo HTML: `{{campo}}`, `{{campo:url}}`, `{{campo:kses}}`, `{{campo:attr}}`, `{{_index}}`.
- `ARB_Loop_Context` — pila de contexto para modo Plantilla + Dynamic Tags.
- Validación de dependencias (ACF PRO + Elementor) con avisos en el admin.
- CSS base responsivo (`assets/frontend.css`).
- Estilo de ítem contenedor configurable desde el panel de Elementor (padding, fondo, borde, border-radius).
