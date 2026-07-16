<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cargador de módulos de Hooma Core.
 *
 * Utiliza el servicio de módulos global para identificar y cargar los módulos activos.
 */
class Hooma_Loader
{
    /**
     * Inicia la carga de módulos.
     */
    public function run()
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Hooma Loader: run() called.');
        }
        $this->load_active_modules();
    }

    /**
     * Obtiene todos los módulos detectados en el sistema (activos e inactivos).
     *
     * @return array
     * @deprecated 1.1.0 Usar Hooma::modules()->get_modules() en su lugar.
     */
    public function get_modules()
    {
        return Hooma::modules()->get_modules();
    }

    /**
     * Carga todos los módulos activos del sistema en tiempo de ejecución.
     */
    private function load_active_modules()
    {
        $modules_service = Hooma::modules();
        $all_modules = $modules_service->get_modules();
        $active_modules = $modules_service->get_active_modules();

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Hooma Loader: Active modules: ' . print_r($active_modules, true));
        }

        foreach ($all_modules as $id => $module) {
            // Comprobar si el módulo está activo
            if (!in_array($id, $active_modules)) {
                continue;
            }

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Hooma Loader: Loading module $id");
            }

            // Preparar metadatos para el módulo
            $module_slug = $id;
            // Namespace: hooma-example-module -> HoomaExampleModule
            $module_namespace = str_replace(' ', '', ucwords(str_replace('-', ' ', $module_slug)));
            $module_version = !empty($module['version']) ? $module['version'] : HOOMA_VERSION;

            // Definir constantes globales para este módulo específico
            $const_prefix = 'HOOMA_' . strtoupper(str_replace('-', '_', $module_slug));

            if (!defined($const_prefix . '_SLUG')) {
                define($const_prefix . '_SLUG', $module_slug);
            }
            if (!defined($const_prefix . '_NAMESPACE')) {
                define($const_prefix . '_NAMESPACE', $module_namespace);
            }
            if (!defined($const_prefix . '_VERSION')) {
                define($const_prefix . '_VERSION', $module_version);
            }

            // Exponer variables en el ámbito de carga del archivo
            $modules_url = HOOMA_MODULES_URL;
            $modules_path = HOOMA_MODULES_PATH;

            // Archivo de inicio del módulo resuelto por el servicio
            $main_file = isset($module['main_file']) ? $module['main_file'] : $module['path'] . '/index.php';

            if (file_exists($main_file)) {
                include_once $main_file;
            }
        }
    }
}
