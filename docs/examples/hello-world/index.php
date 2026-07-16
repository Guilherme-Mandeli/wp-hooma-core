<?php
/**
 * Module Name: Ejemplo Hello World
 * Description: Módulo mínimo de ejemplo listo para clonar y ejecutar en Hooma Core.
 * Version: 1.0.0
 * Author: Technical Writer Hooma
 */

if (!defined('ABSPATH')) {
    exit;
}

// Escuchar inicio de administración
add_action('admin_menu', function() {
    add_menu_page(
        'Hello Hooma',
        'Hello Hooma',
        'read',
        'hello-hooma',
        function() {
            ?>
            <div class="hooma-ui-wrapper">
                <?php echo Hooma_UI::header('Hello Hooma', 'v1.0.0'); ?>
                <div class="hooma-ui-container">
                    <h2>¡Módulo Cargado con Éxito!</h2>
                    <p>Este es un módulo de ejemplo básico autocontenido.</p>
                </div>
                <?php echo Hooma_UI::footer('Hooma Core Examples'); ?>
            </div>
            <?php
        }
    );
});
