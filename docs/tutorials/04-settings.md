# Tutorial Capítulo 04: Guardar Configuración

*Disponible desde v0.3*

En este capítulo aprenderemos a persistir y recuperar ajustes utilizando el servicio `Settings` de Hooma Core.

---

## 1. Recuperar e Imprimir Ajustes en la Vista

Vamos a actualizar la vista `views/admin-page.php` para renderizar un campo de formulario que recupere el valor configurado utilizando el servicio `Settings`:

```php
<?php
// views/admin-page.php
$api_key = Hooma::settings()->get('mi_modulo.api_key', '');
?>
<div class="hooma-ui-wrapper">
    <?php echo Hooma_UI::header('Mi Primer Módulo', 'v1.0.0'); ?>

    <div class="hooma-ui-container">
        <h2>Ajustes</h2>
        
        <form method="POST" action="">
            <?php wp_nonce_field('mi_modulo_settings', 'mi_modulo_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="api_key">Clave del API</label></th>
                    <td>
                        <input type="text" id="api_key" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                    </td>
                </tr>
            </table>
            <?php submit_button('Guardar'); ?>
        </form>
    </div>

    <?php echo Hooma_UI::footer('Ajustes Persistidos'); ?>
</div>
```

---

## 2. Procesar el Envío en el Controlador

Actualiza el método de renderizado de tu controlador `src/Controllers/WelcomeController.php` para interceptar la petición POST y guardar la configuración:

```php
<?php

namespace HoomaModules\MiPrimerModulo\Controllers;

use Hooma;

class WelcomeController
{
    public function renderPage()
    {
        // Procesar formulario si se realiza POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['mi_modulo_nonce']) && wp_verify_nonce($_POST['mi_modulo_nonce'], 'mi_modulo_settings')) {
                $new_key = sanitize_text_field($_POST['api_key']);
                
                // Guardar valor con el servicio Settings
                Hooma::settings()->set('mi_modulo.api_key', $new_key);
                
                // Encolar aviso de éxito
                Hooma::notices()->success('Ajustes guardados con éxito.');
            }
        }

        $view_path = dirname(dirname(__DIR__)) . '/views/admin-page.php';
        if (file_exists($view_path)) {
            include $view_path;
        }
    }
}
```

---

Siguiente capítulo: **[Capítulo 05: Registrar Assets](05-assets.md)**
