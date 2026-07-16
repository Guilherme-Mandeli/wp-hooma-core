<?php

namespace Hooma\Core\Services\Config;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Servicio de Configuración Interna de Hooma Core.
 *
 * Centraliza el acceso a constantes de infraestructura, rutas, URLs y namespaces del Framework,
 * previniendo el uso disperso de constantes globales o variables de entorno de WordPress.
 */
class ConfigService
{
    /**
     * Estructura de configuración interna.
     *
     * @var array
     */
    protected $config = array();

    /**
     * Constructor de ConfigService.
     */
    public function __construct()
    {
        $this->load_defaults();
    }

    /**
     * Obtiene una configuración interna mediante notación de puntos (dot-notation).
     *
     * @param string $key Clave de configuración (ej. 'paths.root').
     * @param mixed $default Valor de retorno opcional si la clave no se encuentra.
     * @return mixed Valor configurado o el valor por defecto.
     */
    public function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $current = $this->config;

        foreach ($parts as $part) {
            if (is_array($current) && array_key_exists($part, $current)) {
                $current = $current[$part];
            } else {
                return $default;
            }
        }

        return $current;
    }

    /**
     * Establece o sobrescribe dinámicamente un valor de configuración en tiempo de ejecución.
     *
     * @param string $key Clave en notación de puntos.
     * @param mixed $value Valor a asignar.
     */
    public function set(string $key, $value): void
    {
        $parts = explode('.', $key);
        $current = &$this->config;

        foreach ($parts as $part) {
            if (!isset($current[$part]) || !is_array($current[$part])) {
                $current[$part] = array();
            }
            $current = &$current[$part];
        }

        $current = $value;
    }

    /**
     * Carga y centraliza las constantes e información de entorno de Hooma.
     */
    protected function load_defaults(): void
    {
        $this->config = array(
            'version' => defined('HOOMA_VERSION') ? HOOMA_VERSION : '1.0.0',
            'paths'   => array(
                'root'    => defined('HOOMA_PATH') ? HOOMA_PATH : '',
                'modules' => defined('HOOMA_MODULES_PATH') ? HOOMA_MODULES_PATH : '',
            ),
            'urls'    => array(
                'root'    => defined('HOOMA_URL') ? HOOMA_URL : '',
                'modules' => defined('HOOMA_MODULES_URL') ? HOOMA_MODULES_URL : '',
            ),
            'namespaces' => array(
                'modules' => defined('HOOMA_MODULES_NAMESPACE') ? HOOMA_MODULES_NAMESPACE : 'HoomaModules',
            ),
        );
    }
}
