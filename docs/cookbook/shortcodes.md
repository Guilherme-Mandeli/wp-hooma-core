# Cookbook: Registrar Shortcodes con UI Kit

*Disponible desde v0.1*

Esta receta muestra cómo registrar un shortcode de WordPress para mostrar información de tu módulo en el frontend utilizando componentes HTML de Hooma.

---

## Solución Completa

```php
// En index.php de tu módulo
add_action('init', function() {
    add_shortcode('hooma_booking_summary', function($atts) {
        $atts = shortcode_atts(array(
            'limit' => 5
        ), $atts, 'hooma_booking_summary');

        $limit = (int) $atts['limit'];

        // Obtener datos
        $db = Hooma::database();
        $prefix = $db->prefix();
        $reservas = $db->get_results(
            "SELECT * FROM {$prefix}hooma_reservas ORDER BY id DESC LIMIT %d",
            array($limit)
        );

        if (empty($reservas)) {
            return '<div class="hooma-ui-alert hooma-ui-alert-info">No hay reservas registradas.</div>';
        }

        // Usamos buffers de salida para renderizar la plantilla HTML limpiamente
        ob_start();
        ?>
        <div class="hooma-frontend-wrapper">
            <h4 class="hooma-title">Últimas Reservas</h4>
            <table class="hooma-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $reserva) : ?>
                        <tr>
                            <td>#<?php echo esc_html($reserva['id']); ?></td>
                            <td><?php echo esc_html($reserva['fecha']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo esc_attr($reserva['estado']); ?>">
                                    <?php echo esc_html(strtoupper($reserva['estado'])); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    });
});
```
