<?php

namespace Hooma\Core\Services\Notices;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Servicio de Notificaciones (Notices Service).
 *
 * Mantiene una cola en memoria para registrar avisos administrativos
 * sin acoplamiento a la estructura HTML ni a los hooks de WordPress.
 */
class NoticesService
{
    /**
     * Cola de avisos encolados.
     *
     * @var array
     */
    protected $notices = array();

    /**
     * Registra un aviso administrativo de tipo éxito (success).
     *
     * @param string $message Mensaje del aviso.
     * @param bool $dismissible Si el aviso puede ser cerrado por el usuario.
     */
    public function success(string $message, bool $dismissible = true): void
    {
        $this->queue('success', $message, $dismissible);
    }

    /**
     * Registra un aviso administrativo de tipo advertencia (warning).
     *
     * @param string $message Mensaje del aviso.
     * @param bool $dismissible Si el aviso puede ser cerrado por el usuario.
     */
    public function warning(string $message, bool $dismissible = true): void
    {
        $this->queue('warning', $message, $dismissible);
    }

    /**
     * Registra un aviso administrativo de tipo error.
     *
     * @param string $message Mensaje del aviso.
     * @param bool $dismissible Si el aviso puede ser cerrado por el usuario.
     */
    public function error(string $message, bool $dismissible = true): void
    {
        $this->queue('error', $message, $dismissible);
    }

    /**
     * Registra un aviso administrativo de tipo información (info).
     *
     * @param string $message Mensaje del aviso.
     * @param bool $dismissible Si el aviso puede ser cerrado por el usuario.
     */
    public function info(string $message, bool $dismissible = true): void
    {
        $this->queue('info', $message, $dismissible);
    }

    /**
     * Obtiene y vacía la lista de avisos acumulados.
     *
     * @return array Listado de avisos.
     */
    public function get_and_clear(): array
    {
        $notices = $this->notices;
        $this->notices = array();
        return $notices;
    }

    /**
     * Encola un aviso en la cola interna.
     *
     * @param string $type Tipo de aviso (success, warning, error, info).
     * @param string $message Mensaje a mostrar.
     * @param bool $dismissible Si se puede cerrar.
     */
    protected function queue(string $type, string $message, bool $dismissible): void
    {
        $this->notices[] = array(
            'type'        => $type,
            'message'     => $message,
            'dismissible' => $dismissible
        );
    }
}
