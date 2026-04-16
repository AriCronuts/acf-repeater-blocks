<?php
/**
 * ARB_Accordion_Widget — ACF Repeater for Elementor
 * Muestra ítems de un Repeater/Group de ACF como acordeón expandible.
 */
defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class ARB_Accordion_Widget extends \Elementor\Widget_Base {

    // =========================================================================
    // IDENTIDAD
    // =========================================================================

    public function get_name(): string    { return 'arb-accordion'; }
    public function get_title(): string   { return 'ACF Accordion'; }
    public function get_icon(): string    { return 'eicon-accordion'; }
    public function get_categories(): array { return [ 'arb' ]; }
    public function get_keywords(): array {
        return [ 'acf', 'accordion', 'faq', 'repeater', 'group', 'toggle', 'custom fields' ];
    }
    public function get_script_depends(): array { return [ 'arb-accordion' ]; }
    public function get_style_depends(): array  { return [ 'arb-frontend' ]; }

    // =========================================================================
    // CONTROLES
    // =========================================================================

    protected function register_controls(): void {

        // ── SECCIÓN 1: Fuente de datos ────────────────────────────────────────
        $this->start_controls_section( 'sec_source', [
            'label' => '🗄️ Fuente de datos',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'acf_field', [
            'label'   => 'Campo Repeater / Group',
            'type'    => Controls_Manager::SELECT,
            'options' => ARB_ACF_Helpers::get_repeater_options(),
        ] );

        $this->add_control( 'acf_from', [
            'label'   => 'Obtener el campo de',
            'type'    => Controls_Manager::SELECT,
            'default' => 'current_post',
            'options' => [
                'current_post'   => 'Post actual',
                'options'        => 'Página de opciones',
                'current_user'   => 'Usuario actual',
                'current_author' => 'Autor del post',
                'current_term'   => 'Término actual',
                'other'          => 'Otro post (por ID)',
            ],
        ] );

        $this->add_control( 'acf_custom_post_id', [
            'label'     => 'Post ID',
            'type'      => Controls_Manager::NUMBER,
            'min'       => 1,
            'condition' => [ 'acf_from' => 'other' ],
        ] );

        $this->end_controls_section();

        // ── SECCIÓN 2: Configuración del acordeón ─────────────────────────────
        $this->start_controls_section( 'sec_accordion', [
            'label' => '⚙️ Configuración',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'question_field', [
            'label'   => 'Sub-campo Pregunta',
            'type'    => Controls_Manager::SELECT,
            'options' => ARB_ACF_Helpers::get_sub_field_options(),
        ] );

        $this->add_control( 'answer_field', [
            'label'   => 'Sub-campo Respuesta',
            'type'    => Controls_Manager::SELECT,
            'options' => ARB_ACF_Helpers::get_sub_field_options(),
        ] );

        $this->add_control( 'image_field', [
            'label'       => 'Sub-campo Imagen (opcional)',
            'type'        => Controls_Manager::SELECT,
            'options'     => ARB_ACF_Helpers::get_sub_field_options(),
            'description' => 'Déjalo vacío si no hay imagen.',
        ] );

        $this->add_control( 'columns', [
            'label'   => 'Columnas',
            'type'    => Controls_Manager::SELECT,
            'default' => '1',
            'options' => [
                '1' => '1 columna',
                '2' => '2 columnas',
            ],
        ] );

        $this->add_control( 'column_gap', [
            'label'      => 'Espacio entre columnas',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 24 ],
            'condition'  => [ 'columns' => '2' ],
            'selectors'  => [ '{{WRAPPER}} .arb-accordion' => 'column-gap: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'row_gap', [
            'label'      => 'Espacio entre filas',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 12 ],
            'selectors'  => [ '{{WRAPPER}} .arb-accordion' => 'row-gap: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'close_others', [
            'label'        => 'Cerrar otros al abrir uno',
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => 'Sí',
            'label_off'    => 'No',
            'return_value' => '1',
            'default'      => '',
            'separator'    => 'before',
        ] );

        $this->add_control( 'no_results', [
            'label'     => 'Texto si no hay resultados',
            'type'      => Controls_Manager::TEXT,
            'separator' => 'before',
        ] );

        $this->end_controls_section();

        // ── SECCIÓN 3: Iconos ─────────────────────────────────────────────────
        $this->start_controls_section( 'sec_icons', [
            'label' => '🔣 Iconos',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'icon_open_svg', [
            'label'    => 'SVG estado cerrado (icono "abrir")',
            'type'     => Controls_Manager::CODE,
            'language' => 'html',
            'rows'     => 4,
            'default'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
        ] );

        $this->add_control( 'icon_close_svg', [
            'label'    => 'SVG estado abierto (icono "cerrar")',
            'type'     => Controls_Manager::CODE,
            'language' => 'html',
            'rows'     => 4,
            'default'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>',
        ] );

        $this->add_control( 'icon_position', [
            'label'   => 'Posición del icono',
            'type'    => Controls_Manager::SELECT,
            'default' => 'right',
            'options' => [
                'right' => 'Derecha',
                'left'  => 'Izquierda',
            ],
        ] );

        $this->end_controls_section();

        // ── STYLE: Ítem ───────────────────────────────────────────────────────
        $this->start_controls_section( 'sec_style_item', [
            'label' => 'Ítem',
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'item_bg', [
            'label'     => 'Fondo',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-item' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'item_bg_active', [
            'label'     => 'Fondo cuando está abierto',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-item.is-open' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'item_border_color', [
            'label'     => 'Color de borde',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-item' => 'border-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'item_border_width', [
            'label'      => 'Ancho de borde',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 10 ] ],
            'selectors'  => [ '{{WRAPPER}} .arb-acc-item' => 'border-width: {{SIZE}}px; border-style: solid;' ],
        ] );

        $this->add_control( 'item_border_radius', [
            'label'      => 'Border radius',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'selectors'  => [ '{{WRAPPER}} .arb-acc-item' => 'border-radius: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();

        // ── STYLE: Cabecera ───────────────────────────────────────────────────
        $this->start_controls_section( 'sec_style_header', [
            'label' => 'Cabecera',
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_responsive_control( 'header_padding', [
            'label'      => 'Padding',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors'  => [ '{{WRAPPER}} .arb-acc-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
        ] );

        $this->add_control( 'question_color', [
            'label'     => 'Color pregunta',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-question' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'question_color_active', [
            'label'     => 'Color pregunta (abierto)',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-item.is-open .arb-acc-question' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'question_typography',
            'selector' => '{{WRAPPER}} .arb-acc-question',
        ] );

        $this->end_controls_section();

        // ── STYLE: Contenido ──────────────────────────────────────────────────
        $this->start_controls_section( 'sec_style_content', [
            'label' => 'Contenido',
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_responsive_control( 'content_padding', [
            'label'      => 'Padding',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors'  => [ '{{WRAPPER}} .arb-acc-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
        ] );

        $this->add_control( 'content_bg', [
            'label'     => 'Fondo del cuerpo',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-body' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'content_color', [
            'label'     => 'Color de texto',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-content' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'content_typography',
            'selector' => '{{WRAPPER}} .arb-acc-content',
        ] );

        $this->add_control( 'image_max_width', [
            'label'      => 'Ancho máximo imagen',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ '%', 'px' ],
            'range'      => [ '%' => [ 'min' => 10, 'max' => 100 ], 'px' => [ 'min' => 50, 'max' => 800 ] ],
            'default'    => [ 'unit' => '%', 'size' => 100 ],
            'selectors'  => [ '{{WRAPPER}} .arb-acc-content img' => 'max-width: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();

        // ── STYLE: Icono ──────────────────────────────────────────────────────
        $this->start_controls_section( 'sec_style_icon', [
            'label' => 'Icono',
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'icon_size', [
            'label'      => 'Tamaño',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 10, 'max' => 80 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 20 ],
            'selectors'  => [
                '{{WRAPPER}} .arb-acc-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'icon_color', [
            'label'     => 'Color del icono',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [
                '{{WRAPPER}} .arb-acc-icon svg'        => 'color: {{VALUE}}; stroke: {{VALUE}};',
                '{{WRAPPER}} .arb-acc-icon svg path'   => 'fill: {{VALUE}}; stroke: {{VALUE}};',
                '{{WRAPPER}} .arb-acc-icon svg line'   => 'stroke: {{VALUE}};',
                '{{WRAPPER}} .arb-acc-icon svg circle' => 'stroke: {{VALUE}};',
            ],
        ] );

        $this->end_controls_section();
    }

    // =========================================================================
    // RENDER
    // =========================================================================

    protected function render(): void {
        $s     = $this->get_settings_for_display();
        $field = ARB_ACF_Helpers::sanitize_field_name( $s['acf_field'] ?? '' );

        if ( ! $field ) {
            $this->arb_placeholder( 'Selecciona un campo Repeater en el panel.' );
            return;
        }

        $post_id = ARB_ACF_Helpers::resolve_post_id(
            $s['acf_from'] ?? 'current_post',
            $s['acf_custom_post_id'] ?? null
        );

        $rows = get_field( $field, $post_id );

        if ( empty( $rows ) || ! is_array( $rows ) ) {
            if ( ! empty( $s['no_results'] ) ) {
                echo '<p class="arb-no-results">' . esc_html( $s['no_results'] ) . '</p>';
            } elseif ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                $this->arb_placeholder( 'El campo "' . esc_html( $field ) . '" no tiene filas en este post.' );
            }
            return;
        }

        $q_field   = ARB_ACF_Helpers::sanitize_field_name( $s['question_field'] ?? '' );
        $a_field   = ARB_ACF_Helpers::sanitize_field_name( $s['answer_field']   ?? '' );
        $img_field = ARB_ACF_Helpers::sanitize_field_name( $s['image_field']    ?? '' );
        $columns   = in_array( $s['columns'] ?? '1', [ '1', '2' ], true ) ? $s['columns'] : '1';
        $close_others = ! empty( $s['close_others'] ) ? '1' : '0';
        $icon_open    = $this->sanitize_svg( $s['icon_open_svg']  ?? '' );
        $icon_close   = $this->sanitize_svg( $s['icon_close_svg'] ?? '' );
        $icon_pos     = ( ( $s['icon_position'] ?? 'right' ) === 'left' ) ? ' arb-icon-left' : '';

        $acc_class = 'arb-accordion' . ( $columns === '2' ? ' arb-accordion-cols-2' : '' ) . $icon_pos;

        echo '<div class="' . esc_attr( $acc_class ) . '" data-close-others="' . esc_attr( $close_others ) . '">';

        if ( $columns === '2' ) {
            // Split rows into two sequential columns so CSS flex-row + .arb-acc-col layout works.
            $mid      = (int) ceil( count( $rows ) / 2 );
            $col_sets = [
                array_slice( $rows, 0, $mid, true ),
                array_slice( $rows, $mid, null, true ),
            ];
            foreach ( $col_sets as $col_rows ) {
                echo '<div class="arb-acc-col">';
                foreach ( $col_rows as $idx => $row ) {
                    $this->render_accordion_item( $idx, $row, $q_field, $a_field, $img_field, $icon_open, $icon_close );
                }
                echo '</div>';
            }
        } else {
            foreach ( $rows as $idx => $row ) {
                $this->render_accordion_item( $idx, $row, $q_field, $a_field, $img_field, $icon_open, $icon_close );
            }
        }

        echo '</div>';
    }

    /**
     * Renders a single accordion item (header button + collapsible body panel).
     * Extracted so the same markup is used for both 1- and 2-column layouts.
     */
    private function render_accordion_item(
        int    $idx,
        array  $row,
        string $q_field,
        string $a_field,
        string $img_field,
        string $icon_open,
        string $icon_close
    ): void {
        $widget_id = $this->get_id();
        $header_id = 'arb-acc-header-' . $widget_id . '-' . $idx;
        $body_id   = 'arb-acc-body-'   . $widget_id . '-' . $idx;
        $question  = '';
        $answer    = '';
        $img_html  = '';

        if ( $q_field && array_key_exists( $q_field, $row ) ) {
            $v        = $row[ $q_field ];
            $question = is_array( $v )
                ? esc_html( implode( ', ', array_map( 'strval', $v ) ) )
                : esc_html( (string) $v );
        }

        if ( $a_field && array_key_exists( $a_field, $row ) ) {
            $v    = $row[ $a_field ];
            $type = ARB_ACF_Helpers::get_sub_field_type( $a_field );
            if ( is_array( $v ) ) {
                $answer = wp_kses_post( implode( ' ', array_map( 'strval', $v ) ) );
            } elseif ( $type === 'wysiwyg' ) {
                $answer = wp_kses_post( wpautop( (string) $v ) );
            } else {
                $answer = '<p>' . esc_html( (string) $v ) . '</p>';
            }
        }

        if ( $img_field && array_key_exists( $img_field, $row ) ) {
            $img_html = $this->render_image_value( $row[ $img_field ] );
        }

        echo '<div class="arb-acc-item">';

        // aria-controls + id pairing lets AT announce which region the button governs.
        echo '<button class="arb-acc-header" '
            . 'id="' . esc_attr( $header_id ) . '" '
            . 'aria-expanded="false" '
            . 'aria-controls="' . esc_attr( $body_id ) . '">';
        echo '<span class="arb-acc-question">' . $question . '</span>';
        echo '<span class="arb-acc-icon arb-acc-icon--open" aria-hidden="true">'  . $icon_open  . '</span>';
        echo '<span class="arb-acc-icon arb-acc-icon--close" aria-hidden="true">' . $icon_close . '</span>';
        echo '</button>';

        // role="region" + aria-labelledby lets screen readers label the panel with its heading.
        echo '<div class="arb-acc-body" '
            . 'id="' . esc_attr( $body_id ) . '" '
            . 'role="region" '
            . 'aria-labelledby="' . esc_attr( $header_id ) . '" '
            . 'hidden>';
        echo '<div class="arb-acc-content">' . $answer . $img_html . '</div>';
        echo '</div>';

        echo '</div>';
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function render_image_value( $val ): string {
        if ( empty( $val ) ) return '';

        if ( is_array( $val ) ) {
            $src = $val['sizes']['large'] ?? $val['url'] ?? '';
            $alt = esc_attr( $val['alt'] ?? '' );
        } elseif ( is_numeric( $val ) ) {
            $img = wp_get_attachment_image_src( (int) $val, 'large' );
            $src = $img ? $img[0] : '';
            $alt = esc_attr( get_post_meta( (int) $val, '_wp_attachment_image_alt', true ) );
        } else {
            $src = (string) $val;
            $alt = '';
        }

        if ( ! $src ) return '';
        return '<img src="' . esc_url( $src ) . '" alt="' . $alt . '" loading="lazy">';
    }

    private function sanitize_svg( string $raw ): string {
        if ( ! $raw ) return '';

        $allowed = [
            'svg'      => [ 'xmlns' => true, 'width' => true, 'height' => true, 'viewbox' => true,
                            'fill' => true, 'stroke' => true, 'stroke-width' => true,
                            'stroke-linecap' => true, 'stroke-linejoin' => true,
                            'aria-hidden' => true, 'role' => true, 'class' => true ],
            'path'     => [ 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ],
            'line'     => [ 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true,
                            'stroke' => true, 'stroke-width' => true ],
            'circle'   => [ 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true ],
            'rect'     => [ 'x' => true, 'y' => true, 'width' => true, 'height' => true,
                            'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true ],
            'polyline' => [ 'points' => true, 'fill' => true, 'stroke' => true ],
            'polygon'  => [ 'points' => true, 'fill' => true, 'stroke' => true ],
            'g'        => [ 'fill' => true, 'stroke' => true, 'transform' => true ],
        ];

        return wp_kses( $raw, $allowed );
    }

    private function arb_placeholder( string $msg ): void {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) return;
        echo '<div style="padding:20px;background:#f0f6fc;border:2px dashed #2271b1;border-radius:8px;'
           . 'text-align:center;color:#135e96;font-size:13px;margin:4px 0">'
           . '<span style="font-size:22px;display:block;margin-bottom:6px">🪗</span>'
           . '<strong>ACF Accordion</strong><br>' . esc_html( $msg ) . '</div>';
    }
}
