<?php

namespace Hooma\Core\Services\Assets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interfaz para el servicio de Assets (Assets Service).
 *
 * Define métodos para registrar y encolar scripts (JS) y estilos (CSS)
 * en WordPress con soporte de cache busting y versionado automático.
 */
interface AssetsInterface
{
    /**
     * Registra un script JS sin encolarlo de inmediato.
     *
     * @param string $handle Nombre único del script.
     * @param string $src URL completa del archivo JS.
     * @param array $deps Dependencias del script (ej. array('jquery')).
     * @param string|bool|null $ver Versión del script. Si es false/null, se calcula automáticamente.
     * @param bool $in_footer Si el script debe cargarse en el footer de la página.
     * @return bool True si se registró con éxito, false en caso contrario.
     */
    public function register_script($handle, $src, $deps = array(), $ver = false, $in_footer = true);

    /**
     * Encola un script JS registrado o lo registra y encola en una sola operación.
     *
     * @param string $handle Nombre único del script.
     * @param string $src URL completa opcional (si no se registró previamente).
     * @param array $deps Dependencias del script.
     * @param string|bool|null $ver Versión del script.
     * @param bool $in_footer Si debe cargarse en el footer.
     */
    public function enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = true);

    /**
     * Registra un estilo CSS sin encolarlo de inmediato.
     *
     * @param string $handle Nombre único del estilo.
     * @param string $src URL completa del archivo CSS.
     * @param array $deps Dependencias del estilo.
     * @param string|bool|null $ver Versión del estilo. Si es false/null, se calcula automáticamente.
     * @param string $media Tipo de medio para el CSS (ej. 'all', 'print', 'screen').
     * @return bool True si se registró con éxito, false en caso contrario.
     */
    public function register_style($handle, $src, $deps = array(), $ver = false, $media = 'all');

    /**
     * Encola un estilo CSS registrado o lo registra y encola en una sola operación.
     *
     * @param string $handle Nombre único del estilo.
     * @param string $src URL completa opcional (si no se registró previamente).
     * @param array $deps Dependencias del estilo.
     * @param string|bool|null $ver Versión del estilo.
     * @param string $media Tipo de medio para el CSS.
     */
    public function enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all');
}
