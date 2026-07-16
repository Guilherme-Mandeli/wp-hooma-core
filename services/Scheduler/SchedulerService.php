<?php

namespace Hooma\Core\Services\Scheduler;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Servicio de Tareas Programadas (Scheduler Service).
 *
 * Abstrae y securiza la API WP-Cron de WordPress. Valida que los callbacks sean persistentes
 * (no closures) y maneja de forma segura las planificaciones recurrentes sin duplicaciones.
 */
class SchedulerService
{
    /**
     * Prefijo para identificar los eventos de cron en WordPress.
     *
     * @var string
     */
    protected $prefix = 'hooma_cron_';

    /**
     * Programa una tarea recurrente en el planificador.
     *
     * @param string $handle Nombre de negocio único de la tarea (ej. 'booking.cleanup').
     * @param string $recurrence Frecuencia del cron (ej: 'hourly', 'daily', 'twicedaily').
     * @param callable $callback Método ejecutor. Debe ser serializable (no funciones anónimas).
     * @param int|null $start_time Timestamp de inicio opcional. Por defecto es el momento actual.
     * @throws \InvalidArgumentException Si se intenta registrar una función anónima (closure).
     */
    public function schedule(string $handle, string $recurrence, $callback, ?int $start_time = null): void
    {
        // Bloquear funciones anónimas (closures) dado que WP-Cron las ejecuta de forma asíncrona en otra petición
        if ($callback instanceof \Closure) {
            throw new \InvalidArgumentException(
                'Hooma Scheduler Error: Las funciones anónimas (closures) no pueden ser serializadas por WP-Cron. ' .
                'Por favor, registra un método serializable, por ejemplo: array(MiClase::class, "metodo") o "MiClase::metodo".'
            );
        }

        $wp_hook = $this->prefix . $handle;

        // Registrar siempre el listener de acción de WP en la petición actual
        add_action($wp_hook, $callback);

        // Programar en la base de datos únicamente si no se encuentra ya agendado
        if (!$this->is_scheduled($handle)) {
            $start = ($start_time === null) ? time() : $start_time;
            wp_schedule_event($start, $recurrence, $wp_hook);
        }
    }

    /**
     * Elimina una tarea programada del planificador.
     *
     * @param string $handle Nombre de negocio único de la tarea.
     * @return bool True si se eliminó con éxito, false en caso contrario.
     */
    public function unschedule(string $handle): bool
    {
        $wp_hook = $this->prefix . $handle;
        $timestamp = wp_next_scheduled($wp_hook);

        if ($timestamp) {
            return (bool) wp_unschedule_event($timestamp, $wp_hook);
        }

        return false;
    }

    /**
     * Comprueba si una tarea está programada en el planificador de WordPress.
     *
     * @param string $handle Nombre único de la tarea.
     * @return bool
     */
    public function is_scheduled(string $handle): bool
    {
        $wp_hook = $this->prefix . $handle;
        return (bool) wp_next_scheduled($wp_hook);
    }

    /**
     * Obtiene la fecha/hora de la próxima ejecución programada para una tarea.
     *
     * @param string $handle Nombre único de la tarea.
     * @return int|bool Timestamp de la próxima ejecución, o false si no está programada.
     */
    public function next_run(string $handle)
    {
        $wp_hook = $this->prefix . $handle;
        return wp_next_scheduled($wp_hook);
    }

    /**
     * Atajo conveniente para programar una tarea de ejecución diaria.
     *
     * @param string $handle Nombre único de la tarea.
     * @param callable $callback Método ejecutor.
     */
    public function daily(string $handle, $callback): void
    {
        $this->schedule($handle, 'daily', $callback);
    }

    /**
     * Atajo conveniente para programar una tarea de ejecución horaria.
     *
     * @param string $handle Nombre único de la tarea.
     * @param callable $callback Método ejecutor.
     */
    public function hourly(string $handle, $callback): void
    {
        $this->schedule($handle, 'hourly', $callback);
    }
}
