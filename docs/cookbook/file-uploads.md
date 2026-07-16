# Cookbook: Subida de Archivos Segura

*Disponible desde v0.3*

Esta receta detalla el proceso completo para interceptar y guardar de forma segura archivos subidos por el usuario en el directorio `/uploads/` de WordPress utilizando el servicio `Filesystem`.

---

## Solución Completa

```php
<?php

namespace HoomaModules\MiModulo\Services;

use Hooma;

class FileUploader
{
    /**
     * Procesa y valida la subida de un archivo del formulario $_FILES.
     */
    public function procesarSubida(array $file_field): ?string
    {
        // 1. Validaciones básicas
        if (empty($file_field['tmp_name']) || $file_field['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // 2. Validar extensión y tipo MIME
        $file_info = wp_check_filetype($file_field['name']);
        $allowed_types = array('jpg', 'jpeg', 'png', 'pdf');

        if (!in_array($file_info['ext'], $allowed_types)) {
            throw new \InvalidArgumentException("Extensión de archivo no permitida.");
        }

        // 3. Definir ruta absoluta segura usando la API de WordPress y Hooma Filesystem
        $upload_dir = wp_upload_dir();
        $target_dir = wp_normalize_path($upload_dir['basedir'] . '/hooma/uploads');
        
        // Crear carpeta si no existe
        if (!Hooma::filesystem()->exists($target_dir)) {
            Hooma::filesystem()->mkdir($target_dir);
        }

        $filename = sanitize_file_name($file_field['name']);
        $target_path = $target_dir . '/' . time() . '_' . $filename;

        // 4. Copiar usando el servicio Filesystem (envolviendo mover)
        // WordPress WP_Filesystem maneja los permisos locales
        $exito = Hooma::filesystem()->copy($file_field['tmp_name'], $target_path);

        if ($exito) {
            // Eliminar el archivo temporal
            @unlink($file_field['tmp_name']);
            return $target_path;
        }

        return null;
    }
}
```
