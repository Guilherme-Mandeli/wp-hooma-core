# Tutorial Capítulo 06: Peticiones HTTP

*Disponible desde v0.3*

En este capítulo aprenderemos a consumir servicios y APIs REST remotas externas utilizando el servicio `HTTP` de Hooma Core.

---

## 1. Realizar una petición remota GET

Vamos a recuperar información de un repositorio de GitHub utilizando el servicio HTTP.

Agrega un método en tu controlador `src/Controllers/WelcomeController.php` para realizar la petición:

```php
<?php

namespace HoomaModules\MiPrimerModulo\Controllers;

use Hooma;

class WelcomeController
{
    public function getRepoStars(string $repo): int
    {
        $url = 'https://api.github.com/repos/' . $repo;

        try {
            // Realizar la petición GET
            $response = Hooma::http()->get($url, array(
                'headers' => array(
                    'User-Agent' => 'Hooma Core Client'
                )
            ));

            if ($response->successful()) {
                $data = $response->json();
                return (int) ($data['stargazers_count'] ?? 0);
            }
        } catch (\RuntimeException $e) {
            // El servicio HTTP captura fallos DNS/Timeout y lanza excepciones
            Hooma::logger()->error('Error de red al conectar con Github: ' . $e->getMessage());
        }

        return 0;
    }
}
```

---

## 2. Mostrar la información en la vista

Puedes invocar el método del controlador desde tu vista `views/admin-page.php` para mostrar la información dinámicamente:

```php
<?php
// views/admin-page.php
$controller = new \HoomaModules\MiPrimerModulo\Controllers\WelcomeController();
$stars = $controller->getRepoStars('wordpress/wordpress');
?>
<div class="hooma-ui-wrapper">
    <div class="hooma-ui-container">
        <h3>Métricas de Repositorio</h3>
        <p>Estrellas en WordPress Core (GitHub): <strong><?php echo $stars; ?> ⭐</strong></p>
    </div>
</div>
```

---

Siguiente capítulo: **[Capítulo 07: Optimizar con Caché](07-cache.md)**
