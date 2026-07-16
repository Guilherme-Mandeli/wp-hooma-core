# Cookbook: Programar Tareas de Mantenimiento Diarias

*Disponible desde v1.0*

Esta receta muestra la solución completa para estructurar, registrar y agendar un proceso diario automático en segundo plano para mantenimiento de logs antiguos.

---

## Solución Completa

### 1. El Job Serializable
Crea el archivo `src/Jobs/LogCleanupJob.php` en tu módulo:

```php
<?php

namespace HoomaModules\MiModulo\Jobs;

use Hooma;

class LogCleanupJob
{
    /**
     * Ejecutor de mantenimiento.
     */
    public static function cleanOldLogs()
    {
        $db = Hooma::database();
        $table = $db->prefix() . 'hooma_logs';

        // Borrar logs con más de 30 días de antigüedad
        $limite = date('Y-m-d H:i:s', strtotime('-30 days'));
        
        $filas_afectadas = $db->query(
            "DELETE FROM `{$table}` WHERE fecha_registro < %s",
            array($limite)
        );

        Hooma::logger()->info(sprintf(
            "Limpieza de logs completada. Registros eliminados: %d",
            $filas_afectadas
        ));
    }
}
```

---

### 2. Registro del Cron (en `index.php`)

Registramos la tarea periódica en el bootstrap del módulo de forma segura:

```php
// index.php del módulo
add_action('hooma_init', function() {
    // Registra y agenda la tarea diaria
    Hooma::scheduler()->daily(
        'mi_modulo.cleanup_logs',
        array(\HoomaModules\MiModulo\Jobs\LogCleanupJob::class, 'cleanOldLogs')
    );
});
```
*Nota: Hooma Scheduler validará automáticamente si la tarea ya está programada en base de datos para no registrarla repetidamente en cada recarga de página.*
