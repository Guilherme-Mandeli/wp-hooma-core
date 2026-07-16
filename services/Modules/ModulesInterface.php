<?php

namespace Hooma\Core\Services\Modules;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interfaz para el servicio de gestión de Módulos (Modules Service).
 *
 * Define contratos para descubrir, activar, desactivar y consultar la información
 * de los módulos disponibles en el sistema.
 */
interface ModulesInterface
{
    /**
     * Obtiene todos los módulos detectados en el sistema (activos e inactivos).
     *
     * @return array Listado de módulos con sus metadatos.
     */
    public function get_modules();

    /**
     * Obtiene el listado de IDs de módulos que se encuentran activos.
     *
     * @return array Array con los slugs/IDs de los módulos activos.
     */
    public function get_active_modules();

    /**
     * Comprueba si un módulo específico está activo.
     *
     * @param string $id Slug/ID del módulo.
     * @return bool True si está activo, false de lo contrario.
     */
    public function is_active($id);

    /**
     * Activa un módulo en el sistema.
     *
     * @param string $id Slug/ID del módulo.
     * @return bool|\WP_Error True en caso de éxito, objeto WP_Error si hay fallos.
     */
    public function activate($id);

    /**
     * Desactiva un módulo en el sistema.
     *
     * @param string $id Slug/ID del módulo.
     * @return bool|\WP_Error True en caso de éxito, objeto WP_Error si hay fallos.
     */
    public function deactivate($id);

    /**
     * Obtiene los metadatos o cabecera completa de un módulo específico.
     *
     * @param string $id Slug/ID del módulo.
     * @return array|null Metadatos del módulo o null si no se encuentra.
     */
    public function get_manifest($id);

    /**
     * Obtiene la ruta física del directorio de un módulo específico.
     *
     * @param string $id Slug/ID del módulo.
     * @return string|null Ruta absoluta del módulo o null.
     */
    public function get_module_path($id);

    /**
     * Obtiene la URL web de la carpeta de un módulo específico.
     *
     * @param string $id Slug/ID del módulo.
     * @return string|null URL web del módulo o null.
     */
    public function get_module_url($id);
}
