<?php
/**
 * Module Name: Ejemplo Eventos Desacoplados
 * Description: Demostración de comunicación por eventos y filtros para el desacoplamiento de módulos.
 * Version: 1.0.0
 * Author: Technical Writer Hooma
 * Text Domain: hooma-events-demo
 * 
 * Nota: Este archivo es meramente ilustrativo para servir como referencia y ejemplo.
 */

if (!defined('ABSPATH')) {
    exit;
}

// ==========================================
// 1. MÓDULO EMISOR (SIMULADO)
// ==========================================

add_action('admin_menu', function() {
    add_menu_page(
        'Events Demo',
        'Events Demo',
        'manage_options',
        'hooma-events-demo',
        function() {
            if (isset($_POST['trigger_event'])) {
                $order_id = rand(1000, 9999);
                $monto_inicial = 150.00;

                // A) Pasar valor a través de filtros para permitir mutaciones
                $monto_final = Hooma::events()->filter('demo.calculate_price', $monto_inicial);

                // B) Despachar evento global
                $result = Hooma::events()->dispatch('demo.order_placed', array(
                    'order_id' => $order_id,
                    'total'    => $monto_final
                ));

                Hooma::notices()->success(sprintf(
                    "Pedido #%d procesado con un monto final de %.2f. Reaccionaron %d listeners en segundo plano.",
                    $order_id,
                    $monto_final,
                    $result->listeners_executed()
                ));
            }
            ?>
            <div class="hooma-ui-wrapper">
                <?php echo Hooma_UI::header('Decoupled Events Demo', 'v1.0.0'); ?>
                <div class="hooma-ui-container">
                    <h2>Simulador de Despacho de Pedidos</h2>
                    <form method="POST" action="">
                        <p>Al hacer clic en el botón inferior se simulará una compra, lo cual disparará un filtro mutable y un evento de acción al que se suscriben otros listeners.</p>
                        <?php submit_button('Simular Compra', 'primary', 'trigger_event'); ?>
                    </form>
                </div>
                <?php echo Hooma_UI::footer('Ejemplo de Bus de Eventos'); ?>
            </div>
            <?php
        }
    );
});


// ==========================================
// 2. MÓDULOS RECEPTORES (DESACOPLADOS)
// ==========================================

// Receptor A: Aplica descuento mediante Filtro
Hooma::events()->listen('demo.calculate_price', function($monto) {
    // Aplica un descuento fijo de 10%
    return $monto * 0.90;
}, 10, 1);

// Receptor B: Genera logs de auditoría en Acción
Hooma::events()->listen('demo.order_placed', function($order_data) {
    Hooma::logger()->info(sprintf(
        "Auditoría: Compra confirmada. Pedido #%d, Total: %.2f",
        $order_data['order_id'],
        $order_data['total']
    ));
}, 10, 1);

// Receptor C: Encola una notificación en memoria
Hooma::events()->listen('demo.order_placed', function($order_data) {
    Hooma::logger()->notice("Notificación: Pedido recibido por receptor asíncrono.");
}, 10, 1);
