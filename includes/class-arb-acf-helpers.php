<?php
/**
 * ARB_ACF_Helpers — Utilidades para leer la estructura de campos ACF.
 */
defined( 'ABSPATH' ) || exit;

class ARB_ACF_Helpers {

    /** @var array|null Caché de todos los field groups con sus campos. */
    private static ?array $_groups_cache = null;

    /**
     * Devuelve todos los field groups con sus campos, usando caché estático.
     * Evita llamadas repetidas a acf_get_field_groups() + acf_get_fields()
     * en una misma petición.
     */
    private static function get_all_groups_with_fields(): array {
        if ( self::$_groups_cache !== null ) {
            return self::$_groups_cache;
        }
        self::$_groups_cache = [];
        foreach ( acf_get_field_groups() as $group ) {
            $group['_fields']     = acf_get_fields( $group['key'] ) ?: [];
            self::$_groups_cache[] = $group;
        }
        return self::$_groups_cache;
    }

    /**
     * Devuelve [ 'nombre' => 'Grupo: Label (nombre)' ] de todos los repeaters/groups.
     */
    public static function get_repeater_options(): array {
        $options = [ '' => '— Selecciona un Repeater / Group —' ];
        foreach ( self::get_all_groups_with_fields() as $group ) {
            foreach ( $group['_fields'] as $field ) {
                if ( in_array( $field['type'], [ 'repeater', 'group' ], true ) ) {
                    $options[ $field['name'] ] = $group['title'] . ': ' . $field['label'] . ' (' . $field['name'] . ')';
                }
            }
        }
        return $options;
    }

    /**
     * Devuelve los sub-campos de un repeater/group dado como [ 'nombre' => 'Label (nombre) [type]' ].
     */
    public static function get_sub_field_options( string $field_name = '' ): array {
        $options = [ '' => '— Sub-campo —' ];
        if ( ! $field_name ) {
            // Devolver todos los sub-campos de todos los repeaters (para cuando aún no se ha elegido)
            foreach ( self::get_all_groups_with_fields() as $group ) {
                foreach ( $group['_fields'] as $field ) {
                    if ( ! in_array( $field['type'], [ 'repeater', 'group' ], true ) ) continue;
                    foreach ( $field['sub_fields'] ?? [] as $sub ) {
                        $options[ $sub['name'] ] = $field['label'] . ' › ' . $sub['label'] . ' [' . $sub['type'] . ']';
                    }
                }
            }
            return $options;
        }

        // Buscar el campo específico
        foreach ( self::get_all_groups_with_fields() as $group ) {
            foreach ( $group['_fields'] as $field ) {
                if ( $field['name'] === $field_name ) {
                    foreach ( $field['sub_fields'] ?? [] as $sub ) {
                        $options[ $sub['name'] ] = $sub['label'] . ' [' . $sub['type'] . ']';
                    }
                    return $options;
                }
            }
        }
        return $options;
    }

    /**
     * Devuelve el tipo ACF de un sub-campo (text, image, url, etc.)
     */
    public static function get_sub_field_type( string $sub_field_name ): string {
        foreach ( self::get_all_groups_with_fields() as $group ) {
            foreach ( $group['_fields'] as $field ) {
                foreach ( $field['sub_fields'] ?? [] as $sub ) {
                    if ( $sub['name'] === $sub_field_name ) {
                        return $sub['type'];
                    }
                }
            }
        }
        return 'text';
    }

    /**
     * Opciones de plantillas Elementor guardadas.
     */
    public static function get_template_options(): array {
        $options = [ '' => '— Selecciona una plantilla —' ];
        $posts   = get_posts( [
            'post_type'      => 'elementor_library',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );
        foreach ( $posts as $p ) {
            $type = get_post_meta( $p->ID, '_elementor_template_type', true );
            $options[ $p->ID ] = $p->post_title . ' [' . $type . ']';
        }
        return $options;
    }

    /**
     * Sanea un nombre de campo ACF (solo letras, números, guiones y _).
     */
    public static function sanitize_field_name( $raw ): string {
        return sanitize_key( (string) $raw );
    }

    /**
     * Resuelve el post_id de ACF según el contexto elegido.
     *
     * @param string $from        current_post | options | current_user | current_term
     * @param mixed  $custom_id   Post ID manual (si $from = 'other')
     * @return int|string|null    null = post actual
     */
    public static function resolve_post_id( string $from, $custom_id = null ) {
        switch ( $from ) {
            case 'options':
                return 'options';
            case 'current_user':
                return 'user_' . get_current_user_id();
            case 'current_author':
                return 'user_' . (int) get_the_author_meta( 'ID' );
            case 'current_term':
                $obj = get_queried_object();
                return ( $obj instanceof WP_Term )
                    ? $obj->taxonomy . '_' . $obj->term_id
                    : null;
            case 'other':
                $id = absint( $custom_id );
                return $id > 0 ? $id : null;
            default:
                return null; // post actual
        }
    }

    /**
     * Valida y devuelve un tag HTML seguro.
     */
    public static function safe_tag( string $tag, string $default = 'span' ): string {
        $allowed = [ 'span', 'p', 'div', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'em', 'li', 'td', 'th' ];
        return in_array( $tag, $allowed, true ) ? $tag : $default;
    }

    /**
     * Renderiza el valor de un sub-campo ACF según su tipo.
     * Llama dentro de have_rows() loop.
     *
     * @param  string $sub_field_name
     * @param  string $forced_type    Tipo forzado desde el panel ('auto' = detectar)
     * @param  array  $image_size     Parámetros de tamaño de imagen
     * @return string  HTML listo para output (ya escapado)
     */
    public static function render_sub_field_value(
        string $sub_field_name,
        string $forced_type = 'auto',
        array  $image_size  = []
    ): string {

        $value = get_sub_field( $sub_field_name );

        if ( $value === false || $value === null || $value === '' ) {
            return '';
        }

        $type = $forced_type === 'auto'
            ? self::get_sub_field_type( $sub_field_name )
            : $forced_type;

        switch ( $type ) {

            case 'image':
                if ( is_array( $value ) ) {
                    $src = $value['url'] ?? '';
                    $alt = esc_attr( $value['alt'] ?? '' );
                    // Tamaño de imagen
                    if ( ! empty( $image_size['size'] ) && isset( $value['sizes'][ $image_size['size'] ] ) ) {
                        $src = $value['sizes'][ $image_size['size'] ];
                    }
                } elseif ( is_numeric( $value ) ) {
                    $img = wp_get_attachment_image_src( (int) $value, $image_size['size'] ?? 'large' );
                    $src = $img ? $img[0] : '';
                    $alt = esc_attr( get_post_meta( (int) $value, '_wp_attachment_image_alt', true ) );
                } else {
                    $src = (string) $value;
                    $alt = '';
                }
                return '<img src="' . esc_url( $src ) . '" alt="' . $alt . '" loading="lazy">';

            case 'wysiwyg':
                return wp_kses_post( wpautop( (string) $value ) );

            case 'url':
            case 'page_link':
                return esc_url( (string) $value );

            case 'link':
                // ACF link field devuelve array { url, title, target }
                if ( is_array( $value ) ) {
                    return esc_url( $value['url'] ?? '' );
                }
                return esc_url( (string) $value );

            case 'true_false':
                return $value ? esc_html__( 'Sí', 'arb' ) : esc_html__( 'No', 'arb' );

            case 'date_picker':
            case 'date_time_picker':
            case 'time_picker':
            case 'color_picker':
            case 'number':
            case 'range':
            case 'email':
            case 'select':
            case 'radio':
            case 'checkbox':
            case 'text':
            case 'textarea':
            default:
                if ( is_array( $value ) ) {
                    return esc_html( implode( ', ', array_map( 'strval', $value ) ) );
                }
                return esc_html( (string) $value );
        }
    }
}
