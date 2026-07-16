# Tutorial Capítulo 03: Crear Página Admin

*Disponible desde v0.1*

En este capítulo aprenderemos a registrar un menú de administración en WordPress y a maquetar el panel del módulo utilizando el UI Kit oficial de Hooma.

---

## 1. El Controlador de Administración

Modifica tu controlador `src/Controllers/WelcomeController.php` para añadir un método de renderizado:

```php
<?php

namespace HoomaModules\MiPrimerModulo\Controllers;

class WelcomeController
{
    public function renderPage()
    {
        // Ruta física a la vista HTML
        $view_path = dirname(dirname(__DIR__)) . '/views/admin-page.php';
        
        if (file_exists($view_path)) {
            include $view_path;
        }
    }
}
```

---

## 2. Creando la Vista con el UI Kit

Crea la carpeta `views/` dentro de tu módulo y añade el archivo `views/admin-page.php`:

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="hooma-ui-wrapper">
    <!-- Header del UI Kit -->
    <?php echo Hooma_UI::header('Mi Primer Módulo', 'v1.0.0'); ?>

    <!-- Contenedor Principal -->
    <div class="hooma-ui-container">
        <h2>Panel de Configuración</h2>
        <p>Este es el cuerpo principal maquetado de forma responsiva bajo la consistencia del UI Kit.</p>
    </div>

    <!-- Footer del UI Kit -->
    <?php echo Hooma_UI::footer('Desarrollado sobre Hooma Core'); ?>
</div>
```

---

## 3. Registrando el Menú en `index.php`

Vincula el controlador a la acción `admin_menu` de WordPress en tu archivo `index.php`:

```php
// En index.php
add_action('admin_menu', function() {
    $controller = new \HoomaModules\MiPrimerModulo\Controllers\WelcomeController();
    
    add_menu_page(
        'Mi Primer Módulo',
        'Mi Primer Módulo',
        'manage_options',
        'mi-primer-modulo',
        array($controller, 'renderPage')
    );
});
```

---

Siguiente capítulo: **[Capítulo 04: Guardar Configuración](04-settings.md)**
