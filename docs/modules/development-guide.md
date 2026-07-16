# Guía de Desarrollo de Módulos (Module Development Guide)

*Disponible desde v0.1*

Esta guía detalla la especificación física y lógica para construir un módulo de negocio compatible con Hooma Core.

---

## 1. Directorio de Módulos

Todos los módulos residen en la carpeta de carga dinámica del sistema:
`wp-content/uploads/hooma/modules/`

Cada módulo debe vivir dentro de su propio subdirectorio, nombrado en minúsculas y separado por guiones si posee múltiples palabras (ej. `gestion-reservas` o `facturacion`).

---

## 2. Estructura de Carpetas Recomendada

Para mantener el orden interno y facilitar la mantenibilidad, te recomendamos estructurar tu módulo de la siguiente manera:

```
mi-modulo/
├── index.php (Archivo de entrada obligatorio con Metadatos)
├── assets/ (Archivos públicos)
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── src/ (Lógica interna autocargada PSR-4)
│   ├── Controllers/
│   │   └── BookingController.php
│   ├── Models/
│   │   └── Booking.php
│   └── Jobs/
│       └── SyncJob.php
└── views/ (Plantillas y maquetación HTML de vistas)
    └── admin-page.php
```

---

## 3. Metadatos de Descubrimiento (Archivo Principal)

Para que Hooma Core pueda encontrar y catalogar tu módulo en el gestor administrativo, el archivo principal del módulo (`index.php` o `{nombre-modulo}.php`) **debe comenzar** con un bloque de metadatos en formato de comentario PHP:

```php
<?php
/**
 * Module Name: Gestión de Reservas
 * Description: Administra el ciclo completo de reservas de la plataforma e integra pasarelas de pago.
 * Version: 1.0.0
 * Author: Tu Nombre o Empresa
 * Author URI: https://tuempresa.com
 * Text Domain: hooma-booking
 */
```

*Nota: Si este bloque no está presente en la cabecera del archivo de entrada, el framework ignorará por completo el directorio y el módulo no se mostrará en el listado de activación.*

---

## 4. Namespaces y Autocarga (PSR-4)

Hooma Core provee un autocargador integrado que mapea automáticamente las clases de tus módulos según el estándar PSR-4:

- El espacio de nombres raíz para todos los módulos es **`HoomaModules`**.
- La carpeta raíz del módulo equivale a su namespace en formato StudlyCase (primera letra de cada palabra en mayúscula, sin guiones).

### Ejemplo de Mapeo:

Para la clase ubicada físicamente en:
`wp-content/uploads/hooma/modules/mi-modulo/src/Controllers/BookingController.php`

El namespace y nombre de clase correspondiente debe ser:
```php
<?php

namespace HoomaModules\MiModulo\Controllers;

class BookingController
{
    public function render()
    {
        // Código...
    }
}
```

---

## 5. El Archivo de Entrada `index.php`

El archivo `index.php` actúa como el bootstrap de tu módulo. Aquí debes enganchar callbacks a los hooks de WordPress o suscribirte a eventos de Hooma. Evita escribir código procedimental largo dentro de este archivo; en su lugar, delega la lógica a controladores.

### Ejemplo de un `index.php` limpio:

```php
<?php
/**
 * Module Name: Mi Módulo
 * Description: Ejemplo de inicialización de módulo limpio.
 * Version: 1.0.0
 * Author: Hooma Core
 */

if (!defined('ABSPATH')) {
    exit;
}

// Inicializar el controlador del módulo escuchando el hook del admin de WordPress
add_action('admin_menu', function() {
    $controller = new \HoomaModules\MiModulo\Controllers\AdminController();
    
    add_menu_page(
        'Mi Módulo',
        'Mi Módulo',
        'manage_options',
        'mi-modulo',
        array($controller, 'show_dashboard')
    );
});
```

---

## 6. Integración con el UI Kit

Cuando expongas pantallas visuales en el panel de administración, debes estructurar el marcado usando las utilidades estandarizadas del UI Kit de Hooma para garantizar la coherencia visual con el resto del sistema.

### Ejemplo de maquetación de vista (`views/admin-page.php`):

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="hooma-ui-wrapper">
    <!-- Header del Módulo -->
    <?php echo Hooma_UI::header('Mi Módulo', 'v1.0.0'); ?>

    <!-- Contenedor Principal de Contenido -->
    <div class="hooma-ui-container">
        <h2>Panel de Control</h2>
        <p>Bienvenido al módulo estructurado bajo el UI Kit oficial de Hooma.</p>
        
        <form method="POST" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="api_token">API Token</label></th>
                    <td>
                        <input type="text" id="api_token" name="api_token" class="regular-text" />
                    </td>
                </tr>
            </table>
            <?php submit_button('Guardar Configuración'); ?>
        </form>
    </div>

    <!-- Pie de Página -->
    <?php echo Hooma_UI::footer('Soporte Técnico: soporte@empresa.com'); ?>
</div>
```
