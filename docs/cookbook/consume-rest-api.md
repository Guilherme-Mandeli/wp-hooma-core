# Cookbook: Consumir API REST externa con Caché

*Disponible desde v0.3*

Esta receta muestra la solución completa para consumir información desde un servicio REST externo, gestionar los posibles fallos de red con excepciones y optimizar el tiempo de respuesta mediante almacenamiento en caché local.

---

## Solución Completa

```php
<?php

namespace HoomaModules\MiModulo\Services;

use Hooma;

class CambioDivisas
{
    /**
     * Obtiene la tasa de conversión guardándola en caché por 4 horas.
     */
    public function obtenerTasa(string $desde, string $hacia): float
    {
        $cache_key = "divisas.tasa_{$desde}_{$hacia}";

        // Almacenar el resultado en caché por 4 horas (14400 segundos)
        return (float) Hooma::cache()->remember($cache_key, 14400, function() use ($desde, $hacia) {
            $url = 'https://api.exchangerate.host/convert';
            
            try {
                // Realizar petición GET externa
                $response = Hooma::http()->get($url, array(
                    'body' => array(
                        'from' => $desde,
                        'to'   => $hacia
                    )
                ));

                if ($response->successful()) {
                    $json = $response->json();
                    return (float) ($json['result'] ?? 1.0);
                }
                
                // Si la API responde con un código de error (ej. 403, 500)
                Hooma::logger()->error("API de divisas falló con estado " . $response->status());

            } catch (\RuntimeException $e) {
                // Captura fallos de DNS, timeout o host inalcanzable
                Hooma::logger()->critical("Fallo de red al conectar con divisas: " . $e->getMessage());
            }

            // Fallback por defecto si la petición falla
            return 1.0;
        });
    }
}
```
