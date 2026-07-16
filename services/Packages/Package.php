<?php

namespace Hooma\Core\Services\Packages;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Entidad inmutable que representa un Package registrado en Hooma Core.
 */
class Package
{
    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var PackageManifest
     */
    protected $manifest;

    /**
     * Constructor del Package.
     *
     * @param string          $path     Ruta absoluta de la carpeta del paquete.
     * @param string          $url      URL pública base de la carpeta del paquete.
     * @param PackageManifest $manifest Objeto manifiesto inmutable.
     */
    public function __construct(string $path, string $url, PackageManifest $manifest)
    {
        $this->uuid = $this->generate_uuid();
        $this->path = wp_normalize_path($path);
        $this->url = esc_url(trailingslashit($url));
        $this->manifest = $manifest;
    }

    /**
     * Obtiene el identificador único del paquete.
     *
     * @return string
     */
    public function get_uuid(): string
    {
        return $this->uuid;
    }

    /**
     * Obtiene el nombre del paquete desde su manifiesto.
     *
     * @return string
     */
    public function get_name(): string
    {
        return $this->manifest->get_name();
    }

    /**
     * Obtiene la versión del paquete desde su manifiesto.
     *
     * @return string
     */
    public function get_version(): string
    {
        return $this->manifest->get_version();
    }

    /**
     * Obtiene el tipo del paquete desde su manifiesto.
     *
     * @return PackageType
     */
    public function get_type(): PackageType
    {
        return $this->manifest->get_type();
    }

    /**
     * Obtiene la descripción del paquete desde su manifiesto.
     *
     * @return string
     */
    public function get_description(): string
    {
        return $this->manifest->get_description();
    }

    /**
     * Obtiene la ruta física absoluta de la carpeta del paquete.
     *
     * @return string
     */
    public function get_path(): string
    {
        return $this->path;
    }

    /**
     * Obtiene la URL pública base de la carpeta del paquete.
     *
     * @return string
     */
    public function get_url(): string
    {
        return $this->url;
    }

    /**
     * Obtiene la instancia del manifiesto inmutable del paquete.
     *
     * @return PackageManifest
     */
    public function get_manifest(): PackageManifest
    {
        return $this->manifest;
    }

    /**
     * Resuelve la ruta física absoluta de una entrada específica.
     *
     * @param string $entry_key Clave de la entrada (ej. 'production', 'development', 'esm').
     * @return string Ruta física absoluta al archivo de entrada, o string vacío si no existe.
     */
    public function get_entry_path(string $entry_key = 'production'): string
    {
        $entries = $this->manifest->get_entries();
        if (!isset($entries[$entry_key])) {
            return '';
        }
        return wp_normalize_path($this->path . '/' . ltrim($entries[$entry_key], '/'));
    }

    /**
     * Resuelve la URL pública absoluta de una entrada específica.
     *
     * @param string $entry_key Clave de la entrada (ej. 'production', 'development', 'esm').
     * @return string URL pública absoluta al archivo de entrada, o string vacío si no existe.
     */
    public function get_entry_url(string $entry_key = 'production'): string
    {
        $entries = $this->manifest->get_entries();
        if (!isset($entries[$entry_key])) {
            return '';
        }
        return esc_url($this->url . ltrim($entries[$entry_key], '/'));
    }

    /**
     * Genera un identificador único (UUID v4 aproximado/único).
     *
     * @return string
     */
    protected function generate_uuid(): string
    {
        if (function_exists('random_bytes')) {
            try {
                $data = random_bytes(16);
                $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // versión 4
                $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variante RFC 4122
                return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
            } catch (\Exception $e) {
                // Fallback
            }
        }
        return uniqid('pkg_', true);
    }

    /**
     * Comprueba si el paquete tiene un archivo README.md.
     *
     * @return bool
     */
    public function has_readme(): bool
    {
        return file_exists($this->path . '/README.md');
    }

    /**
     * Obtiene el contenido del README.md del paquete.
     *
     * @return string
     */
    public function get_readme(): string
    {
        if (!$this->has_readme()) {
            return '';
        }
        $content = file_get_contents($this->path . '/README.md');
        return $content !== false ? $content : '';
    }

    /**
     * Obtiene la lista de subcarpetas de ejemplos.
     *
     * @return string[]
     */
    public function get_examples(): array
    {
        $examples_dir = $this->path . '/examples';
        if (!is_dir($examples_dir)) {
            return array();
        }
        $items = scandir($examples_dir);
        if ($items === false) {
            return array();
        }
        $examples = array();
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            if (is_dir($examples_dir . '/' . $item)) {
                $examples[] = $item;
            }
        }
        return $examples;
    }

    /**
     * Obtiene la lista de archivos de un ejemplo específico.
     *
     * @param string $example Nombre de la carpeta del ejemplo.
     * @return string[]
     */
    public function get_example_files(string $example): array
    {
        $example_dir = $this->path . '/examples/' . sanitize_file_name($example);
        if (!is_dir($example_dir)) {
            return array();
        }
        $items = scandir($example_dir);
        if ($items === false) {
            return array();
        }
        $files = array();
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            if (is_file($example_dir . '/' . $item)) {
                $files[] = $item;
            }
        }
        return $files;
    }

    /**
     * Obtiene el contenido de un archivo de ejemplo específico.
     *
     * @param string $example Nombre de la carpeta del ejemplo.
     * @param string $file Nombre del archivo de ejemplo.
     * @return string
     */
    public function get_example_file_content(string $example, string $file): string
    {
        $file_path = $this->path . '/examples/' . sanitize_file_name($example) . '/' . sanitize_file_name($file);
        if (!is_file($file_path)) {
            return '';
        }
        $content = file_get_contents($file_path);
        return $content !== false ? $content : '';
    }

    /**
     * Obtiene los servicios compatibles declarados.
     *
     * @return string[]
     */
    public function get_compatibility(): array
    {
        return $this->manifest->get_compatibility();
    }
}
