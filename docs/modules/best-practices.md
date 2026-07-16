# Buenas Prácticas (Best Practices)

*Disponible desde v0.1*

Para mantener la robustez, escalabilidad y compatibilidad del ecosistema de Hooma, todos los desarrolladores de módulos deben adherirse a las siguientes directrices y mejores prácticas oficiales:

---

## 1. Reglas de Aislamiento y Desacoplamiento

### ✔ Utiliza siempre los Servicios de Hooma Core
Los servicios (`Hooma::cache()`, `Hooma::http()`, `Hooma::database()`, etc.) actúan como capas protectoras frente a los fallos nativos de WordPress. Úsalos siempre en lugar de las llamadas directas de WordPress.

### ✔ Utiliza Eventos para la Comunicación entre Módulos
Nunca instancies clases ni uses namespaces de otros módulos de forma cruzada. Si necesitas notificar que un pago se ha completado, dispara un evento:
```php
Hooma::events()->dispatch('payment.completed', $payment_id);
```
Y deja que los módulos interesados (ej. Facturación o Alertas) escuchen el evento de forma independiente.

### ✔ Mapea Permisos de Negocio
No utilices capacidades genéricas de WordPress (`edit_posts`) en tu código del controlador. Mapea un permiso semántico (ej. `booking.edit`) y utiliza el servicio de autorización:
```php
if (!Hooma::auth()->can('booking.edit')) {
    wp_die('No autorizado.');
}
```

---

## 2. Organización y Estructura del Código

### ✔ Mantén una Única Responsabilidad (SRP)
Tus archivos de entrada `index.php` deben limitarse al registro de hooks, enrutamiento y bootstrap. Delega la lógica de negocio a controladores y la consulta de base de datos a modelos independientes bajo la carpeta `src/`.

### ✔ No utilices `require_once` de forma dispersa
El cargador automático PSR-4 resuelve las dependencias de clases a demanda de forma eficiente. Usar `require_once` de forma repetitiva anula el cargador automático, ralentiza PHP e incrementa el consumo de memoria.

### ✔ No escribas HTML en tus Clases o Controladores
Separa los archivos de interfaz. Mantén tus plantillas y trozos de HTML dentro de la carpeta `views/` e inclúyelos únicamente en los métodos de renderizado de tus controladores.

---

## 3. Seguridad y Persistencia

### ✔ Sanitiza los Parámetros de Consulta SQL
Aunque uses el servicio de Base de Datos, utiliza placeholders (`%s`, `%d`) para pasar argumentos. El motor preparará de forma segura el query previniendo inyección SQL.

### ✔ Valida la Serialización del Scheduler
Cuando programes tareas de fondo, asegúrate de pasar firmas de métodos estáticos o de clase (ej. `array(MyJob::class, 'execute')`). Recuerda que WP-Cron es asíncrono y los closures anónimos lanzarán excepciones de error al no poder almacenarse en la base de datos de WordPress.
