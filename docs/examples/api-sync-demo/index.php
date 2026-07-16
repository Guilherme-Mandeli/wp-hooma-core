<?php
/**
 * Module Name: Ejemplo API Sync & Cache
 * Description: Demostración de consumo de API externa con control de red, excepciones y almacenamiento en caché.
 * Version: 1.0.0
 * Author: Technical Writer Hooma
 * Text Domain: hooma-api-demo
 * 
 * Nota: Este archivo es meramente ilustrativo para servir como referencia y ejemplo.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function() {
    add_menu_page(
        'API Sync Demo',
        'API Sync Demo',
        'manage_options',
        'hooma-api-demo',
        'hooma_api_demo_render_page'
    );
});

/**
 * Recupera datos de un servicio externo con caché y fallback.
 */
function hooma_api_demo_fetch_data(): array {
    $cache_key = 'demo.cryptos_rates';

    // Guardar respuesta en caché por 10 minutos (600 segundos)
    return (array) Hooma::cache()->remember($cache_key, 600, function() {
        $url = 'https://api.coingecko.com/api/v3/simple/price';

        try {
            $response = Hooma::http()->get($url, array(
                'body' => array(
                    'ids'    => 'bitcoin,ethereum,cardano',
                    'vs_currencies' => 'usd'
                )
            ));

            if ($response->successful()) {
                return $response->json();
            }

            Hooma::logger()->error('API CoinGecko devolvió estado de error: ' . $response->status());

        } catch (\RuntimeException $e) {
            Hooma::logger()->critical('Fallo de red en API Sync: ' . $e->getMessage());
        }

        // Fallback en caso de fallo de red
        return array(
            'bitcoin'  => array('usd' => 0),
            'ethereum' => array('usd' => 0)
        );
    });
}

function hooma_api_demo_render_page() {
    $prices = hooma_api_demo_fetch_data();
    ?>
    <div class="hooma-ui-wrapper">
        <?php echo Hooma_UI::header('API Sync & Cache Demo', 'v1.0.0'); ?>

        <div class="hooma-ui-container">
            <h2>Cotizaciones de Criptomonedas (Actualizado cada 10 min)</h2>
            <p>Los datos a continuación son obtenidos vía API REST externa y se leen desde el caché del sistema para asegurar latencia cero en las recargas.</p>
            
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Moneda</th>
                        <th>Precio (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Bitcoin (BTC)</td>
                        <td><strong>$<?php echo number_format($prices['bitcoin']['usd'] ?? 0, 2); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Ethereum (ETH)</td>
                        <td><strong>$<?php echo number_format($prices['ethereum']['usd'] ?? 0, 2); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Cardano (ADA)</td>
                        <td><strong>$<?php echo number_format($prices['cardano']['usd'] ?? 0, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php echo Hooma_UI::footer('Ejemplo de API y Cacheo Integrado'); ?>
    </div>
    <?php
}
