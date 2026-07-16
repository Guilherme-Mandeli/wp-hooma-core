# Guía de Contexto e Índice de Documentación para Inteligencias Artificiales (AI.md)

Este documento sirve como entrypoint de contexto y mapa de navegación para cualquier Inteligencia Artificial (IA) o agente de programación que trabaje en el repositorio de **Hooma Core**.

---

## 1. Contexto del Proyecto

**Hooma Core** es un Framework / Service Container en forma de plugin de WordPress que centraliza y organiza la lógica de negocios del ecosistema de la empresa.

### Filosofías de Arquitectura Clave:
1. **Thin Core (Core Magro)**: El plugin Core contiene el mínimo código posible. Sus responsabilidades se limitan a:
   - Registrar la UI base de administración.
   - Proveer los servicios del contenedor (`Hooma::...`).
   - Escanear, validar y cargar módulos activos.
   - Las reglas de negocio residen estrictamente en **Módulos Independientes** y **Packages**.
2. **Aislamiento (Sandboxing)**: Los módulos y paquetes de negocio **no** se ubican en `wp-content/plugins/`. Se ubican en:
   - **Módulos**: `wp-content/hooma/modules/` (o el fallback antiguo `wp-content/hooma-modules/`).
   - **Packages**: `wp-content/hooma/packages/`.
3. **Autoloading Estricto**:
   - Namespace Raíz: `HoomaModules\`
   - Convención PSR-4: `HoomaModules\{NomeDoModulo}\{Classe}` se mapea automáticamente al archivo físico:
     `wp-content/hooma/modules/{nome-do-modulo}/includes/{Classe}.php`
4. **Coding Standards**:
   - **Código (variables, funciones, clases, comentarios en docstrings)**: Estrictamente en **Inglés**.
   - **Comentarios narrativos explicativos**: En **Portugués** (explicando el *"por qué"* de las soluciones complejas).

---

## 2. Acceso a Servicios del Core

Todos los servicios provistos por Hooma Core se acceden a través de la fachada estática central `Hooma` ([Hooma.php](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/includes/Hooma.php)).

| Servicio | Acceso en Fachada | Propósito / Responsabilidad |
| :--- | :--- | :--- |
| **Database** | `Hooma::db()` | Consultas seguras, migraciones y queries cacheadas en la DB. |
| **HTTP Client** | `Hooma::http()` | Consumir APIs REST externas y registrar endpoints de forma segura. |
| **Cache** | `Hooma::cache()` | Almacenamiento rápido en memoria (transients / object cache). |
| **Assets** | `Hooma::assets()` | Encolado y registro de scripts (JS) y estilos (CSS) en admin/front. |
| **Scheduler** | `Hooma::scheduler()` | Registro y ejecución de tareas programadas (Cron jobs). |
| **Events** | `Hooma::events()` | Sistema interno de eventos de tipo Pub/Sub (Publish/Subscribe). |
| **Settings** | `Hooma::settings()` | Gestión centralizada de ajustes y opciones en base de datos. |
| **Logger** | `Hooma::logger()` | Registro estructurado de trazas y depuración en archivos locales. |
| **Filesystem** | `Hooma::fs()` | Manipulación segura de archivos y subidas de ficheros al servidor. |
| **Modules** | `Hooma::modules()` | Servicio interno para descubrir y administrar módulos activos. |
| **Packages** | `Hooma::packages()` | Servicio interno para consultar librerías externas o assets compilados. |
| **Auth** | `Hooma::auth()` | Validación de permisos, roles y tokens de seguridad. |
| **Notices** | `Hooma::notices()` | Mostrar banners y avisos administrativos flotantes. |

---

## 3. Índice de Documentación por Casos de Uso (AI Directory Map)

Si tienes que programar o modificar una funcionalidad, utiliza esta tabla para encontrar rápidamente la documentación correcta dentro de la carpeta [docs/](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs):

### 🚀 Primeros Pasos y Conceptos
- **¿Cómo se inicializa el plugin y cuál es el ciclo de vida?** &rarr; [docs/architecture/bootstrap-lifecycle.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/architecture/bootstrap-lifecycle.md) y [docs/concepts/lifecycle.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/concepts/lifecycle.md)
- **¿Cómo funciona el Contenedor de Servicios?** &rarr; [docs/architecture/service-container.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/architecture/service-container.md)
- **Guía de instalación inicial** &rarr; [docs/tutorials/01-installation.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/tutorials/01-installation.md)
- **Preguntas frecuentes generales (FAQ)** &rarr; [docs/faq.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/faq.md)

### 📦 Desarrollo de Módulos
- **Estructura y reglas para crear tu primer módulo** &rarr; [docs/tutorials/02-first-module.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/tutorials/02-first-module.md) y [docs/modules/development-guide.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/modules/development-guide.md)
- **Buenas prácticas de diseño en módulos** &rarr; [docs/modules/best-practices.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/modules/best-practices.md)
- **Antipatrones críticos a evitar** &rarr; [docs/modules/anti-patterns.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/modules/anti-patterns.md)

### 🖥️ Interfaz de Usuario y WordPress Admin
- **Crear una página o menú en el WP Admin** &rarr; [docs/cookbook/admin-page.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/cookbook/admin-page.md) y [docs/tutorials/03-admin-page.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/tutorials/03-admin-page.md)
- **Uso de la UI Kit unificada (`Hooma_UI`)** &rarr; [docs/concepts/ui-kit.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/concepts/ui-kit.md)
- **Gestión de Assets (CSS/JS)** &rarr; [docs/tutorials/05-assets.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/tutorials/05-assets.md) y [docs/api/assets.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/api/assets.md)
- **Crear Shortcodes en WordPress** &rarr; [docs/cookbook/shortcodes.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/cookbook/shortcodes.md)

### 💾 Base de Datos y Caché
- **Uso del servicio Database** &rarr; [docs/tutorials/08-database.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/tutorials/08-database.md) y [docs/api/database.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/api/database.md)
- **Crear tablas personalizadas (custom tables)** &rarr; [docs/cookbook/custom-database-table.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/cookbook/custom-database-table.md)
- **Cómo cachear queries de forma óptima** &rarr; [docs/cookbook/cached-queries.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/cookbook/cached-queries.md) y [docs/tutorials/07-cache.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/tutorials/07-cache.md)

### 📡 Integración y Red
- **Registrar y consumir endpoints HTTP** &rarr; [docs/tutorials/06-http.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/tutorials/06-http.md) y [docs/api/http.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/api/http.md)
- **Consumo de APIs REST paso a paso** &rarr; [docs/cookbook/consume-rest-api.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/cookbook/consume-rest-api.md)

### ⏰ Eventos y Tareas Programadas
- **Sistema de Eventos (Pub/Sub)** &rarr; [docs/tutorials/09-events.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/tutorials/09-events.md) y [docs/cookbook/events.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/cookbook/events.md)
- **Scheduler (Tareas programadas - Cron)** &rarr; [docs/tutorials/10-scheduler.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/tutorials/10-scheduler.md) y [docs/cookbook/scheduled-tasks.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/cookbook/scheduled-tasks.md)

### 📦 Integración de Paquetes Externos (esbuild, Vue, Stripe)
- **Sistema de Packages de Hooma** &rarr; [docs/packages/packages-system.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/packages/packages-system.md)
- **Compilar assets con esbuild sin dependencias Node** &rarr; [docs/packages/esbuild.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/packages/esbuild.md)
- **Uso de Vue.js en WordPress a través de Hooma** &rarr; [docs/packages/vue.md](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Core/Hooma/docs/packages/vue.md)

---

## 4. Instrucciones para la IA (System Rules)

Al proponer cambios o redactar código en este repositorio:
1. **No agregues lógica de negocio al Core**: Si el usuario te pide implementar una regla de facturación, pasarela de pago o lógica específica, guíalo para crear o modificar un archivo bajo un módulo independiente en `wp-content/hooma/modules/{nombre-modulo}/`.
2. **Sigue las convenciones de namespaces**: Declara siempre namespaces que coincidan con la estructura física y no requieras archivos manualmente si la clase puede ser cargada por el autoloader inteligente.
3. **Utiliza la API unificada**: Prefiere usar las llamadas estáticas de `Hooma` (ej: `Hooma::db()`) en lugar de interactuar con la variable global `$wpdb` de WordPress directamente o realizar peticiones con `curl`/`file_get_contents` nativos (usa `Hooma::http()`).
4. **Respeta los idiomas de codificación**: Escribe el código en **Inglés** y añade comentarios de funcionamiento detallado en **Portugués**.
