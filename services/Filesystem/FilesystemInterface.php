<?php

namespace Hooma\Core\Services\Filesystem;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interfaz para el servicio de Archivos (Filesystem Service).
 *
 * Define contratos para manipular archivos y directorios de forma segura en el servidor,
 * aislando el backend de almacenamiento.
 */
interface FilesystemInterface
{
    /**
     * Lee el contenido de un archivo en texto plano.
     *
     * @param string $path Ruta física del archivo.
     * @return string Contenido del archivo.
     * @throws \RuntimeException Si el archivo no existe o no se puede leer.
     */
    public function read(string $path): string;

    /**
     * Escribe contenido en un archivo. Crea carpetas contenedoras si no existen.
     *
     * @param string $path Ruta física del archivo.
     * @param string $content Contenido a escribir.
     * @return bool True en caso de éxito, false de lo contrario.
     */
    public function write(string $path, string $content): bool;

    /**
     * Comprueba si un archivo o directorio existe en el servidor.
     *
     * @param string $path Ruta física.
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * Elimina un archivo o directorio.
     *
     * @param string $path Ruta física.
     * @param bool $recursive Eliminación recursiva si es un directorio.
     * @return bool True si se eliminó con éxito, false de lo contrario.
     */
    public function delete(string $path, bool $recursive = false): bool;

    /**
     * Copia un archivo o directorio de origen a destino.
     *
     * @param string $src Ruta origen.
     * @param string $dest Ruta destino.
     * @return bool True si se copió con éxito, false de lo contrario.
     */
    public function copy(string $src, string $dest): bool;

    /**
     * Crea un directorio de forma recursiva.
     *
     * @param string $path Ruta del directorio.
     * @return bool True en caso de éxito, false de lo contrario.
     */
    public function mkdir(string $path): bool;
}
