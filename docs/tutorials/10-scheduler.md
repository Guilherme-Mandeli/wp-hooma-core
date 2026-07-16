# Tutorial Capítulo 10: Tareas Programadas

*Disponible desde v1.0*

En este capítulo aprenderemos a programar procesos periódicos en segundo plano (WP-Cron) utilizando firmas serializables y seguras con el servicio `Scheduler` de Hooma Core.

---

## 1. El Job Serializable

Dado que las tareas programadas se ejecutan de forma asíncrona en peticiones independientes, el callback que se dispare debe ser persistente (métodos de clase o estáticos).

Crea la clase `src/Jobs/CleanupJob.php` en tu módulo:

```php
<?php

namespace HoomaModules\MiPrimerModulo\Jobs;

use Hooma;

class CleanupJob
{
    /**
     * Método estático ejecutor de la tarea.
     */
    public static function run()
    {
        // Registrar el inicio de la tarea
        Hooma::logger()->info('Iniciando proceso de limpieza diaria de la base de datos...');

        $db = Hooma::database();
        $tabla = $db->prefix() . 'hooma_primer_modulo';

        // Borrar registros antiguos (por ejemplo)
        $db->query("DELETE FROM `{$tabla}` WHERE id < 10");

        Hooma::logger()->info('Limpieza diaria completada con éxito.');
    }
}
```

---

## 2. Programar la Tarea Diaria

En el archivo de entrada `index.php` de tu módulo, registra y agenda la tarea diaria utilizando el atajo `daily()` o el método principal `schedule()`:

```php
// En index.php de tu módulo
add_action('hooma_init', function() {
    // Programar CleanupJob::run para ejecutarse diariamente
    Hooma::scheduler()->daily(
        'mi_primer_modulo.clean',
        array(\HoomaModules\MiPrimerModulo\Jobs\CleanupJob::class, 'run')
    );
});
```

*Nota: Al usar `array(Clase::class, 'metodo')`, Hooma Scheduler valida la persistencia y almacena la tarea en la base de datos de WordPress sin riesgos de fallos de serialización.*

---

Siguiente capítulo: **[Capítulo 11: Auditoría y Buenas Prácticas](11-best-practices.md)**
