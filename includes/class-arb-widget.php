<?php
/**
 * ARB_Widget — ACF Repeater for Elementor
 * Tres modos: Sub-campos | HTML libre | Plantilla
 */
defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class ARB_Widget extends \Elementor\Widget_Base {

    public function get_name(): string  { return 'arb-acf-repeater'; }
    public function get_title(): string { return 'ACF Repeater'; }
    public function get_icon(): string  { return 'eicon-loop-builder'; }
    public function get_categories(): array { return [ 'arb' ]; }
    public function get_keywords(): array {
        return [ 'acf', 'repeater', 'group', 'dynamic', 'loop', 'custom fields', 'accordion', 'faq' ];
    }

    public function get_script_depends(): array {
        $settings = $this->get_settings();
        return ( ( $settings['skin'] ?? 'grid' ) === 'accordion' ) ? [ 'arb-accordion' ] : [];
    }

    // =========================================================================
    // CONTROLES
    // =========================================================================

    protected function register_controls(): void {

        // ── SECCIÓN: Fuente de datos ──────────────────────────────────────────
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

        // ── SECCIÓN: Modo y contenido ─────────────────────────────────────────
        $this->start_controls_section( 'sec_mode', [
            'label'     => '⚙️ Modo y contenido',
            'tab'       => Controls_Manager::TAB_CONTENT,
            'condition' => [ 'skin!' => 'accordion' ],
        ] );

        $this->add_control( 'display_mode', [
            'label'   => 'Modo de visualización',
            'type'    => Controls_Manager::CHOOSE,
            'default' => 'subfields',
            'toggle'  => false,
            'options' => [
                'subfields' => [ 'title' => 'Sub-campos',  'icon' => 'eicon-editor-list-ul'    ],
                'html'      => [ 'title' => 'HTML libre',  'icon' => 'eicon-code'              ],
                'template'  => [ 'title' => 'Plantilla',   'icon' => 'eicon-template-library'  ],
            ],
        ] );

        // ── Sub-campos repeater (controles simples, sin Group controls) ───────
        $rep = new Repeater();

        $rep->add_control( 'sf_name', [
            'label'   => 'Sub-campo ACF',
            'type'    => Controls_Manager::SELECT,
            'options' => ARB_ACF_Helpers::get_sub_field_options(),
        ] );

        $rep->add_control( 'sf_type', [
            'label'   => 'Tipo',
            'type'    => Controls_Manager::SELECT,
            'default' => 'auto',
            'options' => [
                'auto'             => 'Auto',
                'text'             => 'Texto',
                'textarea'         => 'Textarea',
                'wysiwyg'          => 'WYSIWYG',
                'image'            => 'Imagen',
                'url'              => 'URL',
                'link'             => 'Link (campo ACF link)',
                'number'           => 'Número',
                'date_picker'      => 'Fecha',
                'select'           => 'Select / Radio',
                'true_false'       => 'True / False',
            ],
        ] );

        $rep->add_control( 'sf_tag', [
            'label'   => 'Tag HTML',
            'type'    => Controls_Manager::SELECT,
            'default' => 'p',
            'options' => [
                ''       => 'Sin tag',
                'p'      => 'p',
                'span'   => 'span',
                'div'    => 'div',
                'h2'     => 'h2',
                'h3'     => 'h3',
                'h4'     => 'h4',
                'h5'     => 'h5',
                'strong' => 'strong',
                'em'     => 'em',
            ],
        ] );

        $rep->add_control( 'sf_label', [
            'label'       => 'Prefijo visible',
            'type'        => Controls_Manager::TEXT,
            'placeholder' => 'Ej: Autor: ',
        ] );

        // Estilo básico (sin Group_Control_Typography — crashea el panel)
        $rep->add_control( 'sf_color', [
            'label'     => 'Color texto',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [
                '{{WRAPPER}} .elementor-repeater-item-{{_id}}' => 'color: {{VALUE}};',
            ],
        ] );

        $rep->add_control( 'sf_font_size', [
            'label'      => 'Tamaño de fuente',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em', 'rem' ],
            'range'      => [ 'px' => [ 'min' => 10, 'max' => 100 ] ],
            'selectors'  => [
                '{{WRAPPER}} .elementor-repeater-item-{{_id}}' => 'font-size: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $rep->add_control( 'sf_font_weight', [
            'label'     => 'Peso de fuente',
            'type'      => Controls_Manager::SELECT,
            'default'   => '',
            'options'   => [
                ''    => 'Por defecto',
                '300' => 'Light (300)',
                '400' => 'Normal (400)',
                '600' => 'Semi-bold (600)',
                '700' => 'Bold (700)',
                '800' => 'Extra-bold (800)',
            ],
            'selectors' => [
                '{{WRAPPER}} .elementor-repeater-item-{{_id}}' => 'font-weight: {{VALUE}};',
            ],
        ] );

        $rep->add_control( 'sf_margin_bottom', [
            'label'      => 'Margen inferior',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'selectors'  => [
                '{{WRAPPER}} .elementor-repeater-item-{{_id}}' => 'margin-bottom: {{SIZE}}{{UNIT}};',
            ],
        ] );

        // Imagen
        $rep->add_control( 'sf_img_height', [
            'label'      => 'Alto imagen',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'vh' ],
            'range'      => [ 'px' => [ 'min' => 20, 'max' => 600 ] ],
            'condition'  => [ 'sf_type' => 'image' ],
            'selectors'  => [
                '{{WRAPPER}} .elementor-repeater-item-{{_id}} img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover; width: 100%;',
            ],
        ] );

        $rep->add_control( 'sf_img_radius', [
            'label'      => 'Border-radius imagen',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
            'condition'  => [ 'sf_type' => 'image' ],
            'selectors'  => [
                '{{WRAPPER}} .elementor-repeater-item-{{_id}} img' => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ] );

        // Enlace
        $rep->add_control( 'sf_link_enable', [
            'label'     => 'Envolver con enlace',
            'type'      => Controls_Manager::SWITCHER,
            'separator' => 'before',
        ] );

        $rep->add_control( 'sf_link_field', [
            'label'     => 'Sub-campo URL',
            'type'      => Controls_Manager::SELECT,
            'options'   => ARB_ACF_Helpers::get_sub_field_options(),
            'condition' => [ 'sf_link_enable!' => '' ],
        ] );

        $rep->add_control( 'sf_link_target', [
            'label'     => 'Nueva ventana',
            'type'      => Controls_Manager::SWITCHER,
            'condition' => [ 'sf_link_enable!' => '' ],
        ] );

        $this->add_control( 'subfields', [
            'label'       => 'Sub-campos a mostrar',
            'type'        => Controls_Manager::REPEATER,
            'fields'      => $rep->get_controls(),
            'default'     => [],
            'title_field' => "{{{ sf_name || 'Item' }}}",
            'condition'   => [ 'display_mode' => 'subfields' ],
        ] );

        // ── HTML libre ────────────────────────────────────────────────────────
        $this->add_control( 'html_info', [
            'type'      => Controls_Manager::RAW_HTML,
            'raw'       => '<small style="color:#9da5ae;line-height:1.8;display:block">
                Tokens disponibles:<br>
                <code style="background:#1a1f25;padding:1px 5px;border-radius:3px">&#123;&#123;campo&#125;&#125;</code> valor del sub-campo<br>
                <code style="background:#1a1f25;padding:1px 5px;border-radius:3px">&#123;&#123;campo:url&#125;&#125;</code> escape como URL<br>
                <code style="background:#1a1f25;padding:1px 5px;border-radius:3px">&#123;&#123;campo:kses&#125;&#125;</code> HTML permitido<br>
                <code style="background:#1a1f25;padding:1px 5px;border-radius:3px">&#123;&#123;_index&#125;&#125;</code> índice de fila (0, 1, 2…)
            </small>',
            'condition' => [ 'display_mode' => 'html' ],
        ] );

        $this->add_control( 'html_template', [
            'label'     => 'Template HTML por fila',
            'type'      => Controls_Manager::CODE,
            'language'  => 'html',
            'rows'      => 8,
            'default'   => '',
            'condition' => [ 'display_mode' => 'html' ],
        ] );

        // ── Plantilla ─────────────────────────────────────────────────────────
        $this->add_control( 'template_info', [
            'type'      => Controls_Manager::RAW_HTML,
            'raw'       => '<small style="color:#9da5ae;line-height:1.8;display:block">
                <strong style="color:#cdd0d4">Cómo diseñar la plantilla:</strong><br>
                1. Ve a <em>Elementor › Mis Plantillas › Nueva</em><br>
                2. Diseña el ítem (título, imagen, botón…)<br>
                3. En cada widget usa el &#9889; y elige <em>ACF Repeater › campo</em><br>
                4. Guarda y selecciónala aquí abajo
            </small>',
            'condition' => [ 'display_mode' => 'template' ],
        ] );

        $this->add_control( 'template_id', [
            'label'     => 'Plantilla',
            'type'      => Controls_Manager::SELECT,
            'options'   => ARB_ACF_Helpers::get_template_options(),
            'condition' => [ 'display_mode' => 'template' ],
        ] );

        // ── Sin resultados ────────────────────────────────────────────────────
        $this->add_control( 'no_results', [
            'label'     => 'Texto si no hay resultados',
            'type'      => Controls_Manager::TEXT,
            'separator' => 'before',
        ] );

        $this->end_controls_section();

        // ── SECCIÓN: Layout ───────────────────────────────────────────────────
        $this->start_controls_section( 'sec_layout', [
            'label' => '🎨 Layout',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'skin', [
            'label'   => 'Tipo de layout',
            'type'    => Controls_Manager::SELECT,
            'default' => 'grid',
            'options' => [
                'grid'      => 'Grid',
                'list'      => 'Lista',
                'table'     => 'Tabla',
                'text'      => 'Texto inline',
                'accordion' => '🪗 Acordeón',
            ],
        ] );

        $this->add_responsive_control( 'grid_cols', [
            'label'          => 'Columnas',
            'type'           => Controls_Manager::SELECT,
            'default'        => '3',
            'tablet_default' => '2',
            'mobile_default' => '1',
            'options'        => [ '1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6' ],
            'condition'      => [ 'skin' => 'grid' ],
            'selectors'      => [ '{{WRAPPER}} .arb-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);' ],
        ] );

        $this->add_responsive_control( 'grid_gap', [
            'label'      => 'Espacio entre ítems',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 24 ],
            'condition'  => [ 'skin' => [ 'grid', 'list' ] ],
            'selectors'  => [
                '{{WRAPPER}} .arb-grid' => 'gap: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .arb-list' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'text_separator', [
            'label'     => 'Separador',
            'type'      => Controls_Manager::TEXT,
            'default'   => ', ',
            'condition' => [ 'skin' => 'text' ],
        ] );

        $this->end_controls_section();

        // ── SECCIÓN: Acordeón — Configuración ────────────────────────────────
        $this->start_controls_section( 'sec_accordion', [
            'label'     => '🪗 Acordeón',
            'tab'       => Controls_Manager::TAB_CONTENT,
            'condition' => [ 'skin' => 'accordion' ],
        ] );

        $this->add_control( 'acc_question_field', [
            'label'   => 'Sub-campo Pregunta',
            'type'    => Controls_Manager::SELECT,
            'options' => ARB_ACF_Helpers::get_sub_field_options(),
        ] );

        $this->add_control( 'acc_answer_field', [
            'label'   => 'Sub-campo Respuesta',
            'type'    => Controls_Manager::SELECT,
            'options' => ARB_ACF_Helpers::get_sub_field_options(),
        ] );

        $this->add_control( 'acc_image_field', [
            'label'       => 'Sub-campo Imagen (opcional)',
            'type'        => Controls_Manager::SELECT,
            'options'     => ARB_ACF_Helpers::get_sub_field_options(),
            'description' => 'Déjalo vacío si no quieres imagen.',
        ] );

        $this->add_control( 'acc_columns', [
            'label'     => 'Columnas',
            'type'      => Controls_Manager::SELECT,
            'default'   => '1',
            'options'   => [ '1' => '1 columna', '2' => '2 columnas' ],
            'separator' => 'before',
        ] );

        $this->add_control( 'acc_column_gap', [
            'label'      => 'Espacio entre columnas',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 24 ],
            'condition'  => [ 'acc_columns' => '2' ],
            'selectors'  => [ '{{WRAPPER}} .arb-accordion' => 'column-gap: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'acc_row_gap', [
            'label'      => 'Espacio entre filas',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 12 ],
            'selectors'  => [ '{{WRAPPER}} .arb-accordion' => 'row-gap: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'acc_close_others', [
            'label'        => 'Cerrar otros al abrir uno',
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => 'Sí',
            'label_off'    => 'No',
            'return_value' => '1',
            'default'      => '',
            'separator'    => 'before',
        ] );

        $this->add_control( 'acc_header_title_align', [
            'label'     => 'Alineación del título',
            'type'      => Controls_Manager::CHOOSE,
            'default'   => 'left',
            'toggle'    => false,
            'separator' => 'before',
            'options'   => [
                'left'   => [ 'title' => 'Izquierda',     'icon' => 'eicon-text-align-left'    ],
                'center' => [ 'title' => 'Centro',         'icon' => 'eicon-text-align-center'  ],
                'right'  => [ 'title' => 'Derecha',        'icon' => 'eicon-text-align-right'   ],
                'full'   => [ 'title' => 'Ancho completo', 'icon' => 'eicon-text-align-justify' ],
            ],
            'description' => 'Con "Ancho completo" el título ocupa todo y los iconos quedan fijos en el borde.',
        ] );

        $this->add_control( 'acc_faq_schema', [
            'label'        => 'Preguntas frecuentes (FAQ Schema)',
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => 'Sí',
            'label_off'    => 'No',
            'return_value' => '1',
            'default'      => '',
            'separator'    => 'before',
            'description'  => 'Añade JSON-LD FAQPage para resultados enriquecidos en Google.',
        ] );

        $this->add_control( 'acc_no_results', [
            'label'     => 'Texto si no hay resultados',
            'type'      => Controls_Manager::TEXT,
            'separator' => 'before',
        ] );

        $this->end_controls_section();

        // ── SECCIÓN: Acordeón — Iconos ────────────────────────────────────────
        $this->start_controls_section( 'sec_acc_icons', [
            'label'     => '🔣 Iconos del acordeón',
            'tab'       => Controls_Manager::TAB_CONTENT,
            'condition' => [ 'skin' => 'accordion' ],
        ] );

        $this->add_control( 'acc_icon_open_svg', [
            'label'    => 'SVG estado cerrado (icono "abrir")',
            'type'     => Controls_Manager::CODE,
            'language' => 'html',
            'rows'     => 4,
            'default'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
        ] );

        $this->add_control( 'acc_icon_close_svg', [
            'label'    => 'SVG estado abierto (icono "cerrar")',
            'type'     => Controls_Manager::CODE,
            'language' => 'html',
            'rows'     => 4,
            'default'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>',
        ] );

        $this->add_control( 'acc_icon_position', [
            'label'   => 'Posición del icono',
            'type'    => Controls_Manager::SELECT,
            'default' => 'right',
            'options' => [
                'right' => 'Derecha',
                'left'  => 'Izquierda',
            ],
        ] );

        $this->end_controls_section();

        // ── SECCIÓN: Filtros y orden ──────────────────────────────────────────
        $this->start_controls_section( 'sec_query', [
            'label' => '🔎 Filtros y orden',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'order_by', [
            'label'   => 'Ordenar por sub-campo',
            'type'    => Controls_Manager::SELECT,
            'options' => ARB_ACF_Helpers::get_sub_field_options(),
        ] );

        $this->add_control( 'order', [
            'label'     => 'Dirección',
            'type'      => Controls_Manager::SELECT,
            'default'   => 'ASC',
            'options'   => [ 'ASC' => 'Ascendente', 'DESC' => 'Descendente' ],
            'condition' => [ 'order_by!' => '' ],
        ] );

        $this->add_control( 'limit', [
            'label'       => 'Límite de filas',
            'type'        => Controls_Manager::NUMBER,
            'default'     => 0,
            'min'         => 0,
            'description' => '0 = todas',
        ] );

        $this->add_control( 'offset', [
            'label'   => 'Saltar primeras N filas',
            'type'    => Controls_Manager::NUMBER,
            'default' => 0,
            'min'     => 0,
        ] );

        $this->end_controls_section();

        // ── STYLE: Acordeón — Ítem ───────────────────────────────────────────
        $this->start_controls_section( 'sec_acc_style_item', [
            'label'     => 'Acordeón · Ítem',
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'skin' => 'accordion' ],
        ] );

        $this->add_control( 'acc_item_bg', [
            'label'     => 'Fondo',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-item' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'acc_item_bg_active', [
            'label'     => 'Fondo cuando está abierto',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-item.is-open' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'acc_item_border_color', [
            'label'     => 'Color de borde',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-item' => 'border-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'acc_item_border_width', [
            'label'      => 'Ancho de borde',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 10 ] ],
            'selectors'  => [ '{{WRAPPER}} .arb-acc-item' => 'border-width: {{SIZE}}px; border-style: solid;' ],
        ] );

        $this->add_control( 'acc_item_border_radius', [
            'label'      => 'Border radius',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'selectors'  => [ '{{WRAPPER}} .arb-acc-item' => 'border-radius: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();

        // ── STYLE: Acordeón — Cabecera ────────────────────────────────────────
        $this->start_controls_section( 'sec_acc_style_header', [
            'label'     => 'Acordeón · Cabecera',
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'skin' => 'accordion' ],
        ] );

        $this->add_responsive_control( 'acc_header_padding', [
            'label'      => 'Padding',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors'  => [ '{{WRAPPER}} .arb-acc-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
        ] );

        $this->add_control( 'acc_question_color', [
            'label'     => 'Color pregunta',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-question' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'acc_question_color_active', [
            'label'     => 'Color pregunta (abierto)',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-item.is-open .arb-acc-question' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'acc_question_typography',
            'selector' => '{{WRAPPER}} .arb-acc-question',
        ] );

        $this->end_controls_section();

        // ── STYLE: Acordeón — Contenido ───────────────────────────────────────
        $this->start_controls_section( 'sec_acc_style_content', [
            'label'     => 'Acordeón · Contenido',
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'skin' => 'accordion' ],
        ] );

        $this->add_responsive_control( 'acc_content_padding', [
            'label'      => 'Padding',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors'  => [ '{{WRAPPER}} .arb-acc-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
        ] );

        $this->add_control( 'acc_content_bg', [
            'label'     => 'Fondo del cuerpo',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-body' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'acc_content_color', [
            'label'     => 'Color de texto',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-acc-content' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'acc_content_typography',
            'selector' => '{{WRAPPER}} .arb-acc-content',
        ] );

        $this->add_control( 'acc_image_max_width', [
            'label'      => 'Ancho máximo imagen',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ '%', 'px' ],
            'range'      => [ '%' => [ 'min' => 10, 'max' => 100 ], 'px' => [ 'min' => 50, 'max' => 800 ] ],
            'default'    => [ 'unit' => '%', 'size' => 100 ],
            'selectors'  => [ '{{WRAPPER}} .arb-acc-content img' => 'max-width: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();

        // ── STYLE: Acordeón — Icono ───────────────────────────────────────────
        $this->start_controls_section( 'sec_acc_style_icon', [
            'label'     => 'Acordeón · Icono',
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'skin' => 'accordion' ],
        ] );

        $this->add_control( 'acc_icon_size', [
            'label'      => 'Tamaño',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 10, 'max' => 80 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 20 ],
            'selectors'  => [ '{{WRAPPER}} .arb-acc-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'acc_icon_color', [
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

        // ── SECCIÓN: Estilo ítem ──────────────────────────────────────────────
        $this->start_controls_section( 'sec_style_item', [
            'label'     => 'Ítem contenedor',
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'skin' => [ 'grid', 'list', 'table' ] ],
        ] );

        $this->add_responsive_control( 'item_padding', [
            'label'      => 'Padding',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors'  => [ '{{WRAPPER}} .arb-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
        ] );

        $this->add_control( 'item_bg', [
            'label'     => 'Fondo',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-item' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'item_border_color', [
            'label'     => 'Color borde',
            'type'      => Controls_Manager::COLOR,
            'global'    => [ 'active' => true ],
            'selectors' => [ '{{WRAPPER}} .arb-item' => 'border-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'item_border_width', [
            'label'      => 'Ancho borde',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 10 ] ],
            'selectors'  => [ '{{WRAPPER}} .arb-item' => 'border-width: {{SIZE}}px; border-style: solid;' ],
        ] );

        $this->add_control( 'item_border_radius', [
            'label'      => 'Border radius',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'selectors'  => [ '{{WRAPPER}} .arb-item' => 'border-radius: {{SIZE}}{{UNIT}};' ],
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
            $this->placeholder( 'Selecciona un campo Repeater en el panel.' );
            return;
        }

        $post_id = ARB_ACF_Helpers::resolve_post_id( $s['acf_from'] ?? 'current_post', $s['acf_custom_post_id'] ?? null );
        $rows    = get_field( $field, $post_id );

        $skin = $s['skin'] ?? 'grid';

        if ( empty( $rows ) || ! is_array( $rows ) ) {
            $no_results_text = ( $skin === 'accordion' )
                ? ( $s['acc_no_results'] ?? '' )
                : ( $s['no_results']     ?? '' );
            if ( ! empty( $no_results_text ) ) {
                echo '<p class="arb-no-results">' . esc_html( $no_results_text ) . '</p>';
            } elseif ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                $this->placeholder( 'El campo "' . esc_html( $field ) . '" no tiene filas en este post.' );
            }
            return;
        }

        // Ordenar
        $order_by = ARB_ACF_Helpers::sanitize_field_name( $s['order_by'] ?? '' );
        if ( $order_by ) {
            $dir = ( $s['order'] ?? 'ASC' ) === 'DESC' ? -1 : 1;
            usort( $rows, function ( $a, $b ) use ( $order_by, $dir ) {
                $va = $a[ $order_by ] ?? '';
                $vb = $b[ $order_by ] ?? '';
                return ( is_numeric($va) && is_numeric($vb) ? ($va<=>$vb) : strcmp((string)$va,(string)$vb) ) * $dir;
            });
        }

        $offset = max( 0, (int)( $s['offset'] ?? 0 ) );
        $limit  = max( 0, (int)( $s['limit']  ?? 0 ) );
        if ( $offset ) $rows = array_slice( $rows, $offset );
        if ( $limit  ) $rows = array_slice( $rows, 0, $limit );

        $rows  = array_values( $rows );
        $total = count( $rows );
        if ( ! $total ) return;

        // Modo acordeón: render independiente
        if ( $skin === 'accordion' ) {
            $this->render_accordion( $s, $rows );
            return;
        }

        $mode = $s['display_mode'] ?? 'subfields';

        $this->open_skin( $skin );

        foreach ( $rows as $idx => $row ) {
            $this->open_item( $skin, $idx, $total );

            switch ( $mode ) {
                case 'subfields': $this->render_subfields( $s, $row );          break;
                case 'html':      $this->render_html( $s, $row, $idx, $total ); break;
                case 'template':  $this->render_template( $s, $row );           break;
            }

            $this->close_item( $skin, $idx, $total, $s );
        }

        $this->close_skin( $skin );
    }

    // ── Modo sub-campos ───────────────────────────────────────────────────────

    private function render_subfields( array $s, array $row ): void {
        $subfields = $s['subfields'] ?? [];
        if ( empty( $subfields ) ) {
            $this->placeholder( 'Añade al menos un sub-campo en el panel.' );
            return;
        }

        foreach ( $subfields as $sf ) {
            $name = ARB_ACF_Helpers::sanitize_field_name( $sf['sf_name'] ?? '' );
            if ( ! $name || ! array_key_exists( $name, $row ) ) continue;

            $type    = $sf['sf_type']   ?? 'auto';
            $tag     = ARB_ACF_Helpers::safe_tag( $sf['sf_tag'] ?? 'p' );
            $id      = $sf['_id']       ?? '';
            $label   = isset( $sf['sf_label'] ) ? esc_html( $sf['sf_label'] ) : '';
            $val_str = $this->field_to_html( $row, $name, $type );

            if ( $val_str === '' ) continue;

            // Enlace
            if ( ! empty( $sf['sf_link_enable'] ) && ! empty( $sf['sf_link_field'] ) ) {
                $lf   = ARB_ACF_Helpers::sanitize_field_name( $sf['sf_link_field'] );
                $href = '';
                if ( isset( $row[ $lf ] ) ) {
                    $lv   = $row[ $lf ];
                    $href = is_array( $lv ) ? ( $lv['url'] ?? '' ) : (string) $lv;
                }
                if ( $href ) {
                    $tgt  = ! empty( $sf['sf_link_target'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
                    $val_str = '<a href="' . esc_url( $href ) . '"' . $tgt . '>' . $val_str . '</a>';
                }
            }

            // Wrap con tag + clase elementor-repeater-item-{_id} para que los
            // selectores {{CURRENT_ITEM}} del panel de estilo funcionen
            if ( $tag ) {
                echo '<' . esc_attr( $tag ) . ' class="arb-sf elementor-repeater-item-' . esc_attr( $id ) . '">'
                    . $label . $val_str
                    . '</' . esc_attr( $tag ) . '>';
            } else {
                echo $label . $val_str; // phpcs:ignore
            }
        }
    }

    // ── Modo HTML ─────────────────────────────────────────────────────────────

    private function render_html( array $s, array $row, int $idx, int $total ): void {
        $tpl = $s['html_template'] ?? '';
        if ( ! $tpl ) return;

        $tpl = preg_replace( '/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $tpl );
        $tpl = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $tpl );
        // Neutraliza javascript: y data: en atributos href, src, action, formaction, xlink:href
        $tpl = preg_replace( '/(href|src|action|formaction|xlink:href)\s*=\s*(["\'])\s*(?:javascript|data)\s*:/i', '$1=$2#', $tpl );
        // Neutraliza javascript: y data: en url() de atributos style
        $tpl = preg_replace( '/url\s*\(\s*["\']?\s*(?:javascript|data)\s*:/i', 'url(#', $tpl );

        $row['_index'] = $idx;
        $row['_count'] = $total;

        echo preg_replace_callback( '/\{\{([a-z0-9_]+)(?::([a-z]+))?\}\}/i', function ( $m ) use ( $row ) {
            $val    = $row[ $m[1] ] ?? '';
            $escape = $m[2] ?? 'html';
            if ( is_array( $val ) ) $val = $val['url'] ?? implode( ', ', array_map( 'strval', $val ) );
            switch ( $escape ) {
                case 'url':  return esc_url( (string) $val );
                case 'attr': return esc_attr( (string) $val );
                case 'kses': return wp_kses_post( (string) $val );
                case 'raw':  return current_user_can('edit_posts') ? (string)$val : esc_html((string)$val);
                default:     return esc_html( (string) $val );
            }
        }, $tpl ); // phpcs:ignore
    }

    // ── Modo plantilla ────────────────────────────────────────────────────────

    private function render_template( array $s, array $row ): void {
        $id = (int)( $s['template_id'] ?? 0 );
        if ( ! $id ) { $this->placeholder( 'Selecciona una plantilla.' ); return; }
        ARB_Loop_Context::push( $row );
        echo \Elementor\Plugin::$instance->frontend->get_builder_content( $id, true ); // phpcs:ignore
        ARB_Loop_Context::pop();
    }

    // ── Helpers skin ─────────────────────────────────────────────────────────

    private function open_skin( string $skin ): void {
        switch ( $skin ) {
            case 'grid':  echo '<div class="arb-grid">'; break;
            case 'list':  echo '<ul class="arb-list">';  break;
            case 'table': echo '<table class="arb-table"><tbody>'; break;
        }
    }

    private function close_skin( string $skin ): void {
        switch ( $skin ) {
            case 'grid':  echo '</div>'; break;
            case 'list':  echo '</ul>';  break;
            case 'table': echo '</tbody></table>'; break;
        }
    }

    private function open_item( string $skin, int $idx, int $total = 0 ): void {
        $c = 'arb-item arb-item-' . $idx;
        if ( $idx === 0 )                       $c .= ' arb-item-first';
        if ( $total > 0 && $idx === $total - 1 ) $c .= ' arb-item-last';
        switch ( $skin ) {
            case 'grid':  echo '<div class="' . esc_attr($c) . '">'; break;
            case 'list':  echo '<li class="'  . esc_attr($c) . '">'; break;
            case 'table': echo '<tr class="'  . esc_attr($c) . '">'; break;
        }
    }

    private function close_item( string $skin, int $idx, int $total, array $s ): void {
        switch ( $skin ) {
            case 'grid':  echo '</div>'; break;
            case 'list':  echo '</li>';  break;
            case 'table': echo '</tr>';  break;
            case 'text':
                if ( $idx < $total - 1 ) echo wp_kses_post( $s['text_separator'] ?? ', ' );
                break;
        }
    }

    // ── Convertir valor de fila a HTML ────────────────────────────────────────

    private function field_to_html( array $row, string $name, string $type ): string {
        $val = $row[ $name ] ?? null;
        if ( $val === null || $val === '' ) return '';

        if ( $type === 'auto' ) $type = ARB_ACF_Helpers::get_sub_field_type( $name );

        switch ( $type ) {
            case 'image':
                if ( is_array( $val ) ) {
                    $src = $val['sizes']['large'] ?? $val['url'] ?? '';
                    $alt = esc_attr( $val['alt'] ?? '' );
                } elseif ( is_numeric( $val ) ) {
                    $img = wp_get_attachment_image_src( (int)$val, 'large' );
                    $src = $img ? $img[0] : '';
                    $alt = esc_attr( get_post_meta( (int)$val, '_wp_attachment_image_alt', true ) );
                } else {
                    $src = esc_url( (string)$val ); $alt = '';
                }
                return '<img src="' . esc_url($src) . '" alt="' . $alt . '" loading="lazy">';

            case 'wysiwyg':
                return wp_kses_post( wpautop( (string)$val ) );

            case 'url': case 'page_link':
                return esc_url( (string)$val );

            case 'link':
                return esc_url( is_array($val) ? ($val['url']??'') : (string)$val );

            case 'true_false':
                return $val ? esc_html__( 'Sí', 'arb' ) : esc_html__( 'No', 'arb' );

            default:
                if ( is_array($val) ) return esc_html( implode(', ', array_map('strval',$val)) );
                return esc_html( (string)$val );
        }
    }

    // ── Render acordeón ───────────────────────────────────────────────────────

    private function render_accordion( array $s, array $rows ): void {
        $q_field      = ARB_ACF_Helpers::sanitize_field_name( $s['acc_question_field'] ?? '' );
        $a_field      = ARB_ACF_Helpers::sanitize_field_name( $s['acc_answer_field']   ?? '' );
        $img_field    = ARB_ACF_Helpers::sanitize_field_name( $s['acc_image_field']    ?? '' );
        $columns      = in_array( $s['acc_columns'] ?? '1', [ '1', '2' ], true ) ? $s['acc_columns'] : '1';
        $close_others = ! empty( $s['acc_close_others'] ) ? '1' : '0';
        $icon_open    = $this->sanitize_svg( $s['acc_icon_open_svg']  ?? '' );
        $icon_close   = $this->sanitize_svg( $s['acc_icon_close_svg'] ?? '' );
        $icon_pos     = ( ( $s['acc_icon_position'] ?? 'right' ) === 'left' ) ? ' arb-icon-left' : '';
        $title_align  = in_array( $s['acc_header_title_align'] ?? 'left', [ 'left', 'center', 'right', 'full' ], true )
                        ? $s['acc_header_title_align'] : 'left';

        $acc_class = 'arb-accordion' . ( $columns === '2' ? ' arb-accordion-cols-2' : '' ) . $icon_pos;

        echo '<div class="' . esc_attr( $acc_class ) . '" data-close-others="' . esc_attr( $close_others ) . '">';

        foreach ( $rows as $idx => $row ) {
            $body_id  = 'arb-acc-body-' . esc_attr( $this->get_id() ) . '-' . $idx;
            $question = '';
            $answer   = '';
            $img_html = '';

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
                $img_html = $this->acc_render_image( $row[ $img_field ] );
            }

            $header_class = 'arb-acc-header arb-acc-header--' . $title_align;

            echo '<div class="arb-acc-item">';

            echo '<button class="' . esc_attr( $header_class ) . '" '
                . 'aria-expanded="false" '
                . 'aria-controls="' . esc_attr( $body_id ) . '">';
            echo '<span class="arb-acc-question">' . $question . '</span>';
            echo '<span class="arb-acc-icon arb-acc-icon--open">'  . $icon_open  . '</span>';
            echo '<span class="arb-acc-icon arb-acc-icon--close">' . $icon_close . '</span>';
            echo '</button>';

            echo '<div class="arb-acc-body" id="' . esc_attr( $body_id ) . '" hidden>';
            echo '<div class="arb-acc-content">' . $answer . $img_html . '</div>';
            echo '</div>';

            echo '</div>';
        }

        echo '</div>';

        if ( ! empty( $s['acc_faq_schema'] ) ) {
            $this->acc_render_faq_schema( $rows, $q_field, $a_field );
        }
    }

    private function acc_render_faq_schema( array $rows, string $q_field, string $a_field ): void {
        $entities = [];
        foreach ( $rows as $row ) {
            $question = '';
            $answer   = '';

            if ( $q_field && array_key_exists( $q_field, $row ) ) {
                $v        = $row[ $q_field ];
                $question = wp_strip_all_tags( is_array( $v ) ? implode( ', ', array_map( 'strval', $v ) ) : (string) $v );
            }

            if ( $a_field && array_key_exists( $a_field, $row ) ) {
                $v      = $row[ $a_field ];
                $answer = wp_strip_all_tags( is_array( $v ) ? implode( ' ', array_map( 'strval', $v ) ) : (string) $v );
            }

            if ( ! $question || ! $answer ) continue;

            $entities[] = [
                '@type'          => 'Question',
                'name'           => $question,
                'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $answer ],
            ];
        }

        if ( empty( $entities ) ) return;

        echo '<script type="application/ld+json">'
            . wp_json_encode(
                [ '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $entities ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            )
            . '</script>';
    }

    private function acc_render_image( $val ): string {
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

    // ── Editor placeholder ────────────────────────────────────────────────────

    private function placeholder( string $msg ): void {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) return;
        echo '<div style="padding:20px;background:#f0f6fc;border:2px dashed #2271b1;border-radius:8px;'
           . 'text-align:center;color:#135e96;font-size:13px;margin:4px 0">'
           . '<span style="font-size:22px;display:block;margin-bottom:6px">🔁</span>'
           . '<strong>ACF Repeater</strong><br>' . esc_html($msg) . '</div>';
    }
}

// ── Loop context para modo Plantilla + dynamic tags ───────────────────────────

class ARB_Loop_Context {
    private static array $stack = [];
    public static function push( array $row ): void { self::$stack[] = $row; }
    public static function pop(): void  { array_pop( self::$stack ); }
    public static function get( string $key, $fallback = '' ) {
        $row = end( self::$stack );
        if ( ! $row ) return $fallback;
        $v = $row[ $key ] ?? $fallback;
        return ( $v !== '' && $v !== null ) ? $v : $fallback;
    }
    public static function is_active(): bool { return ! empty( self::$stack ); }
}
