<?php
/**
 * Plugin Name:  ACF Repeater for Elementor
 * Description:  Usa campos Repeater y Group de ACF Pro directamente en Elementor: modo Sub-campos, HTML con tokens y Plantilla.
 * Version:      1.3.5
 * Author:       Cronuts Digital
 * Author URI:   https://cronuts.digital
 * License:      GPL-3.0-or-later
 * Requires PHP: 7.4
 * Requires at least: 5.9
 */

defined( 'ABSPATH' ) || exit;

define( 'ARB_VERSION', '1.3.5' );
define( 'ARB_DIR',     plugin_dir_path( __FILE__ ) );
define( 'ARB_URL',     plugin_dir_url( __FILE__ ) );

// â”€â”€ Actualizaciones automÃ¡ticas desde GitHub â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
if ( file_exists( ARB_DIR . 'plugin-update-checker/plugin-update-checker/load-v5p6.php' ) ) {
    require_once ARB_DIR . 'plugin-update-checker/plugin-update-checker/load-v5p6.php';
    $arbUpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/AriCronuts/acf-repeater-blocks/',
        __FILE__,
        'acf-repeater-blocks'
    );
    if ( defined( 'ARB_GITHUB_TOKEN' ) ) {
        $arbUpdateChecker->setAuthentication( ARB_GITHUB_TOKEN );
    }
    $arbUpdateChecker->setBranch( 'main' );
}

add_action( 'plugins_loaded', function () {

    // â”€â”€ Dependencia: ACF Pro â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    if ( ! function_exists( 'acf_get_field_groups' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>'
                . '<strong>ACF Repeater for Elementor</strong> requiere Advanced Custom Fields PRO activo.'
                . '</p></div>';
        } );
        return;
    }

    // â”€â”€ Dependencia: Elementor â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // did_action('elementor/loaded') ya habrÃ¡ disparado si Elementor estÃ¡ activo,
    // porque plugins_loaded corre despuÃ©s de que cada plugin se cargue.
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>'
                . '<strong>ACF Repeater for Elementor</strong> requiere Elementor activo.'
                . '</p></div>';
        } );
        return;
    }

    // â”€â”€ InternacionalizaciÃ³n â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    load_plugin_textdomain( 'arb', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    // â”€â”€ ARB_ACF_Helpers: no extiende nada de Elementor â†’ se carga ahora â”€â”€â”€â”€â”€â”€â”€
    require_once ARB_DIR . 'includes/class-arb-acf-helpers.php';

    // â”€â”€ CategorÃ­a Elementor â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    add_action( 'elementor/elements/categories_registered', function ( $manager ) {
        $manager->add_category( 'arb', [
            'title' => 'ðŸ” ACF Repeater',
            'icon'  => 'fa fa-database',
        ] );
    } );

    // â”€â”€ Widget: se carga DENTRO del hook, cuando Elementor\Widget_Base existe â”€
    add_action( 'elementor/widgets/register', function ( $manager ) {
        require_once ARB_DIR . 'includes/class-arb-widget.php';
        $manager->register( new ARB_Widget() );
    } );

    // â”€â”€ Dynamic tags: se carga DENTRO del hook, cuando Tag base existe â”€â”€â”€â”€â”€â”€â”€â”€
    add_action( 'elementor/dynamic_tags/register', function ( $manager ) {
        require_once ARB_DIR . 'includes/dynamic-tags/class-arb-tags.php';
        ARB_Tags::register( $manager );
    } );

    // â”€â”€ CSS frontend â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    add_action( 'elementor/frontend/after_enqueue_styles', function () {
        wp_enqueue_style( 'arb-frontend', ARB_URL . 'assets/frontend.css', [], ARB_VERSION );
    } );

    // â”€â”€ JS Accordion (registro; Elementor encola vÃ­a get_script_depends()) â”€â”€â”€
    add_action( 'wp_enqueue_scripts', function () {
        wp_register_script( 'arb-accordion', ARB_URL . 'assets/accordion.js', [], ARB_VERSION, true );
    } );

} );
