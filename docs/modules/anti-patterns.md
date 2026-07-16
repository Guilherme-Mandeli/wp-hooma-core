# Anti-Patrones (Anti-Patterns)

*Disponible desde v0.1*

Esta sección recoge los errores y malas prácticas de desarrollo más comunes en WordPress, contrastándolos con la solución limpia y recomendada de Hooma Core.

---

## 1. Guardar configuraciones y opciones

### ❌ Forma Incorrecta
Llamar a funciones globales que guardan datos de forma dispersa sin namespacing ni control de caché:
```php
// ¡Mala práctica! Llena wp_options de basura e incrementa el consumo de base de datos
update_option('booking_currency_value', 'EUR');
$currency = get_option('booking_currency_value');
```

### ✔ Forma Correcta (Hooma Core)
Utiliza el servicio de settings que unifica, aísla y optimiza las configuraciones bajo el namespace del módulo:
```php
// Registra y encapsula la opción de forma automática
Hooma::settings()->set('booking.currency', 'EUR');
$currency = Hooma::settings()->get('booking.currency');
```

---

## 2. Consumo de recursos de red (HTTP)

### ❌ Forma Incorrecta
Usar la función global de WordPress y procesar manualmente el control de errores `WP_Error`:
```php
// ¡Engorroso! Tienes que controlar el tipo WP_Error manualmente
$response = wp_remote_get('https://api.test.com');
if (is_wp_error($response)) {
    error_log($response->get_error_message());
} else {
    $body = json_decode(wp_remote_retrieve_body($response), true);
}
```

### ✔ Forma Correcta (Hooma Core)
Utiliza el cliente HTTP del Core, que lanza excepciones nativas y devuelve un objeto de respuesta unificado:
```php
try {
    $response = Hooma::http()->get('https://api.test.com');
    $body = $response->json();
} catch (\RuntimeException $e) {
    Hooma::logger()->error('Error de red: ' . $e->getMessage());
}
```

---

## 3. Registro de eventos de depuración (Logs)

### ❌ Forma Incorrecta
Escribir en el archivo global `debug.log` de WordPress mezclando mensajes de todos los plugins instalados:
```php
// ¡Incontrolable! Difícil de filtrar en producción
error_log('Error crítico al procesar la reserva #' . $booking_id);
```

### ✔ Forma Correcta (Hooma Core)
Utiliza el logger oficial. Agrupa tus logs por canal de negocio y genera archivos diarios con rotación protegidos por `.htaccess`:
```php
// Se escribe directamente en wp-content/uploads/hooma/logs/mi-modulo-YYYY-MM-DD.log
Hooma::logger()->error('Error crítico al procesar la reserva #' . $booking_id);
```

---

## 4. Consultas a la Base de Datos

### ❌ Forma Incorrecta
Usar concatenaciones de texto en queries sobre la base de datos globales sin escape automático:
```php
global $wpdb;
// ¡Peligroso! Vulnerable a SQL Injection
$user_id = $_GET['user_id'];
$result = $wpdb->get_results("SELECT * FROM wp_users WHERE ID = " . $user_id);
```

### ✔ Forma Correcta (Hooma Core)
Utiliza el servicio de base de datos pasando un array de argumentos para su preparación automática:
```php
$user_id = (int) $_GET['user_id'];
// Hooma se encarga de escapar y preparar el query de forma transparente
$result = Hooma::database()->get_results(
    "SELECT * FROM %swp_users WHERE ID = %d",
    array($user_id)
);
```
