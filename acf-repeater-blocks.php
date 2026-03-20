<?php
/**
 * Plugin Name:  ACF Repeater for Elementor
 * Description:  Usa campos Repeater y Group de ACF Pro directamente en Elementor: modo Sub-campos, HTML con tokens y Plantilla.
 * Version:      1.0.2
 * Author:       Cronuts Digital
 * Author URI:   https://cronuts.digital
 * License:      GPL-3.0-or-later
 * Requires PHP: 7.4
 * Requires at least: 5.9
 */

defined( 'ABSPATH' ) || exit;

define( 'ARB_VERSION', '1.0.2' );
define( 'ARB_DIR',     plugin_dir_path( __FILE__ ) );
define( 'ARB_URL',     plugin_dir_url( __FILE__ ) );

// ── Actualizaciones automáticas desde GitHub ──────────────────────────────
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

    // ── Dependencia: ACF Pro ──────────────────────────────────────────────────
    if ( ! function_exists( 'acf_get_field_groups' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>'
                . '<strong>ACF Repeater for Elementor</strong> requiere Advanced Custom Fields PRO activo.'
                . '</p></div>';
        } );
        return;
    }

    // ── Dependencia: Elementor ────────────────────────────────────────────────
    // did_action('elementor/loaded') ya habrá disparado si Elementor está activo,
    // porque plugins_loaded corre después de que cada plugin se cargue.
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>'
                . '<strong>ACF Repeater for Elementor</strong> requiere Elementor activo.'
                . '</p></div>';
        } );
        return;
    }

    // ── ARB_ACF_Helpers: no extiende nada de Elementor → se carga ahora ───────
    require_once ARB_DIR . 'includes/class-arb-acf-helpers.php';

    // ── Categoría Elementor ───────────────────────────────────────────────────
    add_action( 'elementor/elements/categories_registered', function ( $manager ) {
        $manager->add_category( 'arb', [
            'title' => '🔁 ACF Repeater',
            'icon'  => 'fa fa-database',
        ] );
    } );

    // ── Widget: se carga DENTRO del hook, cuando Elementor\Widget_Base existe ─
    add_action( 'elementor/widgets/register', function ( $manager ) {
        require_once ARB_DIR . 'includes/class-arb-widget.php';
        $manager->register( new ARB_Widget() );
    } );

    // ── Dynamic tags: se carga DENTRO del hook, cuando Tag base existe ────────
    add_action( 'elementor/dynamic_tags/register', function ( $manager ) {
        require_once ARB_DIR . 'includes/dynamic-tags/class-arb-tags.php';
        ARB_Tags::register( $manager );
    } );

    // ── CSS frontend ──────────────────────────────────────────────────────────
    add_action( 'elementor/frontend/after_enqueue_styles', function () {
        wp_enqueue_style( 'arb-frontend', ARB_URL . 'assets/frontend.css', [], ARB_VERSION );
    } );

} );
