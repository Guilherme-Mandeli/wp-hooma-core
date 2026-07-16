<?php

namespace Hooma\Core\Services\Packages;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Servicio de gestión de Packages para Hooma Core.
 *
 * Expone la API pública estable Hooma::packages() delegando consultas al Registry en memoria,
 * el cual es poblado una sola vez durante el arranque mediante el PackageLoader.
 */
class PackagesService implements PackagesInterface
{
    /**
     * @var PackagesRegistry
     */
    protected $registry;

    /**
     * Constructor de PackagesService.
     *
     * Inicializa el registro y corre el descubrimiento de paquetes una sola vez.
     */
    public function __construct()
    {
        $this->registry = new PackagesRegistry();

        $packages_dir = defined('HOOMA_PACKAGES_PATH') ? HOOMA_PACKAGES_PATH : WP_CONTENT_DIR . '/hooma/packages/';
        $packages_url = defined('HOOMA_PACKAGES_URL') ? HOOMA_PACKAGES_URL : content_url('hooma/packages/');

        $loader = new PackageLoader($this->registry);
        $loader->run($packages_dir, $packages_url);
    }

    /**
     * Obtiene un paquete específico por su nombre.
     *
     * @param string $name
     * @return Package
     */
    public function get(string $name): Package
    {
        return $this->registry->get($name);
    }

    /**
     * Comprueba si un paquete específico existe en el sistema.
     *
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return $this->registry->has($name);
    }

    /**
     * Obtiene la versión de un paquete específico.
     *
     * @param string $name
     * @return string
     */
    public function version(string $name): string
    {
        return $this->get($name)->get_version();
    }

    /**
     * Obtiene la ruta física absoluta de un paquete específico.
     *
     * @param string $name
     * @return string
     */
    public function path(string $name): string
    {
        return $this->get($name)->get_path();
    }

    /**
     * Obtiene la URL pública de un paquete específico.
     *
     * @param string $name
     * @return string
     */
    public function url(string $name): string
    {
        return $this->get($name)->get_url();
    }

    /**
     * Obtiene el manifiesto completo de un paquete específico.
     *
     * @param string $name
     * @return PackageManifest
     */
    public function manifest(string $name): PackageManifest
    {
        return $this->get($name)->get_manifest();
    }

    /**
     * Obtiene la ruta física absoluta del punto de entrada de un paquete específico.
     *
     * @param string $name
     * @param string $entry_key
     * @return string
     */
    public function entry(string $name, string $entry_key = 'production'): string
    {
        $package = $this->get($name);
        $entry_path = $package->get_entry_path($entry_key);

        if (empty($entry_path)) {
            throw new \InvalidArgumentException(sprintf('El punto de entrada "%s" no está definido en el paquete "%s".', $entry_key, $name));
        }

        return $entry_path;
    }

    /**
     * Listar todos los paquetes registrados en el sistema.
     *
     * @return Package[]
     */
    public function all(): array
    {
        return $this->registry->all();
    }

    /**
     * Busca todos los paquetes de un tipo específico.
     *
     * @param PackageType $type
     * @return Package[]
     */
    public function findByType(PackageType $type): array
    {
        $result = array();
        foreach ($this->registry->all() as $package) {
            if ($package->get_type() === $type) {
                $result[$package->get_name()] = $package;
            }
        }
        return $result;
    }

    /**
     * Busca todos los paquetes que contienen una palabra clave específica.
     *
     * @param string $keyword
     * @return Package[]
     */
    public function findByKeyword(string $keyword): array
    {
        $result = array();
        $keyword = strtolower($keyword);
        foreach ($this->registry->all() as $package) {
            $keywords = array_map('strtolower', $package->get_manifest()->get_keywords());
            if (in_array($keyword, $keywords)) {
                $result[$package->get_name()] = $package;
            }
        }
        return $result;
    }
}
