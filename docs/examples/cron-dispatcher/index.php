<?php
/**
 * Module Name: Ejemplo Cron y Eventos
 * Description: Módulo demostrativo que ejecuta una tarea programada y despacha un evento global para su consumo.
 * Version: 1.0.0
 * Author: Technical Writer Hooma
 */

namespace HoomaExamples\CronDemo;

use Hooma;

if (!defined('ABSPATH')) {
    exit;
}

// 1. Programación del Cron diario
add_action('hooma_init', function() {
    Hooma::scheduler()->daily(
        'examples.daily_sync',
        array(SyncDispatcher::class, 'trigger')
    );
});

// 2. Oyente o Listener del evento en el mismo módulo (desacoplado)
Hooma::events()->listen('examples.sync_triggered', function($payload) {
    Hooma::logger()->info('Ejemplo Cron: Evento de sincronización procesado.', $payload);
});

class SyncDispatcher
{
    /**
     * Callback persistente invocado por WP-Cron.
     */
    public static function trigger()
    {
        Hooma::logger()->info('Ejemplo Cron: Tarea diaria ejecutada por WP-Cron.');

        // Despachar evento global a todo el ecosistema
        Hooma::events()->dispatch('examples.sync_triggered', array(
            'timestamp' => time(),
            'source'    => 'wp_cron'
        ));
    }
}
