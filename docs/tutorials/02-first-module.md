# Tutorial Capítulo 02: Tu Primer Módulo

*Disponible desde v0.1*

En este capítulo aprenderemos a declarar los metadatos de tu módulo, registrar su Namespace PSR-4 y lograr que se cargue de forma exitosa en el panel administrativo de Hooma Core.

---

## 1. Escribiendo los Metadatos en `index.php`

Edita el archivo `index.php` de tu módulo `mi-primer-modulo/` y añade la cabecera de comentarios:

```php
<?php
/**
 * Module Name: Mi Primer Módulo
 * Description: Módulo inicial de aprendizaje paso a paso para Hooma Core.
 * Version: 1.0.0
 * Author: Desarrollador Hooma
 */

if (!defined('ABSPATH')) {
    exit;
}

// Lógica de arranque...
```

---

## 2. Activando el Módulo
1.  Inicia sesión en tu panel de administración de WordPress.
2.  Navega al menú **Hooma Core** -> **Módulos**.
3.  Deberías ver **"Mi Primer Módulo"** listado en la pantalla. Haz clic en **Activar**.

---

## 3. Registrando tu Primera Clase (PSR-4)

1.  Crea la carpeta `src/Controllers/` dentro de tu módulo.
2.  Crea el archivo `src/Controllers/WelcomeController.php` con la siguiente clase:

```php
<?php

namespace HoomaModules\MiPrimerModulo\Controllers;

class WelcomeController
{
    public function message(): string
    {
        return '¡Hola Mundo desde el controlador de Hooma!';
    }
}
```

3.  Instancia y prueba tu clase en `index.php`:

```php
// En index.php
$controller = new \HoomaModules\MiPrimerModulo\Controllers\WelcomeController();
// El autoloader cargará automáticamente la clase al instanciarla
```

---

Siguiente capítulo: **[Capítulo 03: Crear Página Admin](03-admin-page.md)**
