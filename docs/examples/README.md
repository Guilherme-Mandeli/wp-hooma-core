# Módulos de Ejemplo (Code Examples)

*Disponible desde v0.1*

Esta sección contiene módulos de ejemplo completos y auto-contenidos, diseñados como plantillas estáticas de estudio. Puedes usarlos como punto de partida para clonar y estructurar tus propios módulos de negocio.

---

## Listado de Ejemplos Disponibles

### 1. **[Hello World](hello-world/index.php)**
El módulo más sencillo posible. Muestra cómo registrar metadatos de descubrimiento, crear un menú administrativo de WordPress y maquetar una pantalla básica utilizando el UI Kit.

### 2. **[Settings & Logs Demo](settings-demo/index.php)**
Muestra cómo utilizar el servicio `Settings` para guardar configuraciones de forma agrupada en base de datos, escribir registros en el archivo diario de `Logger` y encolar avisos administrativos con `Notices`.

### 3. **[CRUD Database Demo](crud-database-demo/index.php)**
Demuestra cómo estructurar la base de datos de tu módulo: creación automatizada de tablas en la activación (`hooma_module_activated`), operaciones seguras de inserción/borrado de registros y renderizado de listados usando tablas estilizadas.

### 4. **[API Sync & Cache Demo](api-sync-demo/index.php)**
Muestra cómo realizar peticiones HTTP externas de red de forma segura, controlar fallos de timeout mediante excepciones y optimizar las cargas guardando las respuestas en el servicio `Cache` de forma transparente.

### 5. **[Decoupled Events Demo](decoupled-events-demo/index.php)**
Muestra el uso práctico de la comunicación mediante eventos (`dispatch` y `listen`) y filtros mutadores de datos (`filter`) para intercomunicar módulos independientes con acoplamiento cero.

### 6. **[Cron Scheduler Demo](cron-scheduler-demo/index.php)**
Muestra la programación de rutinas diarias asíncronas con `SchedulerService` utilizando firmas estáticas serializables que previenen problemas de persistencia en WP-Cron, además de un panel para monitorear el estado del cron.

### 7. **[Frontend Assets Demo](frontend-assets-demo/index.php)**
Demuestra cómo registrar y encolar scripts (JS) y estilos (CSS) propios de un módulo de forma exclusiva en el frontend utilizando el servicio `Assets` al invocar un Shortcode personalizado de WordPress.
