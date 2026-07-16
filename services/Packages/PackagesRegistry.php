<?php

namespace Hooma\Core\Services\Packages;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registro en memoria de Packages registrados en Hooma Core.
 */
class PackagesRegistry
{
    /**
     * @var array<string, Package>
     */
    protected $packages = array();

    /**
     * Registra un paquete en el registro.
     *
     * @param Package $package Instancia inmutable del paquete.
     * @throws \InvalidArgumentException Si el nombre del paquete ya existe en el registro.
     */
    public function register(Package $package): void
    {
        $name = $package->get_name();
        if ($this->has($name)) {
            throw new \InvalidArgumentException(sprintf('No se puede registrar el paquete "%s" porque ya existe en el registro.', $name));
        }

        $this->packages[$name] = $package;
    }

    /**
     * Obtiene un paquete registrado por su nombre.
     *
     * @param string $name
     * @return Package
     * @throws \InvalidArgumentException Si el paquete no existe.
     */
    public function get(string $name): Package
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf('El paquete "%s" no está registrado.', $name));
        }

        return $this->packages[$name];
    }

    /**
     * Comprueba si un paquete existe en el registro.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->packages[$name]);
    }

    /**
     * Obtiene todos los paquetes registrados en el sistema.
     *
     * @return Package[]
     */
    public function all(): array
    {
        return $this->packages;
    }
}
