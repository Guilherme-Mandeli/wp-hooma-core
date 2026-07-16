<?php

namespace Hooma\Core\Services\Assets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Implementación del servicio de gestión de Assets para Hooma Core.
 *
 * Envuelve las funciones nativas de encolamiento de WordPress agregando
 * una resolución automática de cache busting basada en la fecha de modificación
 * del archivo local (filemtime) para scripts y estilos.
 */
class AssetsService implements AssetsInterface
{
    /**
     * Registra un script JS sin encolarlo de inmediato.
     *
     * @param string $handle Nombre único del script.
     * @param string $src URL completa del archivo JS.
     * @param array $deps Dependencias del script.
     * @param string|bool|null $ver Versión del script.
     * @param bool $in_footer Si el script debe cargarse en el footer de la página.
     * @return bool True si se registró con éxito, false en caso contrario.
     */
    public function register_script($handle, $src, $deps = array(), $ver = false, $in_footer = true)
    {
        $resolved_ver = $this->resolve_version($src, $ver);
        return wp_register_script($handle, $src, $deps, $resolved_ver, $in_footer);
    }

    /**
     * Encola un script JS registrado o lo registra y encola en una sola operación.
     *
     * @param string $handle Nombre único del script.
     * @param string $src URL completa opcional (si no se registró previamente).
     * @param array $deps Dependencias del script.
     * @param string|bool|null $ver Versión del script.
     * @param bool $in_footer Si debe cargarse en el footer.
     */
    public function enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = true)
    {
        if ($src) {
            $resolved_ver = $this->resolve_version($src, $ver);
            wp_enqueue_script($handle, $src, $deps, $resolved_ver, $in_footer);
        } else {
            wp_enqueue_script($handle);
        }
    }

    /**
     * Registra un estilo CSS sin encolarlo de inmediato.
     *
     * @param string $handle Nombre único del estilo.
     * @param string $src URL completa del archivo CSS.
     * @param array $deps Dependencias del estilo.
     * @param string|bool|null $ver Versión del estilo.
     * @param string $media Tipo de medio para el CSS.
     * @return bool True si se registró con éxito, false en caso contrario.
     */
    public function register_style($handle, $src, $deps = array(), $ver = false, $media = 'all')
    {
        $resolved_ver = $this->resolve_version($src, $ver);
        return wp_register_style($handle, $src, $deps, $resolved_ver, $media);
    }

    /**
     * Encola un estilo CSS registrado o lo registra y encola en una sola operación.
     *
     * @param string $handle Nombre único del estilo.
     * @param string $src URL completa opcional (si no se registró previamente).
     * @param array $deps Dependencias del estilo.
     * @param string|bool|null $ver Versión del estilo.
     * @param string $media Tipo de medio para el CSS.
     */
    public function enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all')
    {
        if ($src) {
            $resolved_ver = $this->resolve_version($src, $ver);
            wp_enqueue_style($handle, $src, $deps, $resolved_ver, $media);
        } else {
            wp_enqueue_style($handle);
        }
    }

    /**
     * Resuelve la versión de un asset. Si es false/null, intenta usar el tiempo de modificación del archivo.
     *
     * @param string $src URL del asset.
     * @param string|bool|null $ver Versión provista.
     * @return string
     */
    protected function resolve_version($src, $ver)
    {
        if ($ver !== false && $ver !== null) {
            return $ver;
        }

        $local_path = $this->get_local_path_from_url($src);
        if ($local_path && file_exists($local_path)) {
            return (string) filemtime($local_path);
        }

        return defined('HOOMA_VERSION') ? HOOMA_VERSION : '1.0.0';
    }

    /**
     * Convierte una URL de WordPress en su respectiva ruta física local.
     *
     * @param string $url URL del asset.
     * @return string|null Ruta física local o null si no se puede determinar.
     */
    protected function get_local_path_from_url($url)
    {
        $content_url = content_url();
        $content_dir = WP_CONTENT_DIR;

        // Comprobar si pertenece a wp-content
        if (strpos($url, $content_url) === 0) {
            $relative_path = str_replace($content_url, '', $url);
            return wp_normalize_path($content_dir . '/' . $relative_path);
        }

        // Comprobar si pertenece al raíz del sitio
        $home_url = home_url();
        if (strpos($url, $home_url) === 0) {
            $relative_path = str_replace($home_url, '', $url);
            return wp_normalize_path(ABSPATH . '/' . $relative_path);
        }

        return null;
    }
}
