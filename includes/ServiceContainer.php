<?php

namespace Hooma\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contenedor de Servicios para Hooma Core.
 *
 * Registra y resuelve las dependencias de infraestructura del Framework de forma diferida (lazy load).
 */
class ServiceContainer
{
    /**
     * Instancias compartidas (Singletons).
     *
     * @var array
     */
    protected $instances = array();

    /**
     * Definiciones de servicios registrados (callbacks o nombres de clase).
     *
     * @var array
     */
    protected $bindings = array();

    /**
     * Registra un servicio en el contenedor.
     *
     * @param string $id Identificador único del servicio.
     * @param mixed $concrete Callback de resolución o nombre de clase.
     */
    public function bind($id, $concrete)
    {
        $this->bindings[$id] = $concrete;
    }

    /**
     * Registra un servicio como Singleton en el contenedor.
     *
     * @param string $id Identificador único del servicio.
     * @param mixed $concrete Callback de resolución o nombre de clase.
     */
    public function singleton($id, $concrete)
    {
        $this->bindings[$id] = function () use ($concrete) {
            static $instance;
            if (null === $instance) {
                if (is_callable($concrete)) {
                    $instance = call_user_func($concrete, $this);
                } else {
                    $instance = new $concrete();
                }
            }
            return $instance;
        };
    }

    /**
     * Registra una instancia ya existente en el contenedor.
     *
     * @param string $id Identificador del servicio.
     * @param mixed $instance Instancia del servicio.
     */
    public function instance($id, $instance)
    {
        $this->instances[$id] = $instance;
    }

    /**
     * Obtiene y resuelve la instancia de un servicio registrado.
     *
     * @param string $id Identificador del servicio.
     * @return mixed Instancia resuelta del servicio.
     * @throws \Exception Si el servicio no está registrado ni es una clase instanciable.
     */
    public function get($id)
    {
        // Retornar instancia existente si ya se resolvió como singleton
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            $concrete = $this->bindings[$id];
            
            if (is_callable($concrete)) {
                $resolved = call_user_func($concrete, $this);
            } else {
                $resolved = new $concrete();
            }

            $this->instances[$id] = $resolved;
            return $resolved;
        }

        // Si es un nombre de clase instanciable, se resuelve directamente
        if (class_exists($id)) {
            $resolved = new $id();
            $this->instances[$id] = $resolved;
            return $resolved;
        }

        throw new \Exception("Hooma Core Container: El servicio '{$id}' no está registrado ni se pudo resolver.");
    }

    /**
     * Verifica si el servicio existe en el contenedor.
     *
     * @param string $id Identificador del servicio.
     * @return bool True si existe, false de lo contrario.
     */
    public function has($id)
    {
        return isset($this->instances[$id]) || isset($this->bindings[$id]) || class_exists($id);
    }
}
