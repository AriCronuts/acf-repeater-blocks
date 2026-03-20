<?php
/**
 * ARB_Tags — Registra los dynamic tags del grupo "ACF Repeater".
 *
 * Estos tags leen de ARB_Loop_Context cuando están dentro del modo Plantilla.
 * También funcionan solos (fuera del loop) indicando campo + índice manual.
 */
defined( 'ABSPATH' ) || exit;

class ARB_Tags {

    const GROUP = 'arb-acf-repeater';

    public static function register( $manager ): void {
        $manager->register_group( self::GROUP, [ 'title' => '🔁 ACF Repeater' ] );
        $manager->register( new ARB_Tag_Text()   );
        $manager->register( new ARB_Tag_Image()  );
        $manager->register( new ARB_Tag_URL()    );
        $manager->register( new ARB_Tag_Number() );
    }
}

// ── Clase base ────────────────────────────────────────────────────────────────

abstract class ARB_Tag_Base extends \Elementor\Core\DynamicTags\Tag {

    public function get_group(): array { return [ ARB_Tags::GROUP ]; }

    protected function register_controls(): void {

        $this->add_control( 'arb_field', [
            'label'       => 'Campo Repeater / Group',
            'type'        => \Elementor\Controls_Manager::SELECT,
            'options'     => ARB_ACF_Helpers::get_repeater_options(),
            'description' => 'Campo padre (repeater o group).',
        ] );

        $this->add_control( 'arb_sub', [
            'label'       => 'Sub-campo',
            'type'        => \Elementor\Controls_Manager::SELECT,
            'options'     => ARB_ACF_Helpers::get_sub_field_options(),
            'description' => 'Sub-campo a leer.',
        ] );

        $this->add_control( 'arb_index', [
            'label'       => 'Índice de fila',
            'type'        => \Elementor\Controls_Manager::NUMBER,
            'default'     => 0,
            'min'         => 0,
            'description' => 'Solo necesario fuera de un Loop widget. Dentro del loop se ignora.',
        ] );

        $this->add_control( 'arb_fallback', [
            'label' => 'Texto si está vacío',
            'type'  => \Elementor\Controls_Manager::TEXT,
        ] );
    }

    /**
     * Lee el valor correcto: contexto loop activo → fila actual. Si no → ACF directo.
     */
    protected function get_value() {
        $sub      = ARB_ACF_Helpers::sanitize_field_name( $this->get_settings( 'arb_sub' ) );
        $fallback = $this->get_settings( 'arb_fallback' );

        if ( ! $sub ) return $fallback;

        // Dentro de un loop
        if ( ARB_Loop_Context::is_active() ) {
            $val = ARB_Loop_Context::get( $sub );
            return ( $val !== '' && $val !== null ) ? $val : $fallback;
        }

        // Fuera del loop: leer directamente de ACF
        $field = ARB_ACF_Helpers::sanitize_field_name( $this->get_settings( 'arb_field' ) );
        $index = (int) $this->get_settings( 'arb_index' );
        if ( ! $field ) return $fallback;

        $rows = get_field( $field );
        if ( ! is_array( $rows ) || ! isset( $rows[ $index ][ $sub ] ) ) return $fallback;

        $val = $rows[ $index ][ $sub ];
        return ( $val !== '' && $val !== null ) ? $val : $fallback;
    }
}

// ── Text ──────────────────────────────────────────────────────────────────────

class ARB_Tag_Text extends ARB_Tag_Base {
    public function get_name(): string  { return 'arb-text'; }
    public function get_title(): string { return 'ACF Repeater › Texto'; }
    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
        ];
    }
    public function render(): void {
        $v = $this->get_value();
        if ( is_array( $v ) ) {
            $v = implode( ', ', array_map( 'strval', $v ) );
        }
        echo wp_kses_post( (string) $v );
    }
}

// ── Image ─────────────────────────────────────────────────────────────────────

class ARB_Tag_Image extends ARB_Tag_Base {
    public function get_name(): string  { return 'arb-image'; }
    public function get_title(): string { return 'ACF Repeater › Imagen'; }
    public function get_categories(): array {
        return [ \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY ];
    }
    public function render(): void {
        $v = $this->get_value();
        $id  = 0;
        $url = '';
        if ( is_array( $v ) ) {
            $id  = $v['ID'] ?? $v['id'] ?? 0;
            $url = $v['url'] ?? '';
        } elseif ( is_numeric( $v ) ) {
            $id  = (int) $v;
            $url = wp_get_attachment_url( $id ) ?: '';
        } else {
            $url = (string) $v;
        }
        $this->print_tag_template_content( [ 'id' => $id, 'url' => esc_url( $url ) ] );
    }
}

// ── URL ───────────────────────────────────────────────────────────────────────

class ARB_Tag_URL extends ARB_Tag_Base {
    public function get_name(): string  { return 'arb-url'; }
    public function get_title(): string { return 'ACF Repeater › URL / Enlace'; }
    public function get_categories(): array {
        return [ \Elementor\Modules\DynamicTags\Module::URL_CATEGORY ];
    }
    public function render(): void {
        $v = $this->get_value();
        if ( is_array( $v ) ) {
            echo esc_url( $v['url'] ?? '' );
        } else {
            echo esc_url( (string) $v );
        }
    }
}

// ── Number ────────────────────────────────────────────────────────────────────

class ARB_Tag_Number extends ARB_Tag_Base {
    public function get_name(): string  { return 'arb-number'; }
    public function get_title(): string { return 'ACF Repeater › Número'; }
    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }
    public function render(): void {
        echo esc_html( (string) $this->get_value() );
    }
}
