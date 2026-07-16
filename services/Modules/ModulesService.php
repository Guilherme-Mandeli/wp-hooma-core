<?php

namespace Hooma\Core\Services\Modules;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Implementación del servicio de gestión de Módulos para Hooma Core.
 *
 * Escanea el directorio hooma-modules para descubrir la información y cabeceras
 * de los submódulos, administrando también su estado de activación.
 */
class ModulesService implements ModulesInterface
{
    /**
     * Caché en memoria para evitar escaneos de disco repetidos.
     *
     * @var array|null
     */
    protected $modules = null;

    /**
     * Ruta física absoluta del directorio de módulos.
     *
     * @var string
     */
    protected $modules_dir;

    /**
     * Constructor de ModulesService.
     */
    public function __construct()
    {
        if (defined('HOOMA_MODULES_PATH')) {
            $this->modules_dir = HOOMA_MODULES_PATH;
        } else {
            $new_path = WP_CONTENT_DIR . '/hooma/modules/';
            $old_path = WP_CONTENT_DIR . '/hooma-modules/';
            $this->modules_dir = is_dir($old_path) && !is_dir($new_path) ? $old_path : $new_path;
        }
    }

    /**
     * Obtiene todos los módulos detectados en el sistema (activos e inactivos).
     *
     * @return array Listado de módulos con sus metadatos.
     */
    public function get_modules()
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        $this->modules = array();

        if (!is_dir($this->modules_dir)) {
            return $this->modules;
        }

        $dirs = scandir($this->modules_dir);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $module_path = wp_normalize_path($this->modules_dir . '/' . $dir);
            if (is_dir($module_path)) {
                // Estrategia de búsqueda de archivo principal: slug.php primero, luego index.php
                $main_file = '';
                $potential_files = array(
                    $dir . '.php',
                    'index.php'
                );

                foreach ($potential_files as $file) {
                    $potential_path = wp_normalize_path($module_path . '/' . $file);
                    if (file_exists($potential_path)) {
                        $main_file = $potential_path;
                        break;
                    }
                }

                if (!$main_file) {
                    continue;
                }

                $data = get_file_data($main_file, array(
                    'Name'            => 'Module Name',
                    'PluginName'      => 'Plugin Name',
                    'Description'     => 'Description',
                    'Version'         => 'Version',
                    'RequiresHooma'   => 'Requires Hooma',
                    'RequiresPlugins' => 'Requires Plugins',
                    'RequiresModules' => 'Requires Modules',
                ));

                $name = !empty($data['Name']) ? $data['Name'] : $data['PluginName'];
                if (empty($name)) {
                    continue;
                }

                $this->modules[$dir] = array(
                    'id'               => $dir,
                    'name'             => $name,
                    'description'      => $data['Description'],
                    'version'          => $data['Version'],
                    'requires_hooma'   => $data['RequiresHooma'],
                    'requires_plugins' => $data['RequiresPlugins'],
                    'requires_modules' => $data['RequiresModules'],
                    'path'             => $module_path,
                    'main_file'        => $main_file,
                );
            }
        }

        return $this->modules;
    }

    /**
     * Obtiene el listado de IDs de módulos que se encuentran activos.
     *
     * @return array Array con los slugs/IDs de los módulos activos.
     */
    public function get_active_modules()
    {
        return get_option('hooma_active_modules', array());
    }

    /**
     * Comprueba si un módulo específico está activo.
     *
     * @param string $id Slug/ID del módulo.
     * @return bool True si está activo, false de lo contrario.
     */
    public function is_active($id)
    {
        $active = $this->get_active_modules();
        return in_array($id, $active);
    }

    /**
     * Activa un módulo en el sistema.
     *
     * @param string $id Slug/ID del módulo.
     * @return bool|\WP_Error True en caso de éxito, objeto WP_Error si hay fallos.
     */
    public function activate($id)
    {
        $modules = $this->get_modules();
        if (!isset($modules[$id])) {
            return new \WP_Error('module_not_found', sprintf(__('El módulo "%s" no existe.', 'hooma'), $id));
        }

        $active = $this->get_active_modules();
        if (!in_array($id, $active)) {
            $active[] = $id;
            update_option('hooma_active_modules', array_values($active));
        }

        return true;
    }

    /**
     * Desactiva un módulo en el sistema.
     *
     * @param string $id Slug/ID del módulo.
     * @return bool|\WP_Error True en caso de éxito, objeto WP_Error si hay fallos.
     */
    public function deactivate($id)
    {
        $active = $this->get_active_modules();
        $key = array_search($id, $active);
        
        if ($key !== false) {
            unset($active[$key]);
            update_option('hooma_active_modules', array_values($active));
        }

        return true;
    }

    /**
     * Obtiene los metadatos o cabecera completa de un módulo específico.
     *
     * @param string $id Slug/ID del módulo.
     * @return array|null Metadatos del módulo o null si no se encuentra.
     */
    public function get_manifest($id)
    {
        $modules = $this->get_modules();
        return isset($modules[$id]) ? $modules[$id] : null;
    }

    /**
     * Obtiene la ruta física del directorio de un módulo específico.
     *
     * @param string $id Slug/ID del módulo.
     * @return string|null Ruta absoluta del módulo o null.
     */
    public function get_module_path($id)
    {
        $modules = $this->get_modules();
        return isset($modules[$id]) ? $modules[$id]['path'] : null;
    }

    /**
     * Obtiene la URL web de la carpeta de un módulo específico.
     *
     * @param string $id Slug/ID del módulo.
     * @return string|null URL web del módulo o null.
     */
    public function get_module_url($id)
    {
        if (defined('HOOMA_MODULES_URL')) {
            $base_url = HOOMA_MODULES_URL;
        } else {
            $new_url = content_url('hooma/modules/');
            $old_url = content_url('hooma-modules/');
            $new_path = WP_CONTENT_DIR . '/hooma/modules/';
            $old_path = WP_CONTENT_DIR . '/hooma-modules/';
            $base_url = is_dir($old_path) && !is_dir($new_path) ? $old_url : $new_url;
        }

        $path = $this->get_module_path($id);
        if (!$path) {
            return null;
        }

        return esc_url(trailingslashit($base_url) . $id);
    }
}
