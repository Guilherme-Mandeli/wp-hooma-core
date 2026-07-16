<?php

namespace Hooma\Core\Services\Settings;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interfaz para el servicio de gestión de configuraciones y opciones.
 *
 * Provee un contrato estable para interactuar con la base de datos de WordPress
 * aislando a los módulos de llamadas directas como `get_option` o `update_option`.
 */
interface SettingsInterface
{
    /**
     * Obtiene el valor de una configuración.
     *
     * @param string $key Nombre de la configuración.
     * @param mixed $default Valor por defecto si la clave no existe.
     * @return mixed Valor configurado o el valor por defecto.
     */
    public function get($key, $default = null);

    /**
     * Define o actualiza el valor de una configuración.
     *
     * @param string $key Nombre de la configuración.
     * @param mixed $value Valor a almacenar.
     * @return bool True si se actualizó con éxito, false si falló.
     */
    public function set($key, $value);

    /**
     * Elimina una configuración del almacén.
     *
     * @param string $key Nombre de la configuración.
     * @return bool True si se eliminó con éxito, false en caso contrario.
     */
    public function delete($key);

    /**
     * Comprueba si existe una configuración almacenada.
     *
     * @param string $key Nombre de la configuración.
     * @return bool True si existe la clave, false en caso contrario.
     */
    public function exists($key);

    /**
     * Establece valores por defecto que se utilizarán cuando la configuración no esté definida.
     *
     * @param array $defaults Array asociativo de configuraciones por defecto (key => value).
     */
    public function set_defaults(array $defaults);
}
