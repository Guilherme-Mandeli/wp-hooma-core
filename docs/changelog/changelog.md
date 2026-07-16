# Registro de Cambios (Changelog)

*Disponible desde v0.1*

Este documento registra cronológicamente las versiones oficiales e incorporaciones de infraestructura en Hooma Core.

---

## v1.0.0 (Fase 3 Finalizada)
*Fecha: 15 de Julio de 2026*

### Añadido
- **Events Service**: Bus de eventos y filtros desacoplados con conteo de suscriptores y tipado `EventResult`.
- **Scheduler Service**: Abstracción e inyección de tareas asíncronas en WP-Cron con validación estricta para evitar closures no serializables.
- **Manual Técnico Oficial**: Reestructuración y redacción completa de la documentación oficial.

---

## v0.3.0
*Fecha: 15 de Julio de 2026*

### Añadido
- **Config Service**: Servicio centralizado de constantes y rutas.
- **Cache Service**: Motor de transient caching con hashing SHA-1 contra límites de base de datos de WordPress.
- **HTTP Service**: Cliente de red con objeto unificado de respuesta `HttpResponse` y conversión de errores a excepciones nativas.
- **Database Service**: Wrapper seguro sobre `$wpdb` con preparación automática de placeholders SQL.
- **Filesystem Service**: Manipulación aislada de archivos mediante la API `WP_Filesystem`.
- **Notices Service**: Cola de notificaciones administrativas desacoplada de la salida HTML.
- **Auth Service**: Motor de verificación de permisos basado en mapas y filtros.

---

## v0.2.0 (Fase 2 de Arquitectura)
*Fecha: 10 de Julio de 2026*

### Añadido
- **Service Container**: Motor de registro lazy singleton.
- **Fachada Hooma**: Clase global y unificada para acceso simplificado.
- **Settings Service**: Gestión agrupada de configuraciones locales.
- **Logger Service**: Motor diario de logs con protección `.htaccess`.
- **Assets Service**: Encolador de estilos y scripts con control automático de caché.
- **Modules Service**: Descubrimiento y activación de módulos de negocio.

---

## v0.1.0 (Fase 1 Inicial)
*Fecha: 1 de Julio de 2026*

### Añadido
- **Autocargador**: Autocarga nativa PSR-4.
- **Module Loader**: Escaneo básico de metadatos de módulos.
- **UI Kit**: Componentes de layout (Header, Tabs, Containers, Footer).
