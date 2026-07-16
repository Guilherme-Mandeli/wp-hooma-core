<?php

namespace Hooma\Core\Services\Notices;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Presentador/Renderizador de Avisos Administrativos.
 *
 * Escucha el hook admin_notices de WordPress, obtiene los avisos encolados en
 * NoticesService y genera la salida HTML correspondiente de forma segura.
 */
class NoticeRenderer
{
    /**
     * El servicio de notificaciones de Hooma Core.
     *
     * @var NoticesService
     */
    protected $service;

    /**
     * Constructor del presentador de notificaciones.
     *
     * @param NoticesService $service
     */
    public function __construct(NoticesService $service)
    {
        $this->service = $service;
    }

    /**
     * Registra el callback de renderizado en WordPress.
     */
    public function register(): void
    {
        add_action('admin_notices', array($this, 'render'));
    }

    /**
     * Procesa y escribe las etiquetas HTML de todas las alertas pendientes.
     */
    public function render(): void
    {
        $notices = $this->service->get_and_clear();
        
        if (empty($notices)) {
            return;
        }

        foreach ($notices as $notice) {
            $class = 'notice notice-' . esc_attr($notice['type']);
            if ($notice['dismissible']) {
                $class .= ' is-dismissible';
            }
            ?>
            <div class="<?php echo $class; ?>">
                <p><?php echo esc_html($notice['message']); ?></p>
            </div>
            <?php
        }
    }
}
