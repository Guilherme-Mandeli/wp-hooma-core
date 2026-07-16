<?php

namespace Hooma\Core\Services\Events;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Servicio de Eventos de Hooma Core.
 *
 * Provee un sistema interno de mensajería desacoplado mediante eventos y filtros.
 * Oculta la implementación interna basada en el motor de hooks (acciones y filtros) de WordPress.
 */
class EventsService
{
    /**
     * Prefijo para aislar las acciones de WordPress.
     *
     * @var string
     */
    protected $action_prefix = 'hooma_event_';

    /**
     * Prefijo para aislar los filtros de WordPress.
     *
     * @var string
     */
    protected $filter_prefix = 'hooma_filter_';

    /**
     * Dispara un evento del sistema y notifica a todos los escuchadores.
     *
     * @param string $event Nombre del evento en notación de namespace (ej. 'booking.created').
     * @param mixed ...$payload Datos y objetos que se pasan al evento.
     * @return EventResult
     */
    public function dispatch(string $event, ...$payload): EventResult
    {
        $hook_name = $this->action_prefix . $event;
        $listeners_count = $this->get_listeners_count($hook_name);

        // Despacha la acción interna en WordPress
        do_action($hook_name, ...$payload);

        return new EventResult($listeners_count);
    }

    /**
     * Registra un escuchador (listener) para un evento determinado.
     *
     * @param string $event Nombre del evento (ej. 'booking.created').
     * @param callable $callback Función a ejecutar cuando ocurra el evento.
     * @param int $priority Prioridad de ejecución (menor número ejecuta antes).
     * @param int $accepted_args Cantidad de parámetros que acepta el callback.
     */
    public function listen(string $event, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $hook_name = $this->action_prefix . $event;
        add_action($hook_name, $callback, $priority, $accepted_args);
    }

    /**
     * Pasa un valor a través de una cadena de filtros modificadores de datos.
     *
     * @param string $event Nombre del filtro (ej. 'booking.price').
     * @param mixed $value Valor inicial a mutar.
     * @param mixed ...$payload Parámetros contextuales adicionales no editables.
     * @return mixed El valor procesado y modificado tras ejecutarse todos los filtros.
     */
    public function filter(string $event, $value, ...$payload)
    {
        $hook_name = $this->filter_prefix . $event;
        return apply_filters($hook_name, $value, ...$payload);
    }

    /**
     * Obtiene el número total de callbacks registrados para un hook específico.
     *
     * @param string $hook_name Nombre interno del hook en WordPress.
     * @return int
     */
    protected function get_listeners_count(string $hook_name): int
    {
        global $wp_filter;

        if (!isset($wp_filter[$hook_name])) {
            return 0;
        }

        $count = 0;
        // WordPress organiza los callbacks por prioridad en un array bidimensional
        foreach ($wp_filter[$hook_name]->callbacks as $priority => $callbacks) {
            $count += count($callbacks);
        }

        return $count;
    }
}
