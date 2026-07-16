# Tutorial Capítulo 09: Eventos del Sistema

*Disponible desde v1.0*

En este capítulo aprenderemos a desacoplar la lógica de nuestra aplicación publicando y suscribiéndonos a eventos semánticos mediante el servicio `Events`.

---

## 1. Publicar un Evento (Despachar)

Cuando tu módulo complete una acción de negocio importante (por ejemplo, registrar una configuración o procesar datos), debes comunicarlo al resto del ecosistema disparando un evento:

```php
// En tu controlador al guardar datos
$result = Hooma::events()->dispatch('mi_primer_modulo.config_updated', array(
    'time' => time(),
    'user' => Hooma::auth()->user_id()
));

// Depurar si algún listener escuchó el evento
Hooma::logger()->info('Evento de actualización lanzado. Reacciones: ' . $result->listeners_executed());
```

---

## 2. Suscribirse a un Evento (Escuchar)

Cualquier módulo del sistema (incluyendo el tuyo) puede suscribirse de forma aislada a este evento desde su bootstrap en `index.php`:

```php
// En index.php del módulo receptor
Hooma::events()->listen('mi_primer_modulo.config_updated', function($payload) {
    // Reaccionar al evento escribiendo un log de auditoría
    Hooma::logger()->notice('Auditoría: Ajustes actualizados por usuario #' . $payload['user']);
}, 10, 1);
```

---

## 3. Filtrar y Mutar Datos

Los filtros permiten que otros módulos modifiquen variables internas antes de ser procesadas:

```php
// Modificar un texto antes de imprimirlo
$mensaje = 'Hola';
$mensaje_filtrado = Hooma::events()->filter('mi_primer_modulo.welcome_message', $mensaje);
```

Y otro módulo puede suscribirse para editar el string:

```php
Hooma::events()->listen('mi_primer_modulo.welcome_message', function($mensaje) {
    return $mensaje . ' ¡y bienvenido a Hooma!';
});
```

---

Siguiente capítulo: **[Capítulo 10: Tareas Programadas](10-scheduler.md)**
