# Cookbook: Intercomunicación por Eventos y Filtros

*Disponible desde v1.0*

Esta receta proporciona un ejemplo completo de desacoplamiento modular. Muestra cómo un módulo de facturación se conecta al módulo de reservas escuchando y modificando datos únicamente a través de eventos y filtros globales de Hooma Core.

---

## Módulo A: Gestión de Reservas (Emisor)

El módulo de reservas calcula el precio y despacha eventos cuando ocurren cambios significativos:

```php
// src/Controllers/BookingController.php
namespace HoomaModules\Reservas\Controllers;

use Hooma;

class BookingController
{
    public function procesarReserva(int $booking_id, float $precio_original)
    {
        // 1. Permitir que otros módulos muten el precio (Filtro)
        $precio_final = Hooma::events()->filter('reservas.calcular_precio', $precio_original, $booking_id);

        // 2. Guardar en base de datos
        // ...

        // 3. Notificar que la reserva se ha confirmado (Acción)
        Hooma::events()->dispatch('reservas.confirmada', array(
            'id'    => $booking_id,
            'total' => $precio_final
        ));
    }
}
```

---

## Módulo B: Fidelización y Descuentos (Receptor y Modificador)

El módulo de fidelización se suscribe a los eventos desde su bootstrap en `index.php`:

```php
// En index.php del Módulo B

// 1. Interceptar y aplicar un descuento al precio final (Filtro)
Hooma::events()->listen('reservas.calcular_precio', function($precio, $booking_id) {
    // Si el cliente es recurrente, aplica 15% de descuento
    if (cliente_es_premium($booking_id)) {
        return $precio * 0.85;
    }
    return $precio;
}, 10, 2);

// 2. Reaccionar al despacho de la reserva confirmada (Acción)
Hooma::events()->listen('reservas.confirmada', function($data) {
    // Generar puntos de fidelidad para el usuario
    sumar_puntos_fidelidad($data['id'], $data['total']);
    
    Hooma::logger()->info("Puntos acumulados para la reserva #" . $data['id']);
}, 10, 1);
```
