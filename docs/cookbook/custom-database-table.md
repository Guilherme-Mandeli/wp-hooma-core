# Cookbook: Crear y Actualizar una Tabla Personalizada

*Disponible desde v0.3*

Esta receta muestra la solución completa para crear, actualizar y mantener una tabla personalizada en la base de datos de WordPress utilizando las herramientas integradas de Hooma Core.

---

## El Problema
Tu módulo necesita almacenar información en una tabla SQL dedicada (ej. para almacenar registros de reservas o transacciones) y debe asegurarse de que la tabla se cree o se actualice estructuralmente al activarse o iniciarse el módulo.

---

## Solución Completa

### 1. El Script de la Estructura (en `index.php`)

Nos suscribimos al evento de activación del módulo para disparar la creación de la tabla:

```php
// index.php
add_action('hooma_module_activated_mi-modulo', function() {
    $db = Hooma::database();
    $prefix = $db->prefix();
    $tabla = $prefix . 'hooma_reservas';

    // Para crear o actualizar la estructura de la tabla de forma segura,
    // es recomendable usar la función dbDelta de WordPress
    $sql = "CREATE TABLE `{$tabla}` (
        `id` bigint(20) NOT NULL AUTO_INCREMENT,
        `cliente_id` bigint(20) NOT NULL,
        `fecha` datetime NOT NULL,
        `monto` decimal(10,2) NOT NULL,
        `estado` varchar(20) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
});
```

---

## Inserción y Lectura Segura de Datos

A continuación se muestra cómo escribir un modelo controlador que manipule esta tabla sanitizando las entradas:

```php
<?php

namespace HoomaModules\MiModulo\Models;

use Hooma;

class ReservaModel
{
    protected $table;

    public function __construct()
    {
        $this->table = Hooma::database()->prefix() . 'hooma_reservas';
    }

    /**
     * Inserta una nueva reserva de forma segura.
     */
    public function crear(int $cliente, float $monto): bool
    {
        return (bool) Hooma::database()->insert($this->table, array(
            'cliente_id' => $cliente,
            'fecha'      => current_time('mysql'),
            'monto'      => $monto,
            'estado'     => 'pendiente'
        ));
    }

    /**
     * Consulta registros aplicando filtros sanitizados.
     */
    public function obtenerConfirmadosPorCliente(int $cliente): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE cliente_id = %d AND estado = %s";
        return Hooma::database()->get_results($sql, array($cliente, 'confirmado'));
    }
}
```
