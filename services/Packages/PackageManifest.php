<?php

namespace Hooma\Core\Services\Packages;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Representación inmutable del manifiesto (manifest.json) de un Package.
 */
class PackageManifest
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var PackageType
     */
    protected $type;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $license;

    /**
     * @var string
     */
    protected $homepage;

    /**
     * @var string[]
     */
    protected $keywords;

    /**
     * @var array<string, string>
     */
    protected $entries;

    /**
     * Constructor del manifiesto.
     *
     * Valida y rellena los datos desde un array asociativo del JSON decodificado.
     *
     * @param array $data
     * @throws \InvalidArgumentException Si la validación falla.
     */
    public function __construct(array $data)
    {
        // 1. Validar Name
        if (empty($data['name']) || !is_string($data['name'])) {
            throw new \InvalidArgumentException('El manifiesto del paquete requiere una propiedad "name" válida de tipo string.');
        }
        $this->name = $data['name'];

        // 2. Validar Version
        if (empty($data['version']) || !is_string($data['version'])) {
            throw new \InvalidArgumentException(sprintf('El manifiesto del paquete "%s" requiere una propiedad "version" válida de tipo string.', $this->name));
        }
        // Validación básica de versión semántica o similar
        if (!preg_match('/^\d+(\.\d+)*(-[a-zA-Z0-9\.]+)?$/', $data['version'])) {
            throw new \InvalidArgumentException(sprintf('El manifiesto del paquete "%s" tiene una versión inválida: "%s".', $this->name, $data['version']));
        }
        $this->version = $data['version'];

        // 3. Validar Type
        if (empty($data['type']) || !is_string($data['type'])) {
            throw new \InvalidArgumentException(sprintf('El manifiesto del paquete "%s" requiere una propiedad "type" válida de tipo string.', $this->name));
        }
        
        $type_enum = PackageType::tryFrom(strtolower($data['type']));
        if ($type_enum === null) {
            throw new \InvalidArgumentException(sprintf('El tipo de paquete "%s" declarado en "%s" no es válido.', $data['type'], $this->name));
        }
        $this->type = $type_enum;

        // 4. Opcionales estándar
        $this->description = isset($data['description']) && is_string($data['description']) ? $data['description'] : '';
        $this->author = isset($data['author']) && is_string($data['author']) ? $data['author'] : '';
        $this->license = isset($data['license']) && is_string($data['license']) ? $data['license'] : '';
        $this->homepage = isset($data['homepage']) && is_string($data['homepage']) ? $data['homepage'] : '';

        // 5. Keywords (debe ser un array de strings)
        $keywords = array();
        if (isset($data['keywords'])) {
            if (!is_array($data['keywords'])) {
                throw new \InvalidArgumentException(sprintf('La propiedad "keywords" en "%s" debe ser un array.', $this->name));
            }
            foreach ($data['keywords'] as $kw) {
                if (is_string($kw)) {
                    $keywords[] = $kw;
                }
            }
        }
        $this->keywords = $keywords;

        // 6. Entries (debe ser un array/objeto de strings)
        $entries = array();
        if (isset($data['entries'])) {
            if (!is_array($data['entries'])) {
                throw new \InvalidArgumentException(sprintf('La propiedad "entries" en "%s" debe ser un objeto/array asociativo de entradas.', $this->name));
            }
            foreach ($data['entries'] as $key => $val) {
                if (is_string($key) && is_string($val)) {
                    $entries[$key] = $val;
                }
            }
        }
        
        // Debe tener al menos una entrada si es un package que requiere punto de acceso directo (opcional para templates/assets puros, pero validado)
        $this->entries = $entries;
    }

    /**
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function get_version(): string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function get_description(): string
    {
        return $this->description;
    }

    /**
     * @return PackageType
     */
    public function get_type(): PackageType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function get_author(): string
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function get_license(): string
    {
        return $this->license;
    }

    /**
     * @return string
     */
    public function get_homepage(): string
    {
        return $this->homepage;
    }

    /**
     * @return string[]
     */
    public function get_keywords(): array
    {
        return $this->keywords;
    }

    /**
     * @return array<string, string>
     */
    public function get_entries(): array
    {
        return $this->entries;
    }
}
