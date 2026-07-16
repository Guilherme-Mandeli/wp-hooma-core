# Tutorial Capítulo 08: Base de Datos

*Disponible desde v0.3*

En este capítulo aprenderemos a interactuar con la base de datos de WordPress ejecutando consultas SQL sanitizadas mediante el servicio `Database` de Hooma Core.

---

## 1. Crear una Tabla Personalizada al Activar el Módulo

WordPress permite ejecutar código específico cuando se activa un plugin, pero dado que los módulos de Hooma se cargan dinámicamente, podemos realizar la verificación de tablas de base de datos durante el arranque del módulo:

```php
// En index.php de tu módulo
add_action('hooma_module_activated_mi-primer-modulo', function() {
    $db = Hooma::database();
    $tabla = $db->prefix() . 'hooma_primer_modulo';

    // Crear la tabla si no existe
    $sql = "CREATE TABLE IF NOT EXISTS `{$tabla}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `clave` varchar(50) NOT NULL,
        `valor` text NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $db->query($sql);
});
```

---

## 2. Insertar y Leer Registros de forma segura

Vamos a crear un modelo `src/Models/ConfigData.php` para encapsular las llamadas a la base de datos:

```php
<?php

namespace HoomaModules\MiPrimerModulo\Models;

use Hooma;

class ConfigData
{
    protected $table;

    public function __construct()
    {
        $this->table = Hooma::database()->prefix() . 'hooma_primer_modulo';
    }

    public function addEntry(string $key, string $val): bool
    {
        // Inserción directa segura
        return (bool) Hooma::database()->insert($this->table, array(
            'clave' => $key,
            'valor' => $val
        ));
    }

    public function getValue(string $key): string
    {
        // Consulta segura con placeholders
        $sql = "SELECT valor FROM {$this->table} WHERE clave = %s LIMIT 1";
        return (string) Hooma::database()->get_var($sql, array($key));
    }
}
```

---

Siguiente capítulo: **[Capítulo 09: Eventos del Sistema](09-events.md)**
