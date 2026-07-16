<?php

namespace Hooma\Core\Services\Settings;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Implementación del servicio de gestión de configuraciones para Hooma Core.
 *
 * Utiliza la API de opciones nativa de WordPress en segundo plano, ofreciendo
 * tipado dinámico, sanitización automática y soporte para valores por defecto.
 */
class SettingsService implements SettingsInterface
{
    /**
     * Valores de configuración por defecto.
     *
     * @var array
     */
    protected $defaults = array();

    /**
     * Prefijo para las opciones de WordPress para evitar conflictos.
     *
     * @var string
     */
    protected $prefix = 'hooma_';

    /**
     * Obtiene el valor de una configuración.
     *
     * @param string $key Nombre de la configuración.
     * @param mixed $default Valor por defecto si la clave no existe.
     * @return mixed Valor configurado o el valor por defecto.
     */
    public function get($key, $default = null)
    {
        $wp_key = $this->get_wp_key($key);
        $sentinel = '__hooma_not_found__';

        $value = get_option($wp_key, $sentinel);

        if ($value === $sentinel) {
            // Si no existe en base de datos, buscar en los valores por defecto
            if (array_key_exists($key, $this->defaults)) {
                return $this->defaults[$key];
            }
            return $default;
        }

        return $value;
    }

    /**
     * Guarda o actualiza el valor de una configuración.
     *
     * @param string $key Nombre de la configuración.
     * @param mixed $value Valor a guardar.
     * @return bool True en caso de éxito, false de lo contrario.
     */
    public function set($key, $value)
    {
        $wp_key = $this->get_wp_key($key);
        
        // Sanitizar el valor según su tipo
        $sanitized_value = $this->sanitize_value($value);

        return update_option($wp_key, $sanitized_value);
    }

    /**
     * Elimina una configuración.
     *
     * @param string $key Nombre de la configuración.
     * @return bool True si se eliminó correctamente, false de lo contrario.
     */
    public function delete($key)
    {
        $wp_key = $this->get_wp_key($key);
        return delete_option($wp_key);
    }

    /**
     * Verifica si una configuración existe (ya sea en base de datos o por defecto).
     *
     * @param string $key Nombre de la configuración.
     * @return bool True si existe, false en caso contrario.
     */
    public function exists($key)
    {
        $wp_key = $this->get_wp_key($key);
        $sentinel = '__hooma_not_found__';

        $value = get_option($wp_key, $sentinel);

        if ($value !== $sentinel) {
            return true;
        }

        return array_key_exists($key, $this->defaults);
    }

    /**
     * Establece los valores por defecto para las configuraciones.
     *
     * @param array $defaults Array asociativo de clave => valor por defecto.
     */
    public function set_defaults(array $defaults)
    {
        $this->defaults = array_merge($this->defaults, $defaults);
    }

    /**
     * Convierte y normaliza la clave de configuración para WordPress.
     *
     * @param string $key
     * @return string
     */
    protected function get_wp_key($key)
    {
        // Sanitiza la clave permitiendo caracteres alfanuméricos, puntos, guiones y guiones bajos
        $clean_key = preg_replace('/[^a-z0-9_\-\.]/i', '', $key);
        return $this->prefix . strtolower($clean_key);
    }

    /**
     * Sanitiza el valor según el tipo de dato.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function sanitize_value($value)
    {
        if (is_string($value)) {
            return sanitize_text_field($value);
        }

        if (is_array($value)) {
            $sanitized = array();
            foreach ($value as $k => $v) {
                $sanitized[$k] = $this->sanitize_value($v);
            }
            return $sanitized;
        }

        return $value;
    }
}
