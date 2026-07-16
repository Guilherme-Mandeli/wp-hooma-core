<?php

namespace Hooma\Core\Services\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Servicio de Base de Datos para Hooma Core.
 *
 * Wrapper seguro y tipado sobre el objeto global $wpdb de WordPress.
 * Facilita operaciones habituales de lectura/escritura y el formateo de placeholders
 * sin construir un ORM redundante en el Core.
 */
class DatabaseService
{
    /**
     * Ejecuta una consulta SQL genérica.
     *
     * @param string $query Sentencia SQL (puede incluir placeholders de tipo %s, %d).
     * @param array $args Argumentos de reemplazo para los placeholders.
     * @return int|bool Número de filas afectadas, o false en caso de fallo.
     */
    public function query(string $query, array $args = array())
    {
        global $wpdb;
        if (!empty($args)) {
            $query = $wpdb->prepare($query, ...$args);
        }
        return $wpdb->query($query);
    }

    /**
     * Obtiene los resultados de una consulta en forma de array asociativo.
     *
     * @param string $query Sentencia SQL.
     * @param array $args Argumentos de reemplazo.
     * @return array Listado de filas (array asociativo por fila).
     */
    public function get_results(string $query, array $args = array()): array
    {
        global $wpdb;
        if (!empty($args)) {
            $query = $wpdb->prepare($query, ...$args);
        }
        $results = $wpdb->get_results($query, ARRAY_A);
        return is_array($results) ? $results : array();
    }

    /**
     * Obtiene una única fila de la consulta SQL.
     *
     * @param string $query Sentencia SQL.
     * @param array $args Argumentos de reemplazo.
     * @return object|null Objeto fila obtenido o null si no se encontraron registros.
     */
    public function get_row(string $query, array $args = array())
    {
        global $wpdb;
        if (!empty($args)) {
            $query = $wpdb->prepare($query, ...$args);
        }
        $row = $wpdb->get_row($query, OBJECT);
        return is_object($row) ? $row : null;
    }

    /**
     * Obtiene el valor de una única columna de la primera fila resultante.
     *
     * @param string $query Sentencia SQL.
     * @param array $args Argumentos de reemplazo.
     * @return mixed Valor único de celda o null si no existe.
     */
    public function get_var(string $query, array $args = array())
    {
        global $wpdb;
        if (!empty($args)) {
            $query = $wpdb->prepare($query, ...$args);
        }
        return $wpdb->get_var($query);
    }

    /**
     * Inserta un registro en una tabla de la base de datos.
     *
     * @param string $table Nombre de la tabla.
     * @param array $data Array asociativo de clave => valor.
     * @return int|bool Número de registros insertados o false en caso de error.
     */
    public function insert(string $table, array $data)
    {
        global $wpdb;
        return $wpdb->insert($table, $data);
    }

    /**
     * Actualiza registros en una tabla de la base de datos.
     *
     * @param string $table Nombre de la tabla.
     * @param array $data Columnas a modificar (clave => valor).
     * @param array $where Condiciones de filtrado (clave => valor).
     * @return int|bool Número de filas actualizadas o false en caso de error.
     */
    public function update(string $table, array $data, array $where)
    {
        global $wpdb;
        return $wpdb->update($table, $data, $where);
    }

    /**
     * Elimina registros de una tabla de la base de datos.
     *
     * @param string $table Nombre de la tabla.
     * @param array $where Condiciones de filtrado (clave => valor).
     * @return int|bool Número de filas eliminadas o false en caso de error.
     */
    public function delete(string $table, array $where)
    {
        global $wpdb;
        return $wpdb->delete($table, $where);
    }

    /**
     * Obtiene el prefijo de tablas de WordPress.
     *
     * @return string
     */
    public function prefix(): string
    {
        global $wpdb;
        return $wpdb->prefix;
    }
}
