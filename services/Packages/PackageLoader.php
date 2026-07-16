<?php

namespace Hooma\Core\Services\Packages;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cargador y descubridor de Packages de Hooma Core (Package Loader).
 *
 * Realiza el escaneo del directorio físico de paquetes, valida sus manifiestos
 * y los registra en el contenedor in-memory Registry de forma simétrica a Module Loader.
 */
class PackageLoader
{
    /**
     * @var PackagesRegistry
     */
    protected $registry;

    /**
     * Constructor del PackageLoader.
     *
     * @param PackagesRegistry $registry Contenedor de destino para registrar paquetes válidos.
     */
    public function __construct(PackagesRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Realiza el descubrimiento, validación y registro de paquetes.
     *
     * @param string $packages_dir Ruta física absoluta de la carpeta de paquetes.
     * @param string $packages_url URL pública base de la carpeta de paquetes.
     */
    public function run(string $packages_dir, string $packages_url): void
    {
        if (!is_dir($packages_dir)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Hooma Package Loader: El directorio de paquetes no existe: {$packages_dir}");
            }
            return;
        }

        $items = scandir($packages_dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $package_path = wp_normalize_path($packages_dir . '/' . $item);
            if (!is_dir($package_path)) {
                continue;
            }

            $manifest_path = wp_normalize_path($package_path . '/manifest.json');
            
            // Validación 1: manifest.json existe
            if (!file_exists($manifest_path)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log(sprintf('Hooma Package Loader: Se omitió el directorio "%s" porque falta el archivo manifest.json.', $item));
                }
                continue;
            }

            // Validación 2: JSON válido
            $json_content = file_get_contents($manifest_path);
            if ($json_content === false) {
                error_log(sprintf('Hooma Package Loader ERROR: No se puede leer el archivo manifest.json en "%s".', $item));
                continue;
            }

            $manifest_data = json_decode($json_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log(sprintf('Hooma Package Loader ERROR: manifest.json inválido en "%s". JSON Error: %s', $item, json_last_error_msg()));
                continue;
            }

            try {
                // Validación 3: Estructura del manifiesto (name, version, type, etc.)
                $manifest = new PackageManifest($manifest_data);
                $name = $manifest->get_name();

                // Validación 4: Nombres duplicados
                if ($this->registry->has($name)) {
                    error_log(sprintf('Hooma Package Loader ERROR: Conflicto de nombres. El paquete "%s" ya ha sido registrado por otro directorio.', $name));
                    continue;
                }

                // Validación 5: Los archivos de entrada (entries) declarados deben existir en el disco
                $entries = $manifest->get_entries();
                $all_entries_exist = true;
                foreach ($entries as $key => $relative_file) {
                    $entry_file_path = wp_normalize_path($package_path . '/' . ltrim($relative_file, '/'));
                    if (!file_exists($entry_file_path)) {
                        error_log(sprintf('Hooma Package Loader ERROR: El archivo de entrada "%s" ("%s") para el paquete "%s" no existe en el disco.', $key, $relative_file, $name));
                        $all_entries_exist = false;
                    }
                }

                if (!$all_entries_exist) {
                    continue;
                }

                // Generar instancia inmutable del paquete
                $package_url = esc_url(trailingslashit($packages_url) . $item);
                $package = new Package($package_path, $package_url, $manifest);

                // Registrar en el Registry
                $this->registry->register($package);

                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log(sprintf('Hooma Package Loader: Paquete "%s" (v%s) cargado correctamente.', $name, $manifest->get_version()));
                }

            } catch (\InvalidArgumentException $e) {
                error_log(sprintf('Hooma Package Loader ERROR de Validación en "%s": %s', $item, $e->getMessage()));
            }
        }
    }
}
