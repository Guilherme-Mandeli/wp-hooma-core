# Cookbook: Cachear Consultas SQL Complejas

*Disponible desde v0.3*

Esta receta proporciona la estructura estándar para cachear consultas pesadas a la base de datos de WordPress reduciendo drásticamente la latencia de respuesta de los servidores de producción.

---

## Solución Completa

```php
<?php

namespace HoomaModules\MiModulo\Services;

use Hooma;

class DashboardMetrics
{
    /**
     * Obtiene el reporte de ventas totales agrupado por mes de forma cacheada.
     */
    public function obtenerReporteVentasAnual(int $year): array
    {
        $cache_key = "reportes.ventas_anual_{$year}";

        // Almacenar el resultado en caché por 2 horas (7200 segundos)
        return (array) Hooma::cache()->remember($cache_key, 7200, function() use ($year) {
            $db = Hooma::database();
            $prefix = $db->prefix();
            
            // Consulta SQL compleja
            $sql = "SELECT 
                        MONTH(fecha) as mes, 
                        SUM(monto) as total_ventas,
                        COUNT(id) as total_pedidos
                    FROM {$prefix}hooma_reservas 
                    WHERE YEAR(fecha) = %d AND estado = %s
                    GROUP BY MONTH(fecha)
                    ORDER BY mes ASC";

            return $db->get_results($sql, array($year, 'confirmado'));
        });
    }

    /**
     * Invalida o borra la caché cuando se procese un nuevo pago o cambio en base de datos.
     */
    public function invalidarCache(int $year)
    {
        $cache_key = "reportes.ventas_anual_{$year}";
        Hooma::cache()->delete($cache_key);
    }
}
```
