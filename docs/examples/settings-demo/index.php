<?php
/**
 * Module Name: Ejemplo Settings y Logs
 * Description: Módulo demostrativo para el uso de configuraciones persistentes y logs diarios.
 * Version: 1.0.0
 * Author: Technical Writer Hooma
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function() {
    add_menu_page(
        'Settings Demo',
        'Settings Demo',
        'manage_options',
        'hooma-settings-demo',
        function() {
            // Guardar configuración al procesar POST
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $token = sanitize_text_field($_POST['api_token'] ?? '');
                
                // Usar servicio Settings
                Hooma::settings()->set('demo.api_token', $token);
                
                // Usar servicio Logger
                Hooma::logger()->info('Token de API Demo actualizado por usuario.');
                
                // Mostrar alerta
                Hooma::notices()->success('Token de prueba actualizado correctamente.');
            }

            $current_token = Hooma::settings()->get('demo.api_token', '');
            ?>
            <div class="hooma-ui-wrapper">
                <?php echo Hooma_UI::header('Settings & Logs Demo', 'v1.0.0'); ?>
                <div class="hooma-ui-container">
                    <form method="POST" action="">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="api_token">Token API</label></th>
                                <td>
                                    <input type="text" id="api_token" name="api_token" value="<?php echo esc_attr($current_token); ?>" class="regular-text" />
                                </td>
                            </tr>
                        </table>
                        <?php submit_button('Guardar Token'); ?>
                    </form>
                </div>
                <?php echo Hooma_UI::footer('Hooma Core Examples'); ?>
            </div>
            <?php
        }
    );
});
