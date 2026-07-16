<?php

namespace Hooma\Core\Services\Events;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Objeto de resultado del disparo de un evento en Hooma.
 *
 * Contiene metadatos sobre la ejecución del evento, como el número de escuchadores
 * que estaban registrados en el momento de realizar el despacho (dispatch).
 */
class EventResult
{
    /**
     * Cantidad de listeners asociados.
     *
     * @var int
     */
    protected $listeners_count;

    /**
     * Constructor de EventResult.
     *
     * @param int $listeners_count Número de escuchadores registrados.
     */
    public function __construct(int $listeners_count)
    {
        $this->listeners_count = $listeners_count;
    }

    /**
     * Retorna el número total de escuchadores que fueron ejecutados al disparar el evento.
     *
     * @return int Cantidad de escuchadores.
     */
    public function listeners_executed(): int
    {
        return $this->listeners_count;
    }
}
