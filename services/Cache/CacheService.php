<?php

namespace Hooma\Core\Services\Cache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Implementación del servicio de Caché para Hooma Core.
 *
 * Utiliza Transients de WordPress por debajo. Aplica hashing SHA-1 en las claves
 * para evitar sobrepasar el límite de 172 caracteres de WordPress. Evita el "bug de falso"
 * envolviendo el valor en una estructura estructurada.
 */
class CacheService implements CacheInterface
{
    /**
     * Prefijo para los transients de WordPress.
     *
     * @var string
     */
    protected $prefix = 'hooma_c_';

    /**
     * Tiempo de expiración por defecto en segundos (1 día).
     *
     * @var int
     */
    protected $default_ttl = 86400;

    /**
     * Obtiene un elemento de la caché.
     *
     * @param string $key Clave única del elemento.
     * @param mixed $default Valor por defecto si no existe o ha expirado.
     * @return mixed Valor almacenado o por defecto.
     */
    public function get(string $key, $default = null)
    {
        $hash_key = $this->get_transient_key($key);
        $cached = get_transient($hash_key);

        if ($cached === false) {
            return $default;
        }

        // Evitar falso positivo si el transient expiró o si guardaba un booleano falso
        if (is_array($cached) && array_key_exists('val', $cached)) {
            return $cached['val'];
        }

        return $default;
    }

    /**
     * Almacena un elemento en la caché con un tiempo de vida (TTL) determinado.
     *
     * @param string $key Clave única del elemento.
     * @param mixed $value Valor a almacenar.
     * @param int|null $ttl Tiempo de vida en segundos.
     * @return bool True si se guardó con éxito, false de lo contrario.
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $hash_key = $this->get_transient_key($key);
        $data = array('val' => $value);
        $expiration = ($ttl === null) ? $this->default_ttl : $ttl;

        return set_transient($hash_key, $data, $expiration);
    }

    /**
     * Elimina un elemento de la caché.
     *
     * @param string $key Clave única del elemento.
     * @return bool True si se eliminó con éxito, false de lo contrario.
     */
    public function delete(string $key): bool
    {
        $hash_key = $this->get_transient_key($key);
        return delete_transient($hash_key);
    }

    /**
     * Obtiene un elemento de la caché, o ejecuta un callback para generarlo y guardarlo si no existe.
     *
     * @param string $key Clave única del elemento.
     * @param int|null $ttl Tiempo de vida en segundos.
     * @param callable $callback Función para generar el valor si no existe.
     * @return mixed El valor almacenado o el recién generado.
     */
    public function remember(string $key, ?int $ttl, callable $callback)
    {
        $sentinel = '__hooma_cache_miss__';
        $value = $this->get($key, $sentinel);

        if ($value !== $sentinel) {
            return $value;
        }

        // Generar valor mediante callback
        $generated = call_user_func($callback);
        $this->set($key, $generated, $ttl);

        return $generated;
    }

    /**
     * Obtiene la clave segura del transient aplicando hashing SHA-1.
     *
     * @param string $key
     * @return string
     */
    protected function get_transient_key(string $key): string
    {
        return $this->prefix . sha1($key);
    }
}
