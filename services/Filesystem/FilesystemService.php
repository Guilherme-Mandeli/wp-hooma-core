<?php

namespace Hooma\Core\Services\Filesystem;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Implementación del servicio de sistema de archivos para Hooma Core.
 *
 * Utiliza la API WP_Filesystem de WordPress de forma transparente en segundo plano.
 * Maneja automáticamente la inicialización y traduce los fallos de lectura a excepciones.
 */
class FilesystemService implements FilesystemInterface
{
    /**
     * Inicializa y retorna la instancia global del sistema de archivos de WordPress.
     *
     * @return \WP_Filesystem_Base Objeto del sistema de archivos de WordPress.
     */
    protected function get_wp_filesystem()
    {
        global $wp_filesystem;
        
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }
        
        return $wp_filesystem;
    }

    /**
     * Lee el contenido de un archivo en texto plano.
     *
     * @param string $path Ruta física del archivo.
     * @return string Contenido del archivo.
     * @throws \RuntimeException Si el archivo no existe o no se puede leer.
     */
    public function read(string $path): string
    {
        if (!$this->exists($path)) {
            throw new \RuntimeException(
                sprintf('El archivo no existe: "%s"', $path)
            );
        }

        $fs = $this->get_wp_filesystem();
        $content = $fs->get_contents($path);

        if ($content === false) {
            throw new \RuntimeException(
                sprintf('No se pudo leer el archivo en la ruta: "%s"', $path)
            );
        }

        return $content;
    }

    /**
     * Escribe contenido en un archivo. Crea carpetas contenedoras de forma automática.
     *
     * @param string $path Ruta física del archivo.
     * @param string $content Contenido a escribir.
     * @return bool True en caso de éxito, false de lo contrario.
     */
    public function write(string $path, string $content): bool
    {
        $dir = dirname($path);
        if (!$this->exists($dir)) {
            $this->mkdir($dir);
        }

        $fs = $this->get_wp_filesystem();
        return $fs->put_contents($path, $content, defined('FS_CHMOD_FILE') ? FS_CHMOD_FILE : 0644);
    }

    /**
     * Comprueba si un archivo o directorio existe en el servidor.
     *
     * @param string $path Ruta física.
     * @return bool
     */
    public function exists(string $path): bool
    {
        $fs = $this->get_wp_filesystem();
        return $fs->exists($path);
    }

    /**
     * Elimina un archivo o directorio.
     *
     * @param string $path Ruta física.
     * @param bool $recursive Eliminación recursiva si es un directorio.
     * @return bool True si se eliminó con éxito, false de lo contrario.
     */
    public function delete(string $path, bool $recursive = false): bool
    {
        $fs = $this->get_wp_filesystem();
        return $fs->delete($path, $recursive);
    }

    /**
     * Copia un archivo o directorio de origen a destino.
     *
     * @param string $src Ruta origen.
     * @param string $dest Ruta destino.
     * @return bool True si se copió con éxito, false de lo contrario.
     */
    public function copy(string $src, string $dest): bool
    {
        $fs = $this->get_wp_filesystem();
        return $fs->copy($src, $dest, true, defined('FS_CHMOD_FILE') ? FS_CHMOD_FILE : 0644);
    }

    /**
     * Crea un directorio de forma recursiva en el servidor.
     *
     * @param string $path Ruta del directorio.
     * @return bool True en caso de creación exitosa, false de lo contrario.
     */
    public function mkdir(string $path): bool
    {
        if ($this->exists($path)) {
            return true;
        }

        $fs = $this->get_wp_filesystem();
        return $fs->mkdir($path, defined('FS_CHMOD_DIR') ? FS_CHMOD_DIR : 0755);
    }
}
