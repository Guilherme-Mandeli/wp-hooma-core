<?php
/**
 * Module Name: Ejemplo Cron Scheduler
 * Description: Demostración de programación de tareas en segundo plano usando callbacks serializables.
 * Version: 1.0.0
 * Author: Technical Writer Hooma
 * Text Domain: hooma-cron-demo
 * 
 * Nota: Este archivo es meramente ilustrativo para servir como referencia y ejemplo.
 */

namespace HoomaExamples\CronDemo;

use Hooma;
use Hooma_UI;

if (!defined('ABSPATH')) {
    exit;
}

// 1. Registro de la tarea programada durante el bootstrap del módulo
add_action('hooma_init', function() {
    Hooma::scheduler()->daily(
        'demo.cleanup_task',
        array(CleanupJob::class, 'execute')
    );
});

// 2. Panel administrativo para monitorear el estado del cron
add_action('admin_menu', function() {
    add_menu_page(
        'Cron Demo',
        'Cron Demo',
        'manage_options',
        'hooma-cron-demo',
        function() {
            $scheduler = Hooma::scheduler();
            $handle = 'demo.cleanup_task';

            // Forzar ejecución manual
            if (isset($_POST['run_now'])) {
                CleanupJob::execute();
                Hooma::notices()->success('Tarea de limpieza ejecutada manualmente de forma inmediata.');
            }

            $is_active = $scheduler->is_scheduled($handle);
            $next_run_ts = $scheduler->next_run($handle);
            ?>
            <div class="hooma-ui-wrapper">
                <?php echo Hooma_UI::header('Cron Scheduler Demo', 'v1.0.0'); ?>
                <div class="hooma-ui-container">
                    <h2>Estado del Planificador</h2>
                    <table class="form-table">
                        <tr>
                            <th>Identificador de Tarea</th>
                            <td><code><?php echo esc_html($handle); ?></code></td>
                        </tr>
                        <tr>
                            <th>Programación Diaria Activa</th>
                            <td>
                                <strong><?php echo $is_active ? '✅ SÍ' : '❌ NO'; ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Próxima Ejecución Programada</th>
                            <td>
                                <code>
                                    <?php echo $next_run_ts ? date('Y-m-d H:i:s', $next_run_ts) : 'No definida'; ?>
                                </code>
                            </td>
                        </tr>
                    </table>
                    <form method="POST" action="">
                        <?php submit_button('Ejecutar Ahora (Manual)', 'secondary', 'run_now'); ?>
                    </form>
                </div>
                <?php echo Hooma_UI::footer('Ejemplo de Tareas Programadas'); ?>
            </div>
            <?php
        }
    );
});

/**
 * Clase contenedora del Job. Cumple con la firma serializable requerida.
 */
class CleanupJob
{
    /**
     * Callback estático ejecutado por el Scheduler.
     */
    public static function execute()
    {
        // Acción de mantenimiento
        Hooma::logger()->info('Ejemplo Cron: Proceso de mantenimiento asíncrono ejecutado con éxito.');
    }
}
