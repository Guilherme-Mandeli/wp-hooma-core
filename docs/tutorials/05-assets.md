# Tutorial Capítulo 05: Registrar Assets

*Disponible desde v0.3*

En este capítulo aprenderemos a registrar y cargar hojas de estilo (CSS) y scripts de Javascript (JS) en las vistas de nuestro módulo de forma segura, aprovechando el control de versiones (`filemtime`) automático de Hooma.

---

## 1. Estructura física de los archivos

1.  Crea la carpeta `assets/css/` y `assets/js/` en la raíz de tu módulo.
2.  Crea un archivo vacío `assets/css/style.css`.
3.  Crea un archivo `assets/js/app.js` con el siguiente código:
    ```javascript
    console.log('¡Hola de nuevo desde Hooma Assets!');
    ```

---

## 2. Encolar los archivos en `index.php`

En el archivo principal `index.php` de tu módulo, suscríbete al gancho `admin_enqueue_scripts` de WordPress y utiliza el servicio `Assets` de Hooma Core para registrar y encolar tus archivos:

```php
// En index.php
add_action('admin_enqueue_scripts', function($hook) {
    // Cargar assets únicamente en la pantalla de nuestro módulo
    if ($hook !== 'toplevel_page_mi-primer-modulo') {
        return;
    }

    // Registrar y encolar la hoja de estilos
    Hooma::assets()->enqueue_style('mi-modulo-css', 'assets/css/style.css');

    // Registrar y encolar el script JS
    Hooma::assets()->enqueue_script('mi-modulo-js', 'assets/js/app.js');
});
```

*Nota: Hooma Assets comprobará el tiempo de modificación del archivo físico en el servidor e inyectará automáticamente un parámetro `?ver={timestamp}` a la url. Esto evita problemas de almacenamiento en caché en el navegador del usuario.*

---

Siguiente capítulo: **[Capítulo 06: Peticiones HTTP](06-http.md)**
