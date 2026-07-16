<?php

namespace Hooma\Core\Services\Packages;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interfaz para el servicio de gestión de Packages (Packages Service).
 *
 * Define contratos para descubrir, validar y consultar la información
 * de los paquetes (dependencias reutilizables) en el sistema.
 */
interface PackagesInterface
{
    /**
     * Obtiene un paquete específico por su nombre.
     *
     * @param string $name Nombre del paquete.
     * @return Package La entidad del paquete.
     * @throws \InvalidArgumentException Si el paquete no existe o no tiene un manifiesto válido.
     */
    public function get(string $name): Package;

    /**
     * Comprueba si un paquete específico existe en el sistema.
     *
     * @param string $name Nombre del paquete.
     * @return bool True si existe, false en caso contrario.
     */
    public function exists(string $name): bool;

    /**
     * Obtiene la versión de un paquete específico.
     *
     * @param string $name Nombre del paquete.
     * @return string Versión del paquete.
     * @throws \InvalidArgumentException Si el paquete no existe.
     */
    public function version(string $name): string;

    /**
     * Obtiene la ruta física absoluta de un paquete específico.
     *
     * @param string $name Nombre del paquete.
     * @return string Ruta física absoluta de la carpeta del paquete.
     * @throws \InvalidArgumentException Si el paquete no existe.
     */
    public function path(string $name): string;

    /**
     * Obtiene la URL pública de un paquete específico.
     *
     * @param string $name Nombre del paquete.
     * @return string URL pública de la carpeta del paquete.
     * @throws \InvalidArgumentException Si el paquete no existe.
     */
    public function url(string $name): string;

    /**
     * Obtiene el manifiesto completo (PackageManifest) de un paquete específico.
     *
     * @param string $name Nombre del paquete.
     * @return PackageManifest Instancia del manifiesto.
     * @throws \InvalidArgumentException Si el paquete no existe.
     */
    public function manifest(string $name): PackageManifest;

    /**
     * Obtiene la ruta física absoluta del punto de entrada de un paquete específico.
     *
     * @param string $name Nombre del paquete.
     * @param string $entry_key Clave del punto de entrada (ej. 'production', 'development').
     * @return string Ruta física absoluta al archivo de entrada del paquete.
     * @throws \InvalidArgumentException Si el paquete no existe o la entrada no está configurada.
     */
    public function entry(string $name, string $entry_key = 'production'): string;

    /**
     * Listar todos los paquetes registrados en el sistema.
     *
     * @return Package[] Array asociativo de paquetes donde la clave es el nombre del paquete.
     */
    public function all(): array;

    /**
     * Busca todos los paquetes de un tipo específico.
     *
     * @param PackageType $type Tipo de paquete.
     * @return Package[] Colección de paquetes que coinciden con el tipo.
     */
    public function findByType(PackageType $type): array;

    /**
     * Busca todos los paquetes que contienen una palabra clave específica.
     *
     * @param string $keyword Palabra clave a buscar.
     * @return Package[] Colección de paquetes que contienen la palabra clave.
     */
    public function findByKeyword(string $keyword): array;
}
