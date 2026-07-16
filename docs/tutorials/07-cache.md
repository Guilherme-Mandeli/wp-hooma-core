# Tutorial Capítulo 07: Optimizar con Caché

*Disponible desde v0.3*

En este capítulo aprenderemos a acelerar los tiempos de carga y evitar peticiones externas redundantes almacenando en caché los resultados de red de forma segura.

---

## 1. El problema del consumo repetitivo de APIs

En el capítulo anterior, cada vez que el usuario cargaba la página de administración realizábamos una petición HTTP a la API de GitHub. Esto puede provocar:
1.  **Lentitud**: Retardo en el renderizado de la página esperando la respuesta remota.
2.  **Límites de cuotas**: Bloqueos del servidor remoto por peticiones concurrentes (Rate Limit).

---

## 2. Optimización usando `Hooma::cache()->remember()`

El método `remember()` simplifica el flujo completo: busca en caché, si existe lo devuelve, y si no, ejecuta el callback para guardarlo por el tiempo (TTL) indicado en segundos.

Actualiza tu controlador `src/Controllers/WelcomeController.php` para incorporar la optimización:

```php
<?php

namespace HoomaModules\MiPrimerModulo\Controllers;

use Hooma;

class WelcomeController
{
    public function getRepoStars(string $repo): int
    {
        $cache_key = 'mi_modulo.repo_stars_' . str_replace('/', '_', $repo);

        // Almacenar el resultado en caché por 1 hora (3600 segundos)
        return (int) Hooma::cache()->remember($cache_key, 3600, function() use ($repo) {
            $url = 'https://api.github.com/repos/' . $repo;

            try {
                $response = Hooma::http()->get($url, array(
                    'headers' => array('User-Agent' => 'Hooma Core Client')
                ));

                if ($response->successful()) {
                    $data = $response->json();
                    return (int) ($data['stargazers_count'] ?? 0);
                }
            } catch (\RuntimeException $e) {
                Hooma::logger()->error('Fallo HTTP en caché: ' . $e->getMessage());
            }

            return 0;
        });
    }
}
```

*Nota: La primera petición seguirá tardando, pero las siguientes cargas serán instantáneas al leer los datos directamente del almacenamiento en memoria caché.*

---

Siguiente capítulo: **[Capítulo 08: Base de Datos](08-database.md)**
