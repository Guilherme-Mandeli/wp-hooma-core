<?php
/**
 * Module Name: Ejemplo Frontend Assets
 * Description: Demostración de registro de Shortcode y carga dinámica de estilos y scripts del módulo en el frontend.
 * Version: 1.0.0
 * Author: Technical Writer Hooma
 * Text Domain: hooma-assets-demo
 * 
 * Nota: Este archivo es meramente ilustrativo para servir como referencia y ejemplo.
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Registro del Shortcode de WordPress
add_action('init', function() {
    add_shortcode('hooma_demo_widget', 'hooma_assets_demo_widget_render');
});

/**
 * Renderiza el widget en el frontend y encola selectivamente los assets del módulo.
 */
function hooma_assets_demo_widget_render($atts) {
    // A) Encolar assets utilizando el servicio de Hooma
    // Assets aplicará automáticamente filemtime como parámetro de versión para romper la caché
    Hooma::assets()->enqueue_style('demo-widget-style', 'assets/css/widget.css');
    Hooma::assets()->enqueue_script('demo-widget-script', 'assets/js/widget.js', array('jquery'));

    // B) Renderizar el HTML
    ob_start();
    ?>
    <div class="hooma-widget-container">
        <h4 class="widget-title">Widget de Ejemplo de Hooma</h4>
        <p class="widget-desc">Este bloque fue generado por un Shortcode y cargó sus propios archivos CSS y JS de forma aislada.</p>
        <button id="demo-action-btn" class="button">Púlsame</button>
    </div>
    <?php
    return ob_get_clean();
}
