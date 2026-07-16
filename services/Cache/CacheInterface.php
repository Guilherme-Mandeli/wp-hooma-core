<?php

namespace Hooma\Core\Services\Cache;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interfaz para el servicio de Caché (Cache Service).
 *
 * Provee contratos para almacenar datos temporalmente en memoria o base de datos.
 */
interface CacheInterface
{
    /**
     * Obtiene un elemento de la caché.
     *
     * @param string $key Clave única del elemento.
     * @param mixed $default Valor por defecto si no existe o ha expirado.
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Almacena un elemento en la caché con un tiempo de vida (TTL) determinado.
     *
     * @param string $key Clave única del elemento.
     * @param mixed $value Valor a almacenar (tipos simples, arrays u objetos serializables).
     * @param int|null $ttl Tiempo de vida en segundos. Si es null, se usa el valor por defecto.
     * @return bool True en caso de éxito, false de lo contrario.
     */
    public function set(string $key, $value, ?int $ttl = null): bool;

    /**
     * Elimina un elemento de la caché.
     *
     * @param string $key Clave única a eliminar.
     * @return bool True si se eliminó con éxito, false de lo contrario.
     */
    public function delete(string $key): bool;

    /**
     * Obtiene un elemento de la caché, o ejecuta un callback para generarlo y guardarlo si no existe.
     *
     * @param string $key Clave única del elemento.
     * @param int|null $ttl Tiempo de vida en segundos.
     * @param callable $callback Función para generar el valor si no existe.
     * @return mixed El valor almacenado o el recién generado.
     */
    public function remember(string $key, ?int $ttl, callable $callback);
}
