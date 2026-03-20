# Política de seguridad

## Versiones soportadas

| Versión | Soporte de seguridad |
|---|---|
| 1.0.x | ✅ Activo |

---

## Reportar una vulnerabilidad

**No abras un Issue público** para reportar vulnerabilidades de seguridad, ya que podría exponer el problema antes de que esté corregido.

Envía un email a **hello@cronuts.digital** con el asunto `[SECURITY] ACF Repeater for Elementor` e incluye:

1. Descripción detallada de la vulnerabilidad.
2. Pasos para reproducirla (proof of concept si es posible).
3. Impacto estimado (qué datos o acciones quedan expuestos).
4. Tu nombre/alias si quieres aparecer en los créditos.

### Qué esperar

- **Acuse de recibo** en menos de 48 horas (días laborables).
- **Evaluación inicial** en menos de 5 días laborables.
- **Parche y release** coordinados contigo antes de divulgación pública.
- Crédito público en el `CHANGELOG.md` si lo deseas (responsible disclosure).

---

## Prácticas de seguridad del plugin

- Todo output usa `esc_html()`, `esc_url()`, `esc_attr()` o `wp_kses_post()`.
- El modo HTML libre filtra atributos de evento (`on*`) y bloques `<script>`.
- La opción de escape `raw` en Dynamic Tags requiere la capacidad `edit_posts`.
- Los nombres de campo se sanitizan con `sanitize_key()` antes de usarse.
- No se almacenan ni transmiten credenciales ni datos sensibles de usuario.
