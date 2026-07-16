# Manual Oficial de Hooma Core: Preguntas Frecuentes (FAQ)

*Disponible desde v0.1*

Esta sección recopila y responde de forma exhaustiva a las dudas conceptuales, operacionales y de diseño más recurrentes al trabajar con Hooma Core.

---

## La Pregunta Clave

### ¿Cuándo debo utilizar la API nativa de WordPress y cuándo debo utilizar un Service de Hooma?
Esta es la decisión fundamental que define el desarrollo consistente en el ecosistema. La regla de oro es simple:

1.  **Si Hooma ofrece un Service para esa funcionalidad, úsalo.** No recurras a funciones nativas ni implementes soluciones personalizadas para interactuar con la caché, realizar peticiones de red, registrar ajustes, emitir logs o agendar tareas cron.
2.  **Si Hooma no ofrece un Service, utiliza la API nativa de WordPress.** Hooma no pretende duplicar ni ocultar las capacidades centrales de WordPress (como las consultas de contenido mediante `WP_Query`, los sistemas de sanitización/escapado o el renderizado de taxonomías).
3.  **Nunca implementes una solución propia para algo que el Core ya abstrae.** Esto evita la fragmentación del código y garantiza que tu módulo se beneficie automáticamente de las optimizaciones, medidas de seguridad y mejoras que se añadan al framework Core en el futuro.

---

## Filosofía

### ¿Por qué Hooma utiliza un Thin Core?
Para garantizar el máximo rendimiento y la mínima sobrecarga en el servidor. WordPress es utilizado para múltiples propósitos (SaaS, e-commerce, blogs, APIs). Un núcleo sobredimensionado ("Fat Core") cargaría utilidades pesadas en memoria en cada recarga de página, afectando negativamente a la latencia global. El enfoque Thin Core asegura que el framework solo provea la infraestructura mínima esencial (autocargador, contenedor y ciclo de vida de los módulos).
*   **Archivo relacionado:** [hooma.php](../hooma.php)

### ¿Por qué no existe un ORM integrado en Hooma?
Escribir y mantener un mapeador objeto-relacional (ORM) propio requiere un esfuerzo de mantenimiento masivo a lo largo de los años y añade una alta sobrecarga de CPU y memoria en cada consulta. Hooma promueve el uso del servicio `Database` (un wrapper seguro sobre `$wpdb`) o la API nativa de WordPress (`WP_Query`), dejando la decisión de estructurar la hidratación de objetos en manos de las necesidades específicas de cada módulo.
*   **Archivo relacionado:** [DatabaseService.php](../services/Database/DatabaseService.php)

### ¿Por qué los módulos viven fuera del directorio `plugins/` de WordPress?
Los módulos se ubican físicamente en el directorio `uploads/hooma/modules/` para separarlos lógicamente de los plugins genéricos de terceros. Esto previene que el administrador del sitio desactive accidentalmente piezas críticas de negocio desde el panel de plugins general de WordPress y permite a Hooma Core gobernar centralizadamente su ciclo de activación, dependencias y orden de carga.
*   **Archivo relacionado:** [class-hooma-loader.php](../includes/class-hooma-loader.php)

### ¿Por qué Hooma abstrae parte de la API de WordPress?
Para resolver problemas recurrentes de seguridad, rendimiento y usabilidad de las APIs antiguas de WordPress:
*   **Seguridad**: El servicio `Database` reduce la posibilidad de inyecciones SQL preparando consultas de forma automática.
*   **Tipado e Interfases**: Centraliza las respuestas en objetos tipados (como `HttpResponse` o `EventResult`).
*   **Mantenibilidad**: Si en el futuro decidimos migrar el motor de logs o el almacenamiento de caché fuera de WordPress (ej. a Redis o ficheros externos), los módulos no requerirán cambios puesto que interactúan con la interfaz abstracta del servicio de Hooma.
*   **Archivo relacionado:** [Hooma.php](../includes/Hooma.php)

### ¿Por qué existen Services en lugar de Helpers estáticos?
Las clases helpers con métodos estáticos puros (ej. `Logger::info()`) acoplan rígidamente tu código y hacen que sea imposible realizar pruebas unitarias con "mocks" o sustituir la implementación del servicio en tiempo de ejecución. Los servicios de Hooma se resuelven a través de un `ServiceContainer` y se acceden mediante fachadas estáticas dinámicas, lo que permite desacoplamiento completo y testeabilidad.
*   **Archivo relacionado:** [ServiceContainer.php](../includes/ServiceContainer.php)

---

## Arquitectura

### ¿Qué es exactamente un módulo?
Un módulo es un paquete autocontenido de funcionalidad empresarial que vive dentro de la carpeta `uploads/hooma/modules/`. Debe constar como mínimo de un archivo de inicio (`index.php`) con cabeceras de metadatos declaradas. Los módulos respetan fronteras claras de negocio y se comunican únicamente con el Core y con otros módulos de forma asíncrona mediante el bus de eventos.
*   **Archivo relacionado:** [class-hooma-loader.php](../includes/class-hooma-loader.php)

### ¿En qué se diferencia un módulo de un plugin de WordPress?
Un plugin es gestionado y cargado directamente por el cargador de plugins nativo de WordPress. Un módulo es descubierto, validado y cargado perezosamente por el motor de carga de Hooma Core. Físicamente residen en diferentes rutas y los módulos no pueden iniciarse de forma independiente sin la presencia del framework Hooma Core.
*   **Archivo relacionado:** [hooma.php](../hooma.php)

### ¿Cuándo debo crear un módulo nuevo y cuándo ampliar uno existente?
*   **Crea un módulo nuevo** cuando la funcionalidad represente una frontera de negocio o dominio independiente (ej. "Pasarela de Pagos Stripe" vs "Motor de Reservas").
*   **Amplía un módulo existente** cuando el cambio requiera acceso directo e íntimo a las tablas internas, entidades o lógica ya definidas en dicho módulo, o cuando separar la funcionalidad complique excesivamente la consistencia de los datos.

### ¿Puede un módulo tener dependencias de otros módulos o plugins?
Sí. Puedes declarar dependencias explícitamente en las cabeceras del archivo principal `index.php` de tu módulo:
*   **De otros módulos**: `Requires Modules: modulo-a, modulo-b`
*   **De plugins de WordPress**: `Requires Plugins: woocommerce, contact-form-7`

El cargador de Hooma validará estas dependencias al intentar activar tu módulo. Si falta alguna, el proceso de activación fallará reportando un error detallado mediante el servicio de notificaciones.
*   **Archivo relacionado:** [class-hooma-installer.php](../includes/class-hooma-installer.php)

### ¿Qué ocurre si un módulo falla durante la carga? ¿Se desactiva o se rompe el sitio?
Si un módulo contiene un error fatal de sintaxis PHP (Parse Error), PHP detendrá la ejecución del hilo y el sitio web fallará con una pantalla de error (WSOD). Sin embargo, si el módulo implementa comprobaciones lógicas y retorna un objeto de error o excepción capturable durante su inicialización, Hooma Core evitará su carga, registrará el fallo en la bitácora (`Logger`) y continuará cargando los demás módulos activos de forma segura sin interrumpir el resto de la plataforma.
*   **Archivo relacionado:** [class-hooma-loader.php](../includes/class-hooma-loader.php)

### ¿Puedo cargar un módulo manualmente o siempre debe descubrirlo Hooma?
Siempre debe descubrirlo, registrarlo y cargarlo el motor de Hooma Core. La carga manual rompe el aislamiento del framework, desactiva el control de dependencias e invalida el ciclo de vida de los ganchos de inicialización.
*   **Archivo relacionado:** [ModulesService.php](../services/Modules/ModulesService.php)

---

## Services

### ¿Estoy obligado a utilizar todos los Services?
No. Puedes utilizar solo los servicios que tu lógica necesite. De la misma forma, eres totalmente libre de utilizar las funciones nativas de WordPress de visualización y formateo (como `esc_html()`, `esc_attr()`, `wp_kses()`) o de manipulación de contenidos (`WP_Query`), ya que el framework no duplica estas utilidades de presentación.

### ¿Cuándo debo crear un nuevo Service?
Debes proponer y crear un nuevo servicio en el Core cuando identifiques una necesidad de infraestructura común que vaya a ser reutilizada por múltiples módulos independientes (ej. un motor de exportación a PDF, un gestor de colas de correo electrónico o un conector a almacenamiento S3).

### ¿Cómo solicito o registro un nuevo Service en el Core?
Para registrar un nuevo servicio en el Core debes:
1.  Definir su interfaz (contrato) bajo `includes/contracts/`.
2.  Implementar la clase del servicio bajo `services/{Nombre}/`.
3.  Registrar el servicio perezosamente en el contenedor mediante `ServiceProvider::register()`.
4.  Exponer el acceso en la fachada global `Hooma.php`.
*   **Archivo relacionado:** [ServiceProvider.php](../includes/ServiceProvider.php)

---

## Events

### ¿Cómo nombro correctamente un evento? ¿Existe una convención oficial?
Sí. Hooma Core promueve la convención namespaced por puntos para prevenir colisiones entre módulos:
`{modulo}.{entidad}.{accion}`

*Ejemplos:*
*   `booking.order.created`
*   `booking.payment.completed`
*   `user.profile.updated`
*   **Archivo relacionado:** [EventsService.php](../services/Events/EventsService.php)

### ¿Qué ocurre si nadie escucha un evento que he despachado?
Nada. El despachador de eventos ejecutará el flujo, contabilizará cero listeners ejecutados y devolverá un objeto `EventResult` vacío de forma segura. No se producen excepciones ni pérdidas de rendimiento.
*   **Archivo relacionado:** [EventResult.php](../services/Events/EventResult.php)

### ¿Puedo escuchar eventos de otro módulo?
Sí. El bus de eventos de Hooma es global para todo el ecosistema. Cualquier módulo puede registrar un oidor (`Hooma::events()->listen()`) para interceptar y reaccionar a los eventos lanzados por cualquier otro módulo.
*   **Archivo relacionado:** [EventsService.php](../services/Events/EventsService.php)

### ¿Puedo disparar eventos desde un cron?
Sí. Es la práctica recomendada para flujos asíncronos. Puedes agendar una tarea con `SchedulerService` que invoque un método estático, y dentro de ese método, disparar un evento global con `Hooma::events()->dispatch()` para que múltiples módulos reaccionen al cron.

---

## Scheduler

### ¿Cuándo se registra realmente un cron en el sistema?
El registro se realiza al invocar `Hooma::scheduler()->daily(...)` o `hourly(...)` en el gancho `hooma_init`. El servicio comprueba automáticamente contra la base de datos de WordPress si la tarea con dicho identificador (handle) ya se encuentra agendada. Si ya existe, omite el registro físico en base de datos para no consumir recursos I/O redundantes.
*   **Archivo relacionado:** [SchedulerService.php](../services/Scheduler/SchedulerService.php)

### ¿Qué ocurre si el módulo está desactivado? ¿El cron desaparece?
Cuando desactivas un módulo, debes desprogramar explícitamente sus tareas utilizando el gancho de desactivación:
```php
add_action('hooma_module_deactivated_mi-modulo', function() {
    Hooma::scheduler()->unschedule('mi_modulo.mi_tarea');
});
```
Si olvidas desprogramarlo, WordPress intentará disparar la tarea cron en el futuro, pero al no encontrar la clase o el callback en memoria (porque el módulo está inactivo), la ejecución fallará de forma silenciosa sin afectar al resto del sistema.
*   **Archivo relacionado:** [SchedulerService.php](../services/Scheduler/SchedulerService.php)

### ¿Puedo ejecutar una tarea programada inmediatamente en lugar de esperar al cron?
Sí. Puedes invocar directamente el método del callback de forma síncrona en tu código (ej. `CleanupJob::execute()`) en cualquier momento de la petición para pruebas o ejecuciones manuales.

### ¿Cómo elimino correctamente una tarea programada?
Utilizando el método `unschedule()` del servicio `Scheduler`:
```php
Hooma::scheduler()->unschedule('mi_modulo.cleanup_task');
```
*   **Archivo relacionado:** [SchedulerService.php](../services/Scheduler/SchedulerService.php)

---

## Settings

### ¿Dónde se almacenan realmente las configuraciones de mi módulo?
Se guardan en la tabla `wp_options` de WordPress. El servicio `Settings` agrupa todas las opciones de tu módulo bajo una única opción serializada en base de datos (con la clave `hooma_s_{modulo_slug}`), reduciendo drásticamente el número de consultas SQL directas.
*   **Archivo relacionado:** [SettingsService.php](../services/Settings/SettingsService.php)

### ¿Cómo migro configuraciones entre versiones de mi módulo?
Debes suscribirte al evento de activación del módulo, comprobar la versión de la base de datos guardada en los ajustes y ejecutar tus funciones de migración secuencialmente:
```php
add_action('hooma_module_activated_mi-modulo', function() {
    $version_guardada = Hooma::settings()->get('db_version', '1.0.0');
    if (version_compare($version_guardada, '1.1.0', '<')) {
        // Ejecutar migración de datos
        Hooma::settings()->set('db_version', '1.1.0');
    }
});
```
*   **Archivo relacionado:** [SettingsService.php](../services/Settings/SettingsService.php)

### ¿Las configuraciones son privadas para mi módulo?
Lógicamente sí. El servicio `Settings` aísla tus claves de configuración. Sin embargo, dado que se almacenan en la tabla de opciones global de WordPress, cualquier otro módulo o plugin con acceso a base de datos podría leer la opción serializada si conoce el slug.

---

## Cache

### ¿Cuándo debería utilizar Cache?
Utiliza el servicio `Cache` para guardar resultados de operaciones lentas que no cambian con frecuencia durante la misma petición o a lo largo del día, tales como:
*   Respuestas de llamadas HTTP a APIs externas.
*   Consultas SQL complejas que involucren agregaciones y cálculos pesados.
*   Fragmentos de HTML renderizados que requieran procesamiento costoso (fragment caching).
*   **Archivo relacionado:** [CacheService.php](../services/Cache/CacheService.php)

### ¿Cómo invalido una caché de forma segura?
Utilizando el método `delete()` con la clave correspondiente en el instante en que ocurra una mutación en tus datos:
```php
// Al guardar una reserva, borramos su reporte cacheado
Hooma::cache()->delete('reporte_reservas_anual');
```
*   **Archivo relacionado:** [CacheService.php](../services/Cache/CacheService.php)

### ¿Qué ocurre si el servidor no dispone de un motor de Object Cache (Redis/Memcached)?
El servicio `Cache` se adaptará automáticamente. WordPress guardará los datos utilizando la tabla `wp_options` (transients) de forma transparente. Si el servidor dispone de Redis o Memcached configurados en WordPress, el almacenamiento se redirigirá a la memoria RAM de forma nativa sin que tengas que modificar una sola línea de código en tu módulo.

---

## HTTP

### ¿Qué ocurre cuando una petición HTTP de red falla?
El cliente de red de Hooma Core intercepta los fallos de DNS, conexiones rehusadas o timeouts y los eleva como excepciones nativas de PHP (`\RuntimeException`). Los códigos de estado HTTP de error (ej. 404, 500) no lanzan excepciones por defecto, sino que te permiten comprobarlos usando `$response->successful()` o `$response->status()`.
*   **Archivo relacionado:** [HttpService.php](../services/HTTP/HttpService.php)

### ¿Cómo establezco timeouts en mis peticiones HTTP?
Puedes pasar parámetros de configuración en el segundo argumento de tus llamadas de red:
```php
$response = Hooma::http()->get('https://api.com', array(
    'timeout' => 5 // Tiempo de espera en segundos
));
```

### ¿Cómo envío datos en formato JSON?
Utiliza la opción `json` en el cuerpo de la petición:
```php
$response = Hooma::http()->post('https://api.com', array(
    'headers' => array('Content-Type' => 'application/json'),
    'body'    => json_encode(array('item' => 'datos'))
));
```

### ¿Cómo envío archivos en peticiones multipart/form-data?
Puedes estructurar los archivos pasando sus rutas físicas en los argumentos de cuerpo (body) de tu petición:
```php
$response = Hooma::http()->post('https://api.com', array(
    'body' => array(
        'archivo' => fopen('/ruta/al/archivo.jpg', 'r')
    )
));
```

### ¿Cómo añado cabeceras HTTP personalizadas?
Pasando una matriz asociativa en la clave `headers`:
```php
$response = Hooma::http()->get('https://api.com', array(
    'headers' => array(
        'Authorization' => 'Bearer token_secreto',
        'Accept'        => 'application/json'
    )
));
```

---

## Database

### ¿Puedo crear tablas SQL dedicadas en la base de datos?
Sí. Es la práctica recomendada para entidades de negocio transaccionales del módulo que no encajen con la estructura de posts nativos de WordPress. Debes crearlas dentro del evento de activación de tu módulo utilizando la estructura estructurada con `dbDelta()`.
*   **Archivo relacionado:** [DatabaseService.php](../services/Database/DatabaseService.php)

### ¿Cómo debo nombrar mis tablas personalizadas?
Debes usar siempre el prefijo oficial de WordPress obtenido dinámicamente mediante `Hooma::database()->prefix()` y añadir tu namespace o slug del módulo para prevenir colisiones con otras tablas:
`{$prefix}hooma_{modulo_slug}_{tabla_name}`

*Ejemplo:*
`wp_hooma_reservas_clientes`

### ¿Cómo ejecuto migraciones estructurales de base de datos?
Utilizando `dbDelta()` de WordPress dentro del evento de activación de tu módulo. Esta función analiza la estructura física de la tabla existente y la altera de forma inteligente añadiendo nuevas columnas o modificando índices sin borrar los datos existentes.

### ¿Debo utilizar Custom Post Types (CPT) o tablas personalizadas de base de datos?
*   **Utiliza Custom Post Types (CPT)** si tu entidad requiere una interfaz visual nativa de WordPress para crearse y editarse (como entradas del blog), si hace un uso intensivo del sistema de metadatos (`postmeta`) y si requiere integrarse con plugins de SEO o constructores visuales.
*   **Utiliza Tablas Personalizadas** para datos relacionales puros, transacciones, registros de auditoría o entidades de alto volumen (ej. reservas, facturas, logs) que requieran consultas SQL optimizadas y no deban exponerse en el gestor de contenidos tradicional de WordPress.

---

## Assets

### ¿Dónde debo colocar los archivos CSS y JavaScript de mi módulo?
Deben colocarse en subcarpetas dedicadas dentro del directorio de tu módulo:
*   `mi-modulo/assets/css/`
*   `mi-modulo/assets/js/`
*   **Archivo relacionado:** [AssetsService.php](../services/Assets/AssetsService.php)

### ¿Los assets del módulo se cargan automáticamente en WordPress?
No. Para mantener el rendimiento, Hooma no carga assets automáticamente. Debes registrarlos y encolarlos explícitamente usando el servicio de Assets del framework:
```php
Hooma::assets()->enqueue_style('mi-estilo', 'assets/css/estilo.css');
```
*   **Archivo relacionado:** [AssetsService.php](../services/Assets/AssetsService.php)

### ¿Cómo cargo assets de mi módulo únicamente en una página concreta de administración?
Comprobando el identificador de la pantalla actual (`$current_screen`) en el gancho de encolamiento de WordPress:
```php
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'toplevel_page_hooma-modules') {
        Hooma::assets()->enqueue_script('mi-script-modulo', 'assets/js/admin.js');
    }
});
```

---

## Logger

### ¿Dónde se almacenan los logs generados por Hooma?
Se guardan físicamente en archivos diarios estructurados bajo la ruta:
`wp-content/uploads/hooma/logs/log-YYYY-MM-DD.log`

Esta carpeta está protegida por un archivo `.htaccess` generado automáticamente por el Core para bloquear cualquier intento de acceso web directo desde navegadores.
*   **Archivo relacionado:** [LoggerService.php](../services/Logger/LoggerService.php)

### ¿Debo registrar las excepciones en los logs o capturarlas?
Debes capturar las excepciones (`try/catch`) en tu lógica de negocio para ofrecer una experiencia limpia al usuario final (fallback) y, dentro del bloque `catch`, registrar el error técnico en el servicio `Logger` para la posterior auditoría del administrador del sitio.

### ¿Qué nivel de log debo utilizar en cada caso?
Hooma implementa el estándar PSR-3. Usa los niveles de acuerdo a su gravedad:
*   `debug`: Información detallada para depuración en desarrollo.
*   `info`: Eventos comunes (ej. "Reserva creada #123").
*   `warning`: Situaciones no deseadas pero que no interrumpen el flujo (ej. "Reintento de conexión HTTP exitoso").
*   `error`: Errores operacionales importantes que requieren atención (ej. "Fallo al insertar reserva en BD").
*   `critical`: Problemas graves de infraestructura (ej. "Servicio externo inaccesible o error de escritura en disco").

---

## UI Kit

### ¿Estoy obligado a utilizar el UI Kit oficial de Hooma?
No. Eres libre de escribir tu propio HTML y maquetar con clases CSS de WordPress tradicionales en tu frontend o administración. Sin embargo, se recomienda encarecidamente utilizar `Hooma_UI` para la estructura y secciones generales (headers, pestañas, containers) de tu panel administrativo para mantener una estética unificada y consistente en toda la plataforma.
*   **Archivo relacionado:** [class-hooma-ui.php](../includes/class-hooma-ui.php)

### ¿Puedo crear componentes visuales propios?
Sí. Puedes maquetar tus propios componentes de formulario, modales o widgets de forma aislada utilizando hojas de estilo de tu módulo.

### ¿Cómo personalizo el aspecto visual del UI Kit?
El UI Kit utiliza variables CSS nativas (CSS custom properties) para sus colores y espaciados. Puedes personalizar visualmente toda la interfaz sobrescribiendo dichas variables CSS en tu hoja de estilos:
```css
:root {
    --hooma-primary-color: #ff5722; /* Cambiar color primario */
}
```

---

## Desarrollo

### ¿Cómo organizo la arquitectura de un módulo de gran tamaño?
Se recomienda separar responsabilidades organizando tu código en subdirectorios lógicos:
*   `src/Controllers/` para gestores de peticiones e interacción con vistas.
*   `src/Models/` o `src/Repositories/` para lógica de base de datos.
*   `src/Jobs/` para callbacks ejecutados por tareas programadas.
*   `views/` para plantillas HTML de visualización limpias.

### ¿Cuál es el tamaño máximo recomendado para una clase?
Para mantener la legibilidad y cumplir el Principio de Responsabilidad Única, se recomienda que tus clases no superen las 300-400 líneas de código. Si una clase crece demasiado, considera delegar responsabilidades a clases auxiliares.

### ¿Puedo utilizar Traits en mis módulos de negocio?
Sí. El autoloader PSR-4 de Hooma Core cargará automáticamente cualquier trait que declares siempre que se guarde en un archivo con el mismo nombre físico y dentro de las rutas del namespace mapeadas.
*   **Archivo relacionado:** [class-hooma-autoloader.php](../includes/class-hooma-autoloader.php)

### ¿Puedo utilizar Enums de PHP 8?
Sí, totalmente. Son la herramienta recomendada para manejar catálogos de estados fijos (ej. estados de una reserva: `PENDING`, `CONFIRMED`, `CANCELLED`).

### ¿Puedo utilizar Atributos de PHP 8?
Sí, el framework Core soporta el uso de la sintaxis nativa de PHP 8.0+ para anotaciones de metadatos o validaciones de clases.

### ¿Puedo cargar y utilizar librerías de terceros (Composer)?
Sí. Ejecuta `composer require` dentro de la raíz de tu módulo de forma aislada y añade la llamada de carga al inicio del archivo principal de tu módulo:
```php
require_once __DIR__ . '/vendor/autoload.php';
```

---

## Rendimiento

### ¿Los Services se cargan siempre en memoria en cada recarga?
No. Hooma Core implementa **Lazy Loading** (carga perezosa). El contenedor de servicios solo almacena funciones anónimas de resolución. La instancia real del servicio (ej. `DatabaseService` o `FilesystemService`) solo se crea en memoria la primera vez que tu código invoca su fachada correspondiente (`Hooma::database()`). Si una petición web no utiliza un servicio específico, éste nunca se instanciará, ahorrando CPU y memoria.
*   **Archivos relacionados:** [ServiceContainer.php](../includes/ServiceContainer.php) y [ServiceProvider.php](../includes/ServiceProvider.php)

### ¿Qué impacto en rendimiento tiene un módulo desactivado?
Cero. El cargador de módulos de Hooma Core recupera la lista de módulos activos desde las opciones cacheadas de WordPress y omite por completo leer, escanear o incluir cualquier archivo físico de los módulos que se encuentren inactivos en base de datos.
*   **Archivo relacionado:** [class-hooma-loader.php](../includes/class-hooma-loader.php)

### ¿Qué prácticas debo evitar para no ralentizar el Framework?
*   Evita registrar cargadores de clases pesados o ejecutar búsquedas de archivos en disco (`is_dir`, `file_exists`) dentro de ganchos globales recurrentes de WordPress.
*   No realices peticiones HTTP externas síncronas sin aplicar caché o timeouts cortos.
*   No llames a `get_option()` o `update_option()` de forma repetida dentro de bucles `foreach`; utiliza `Hooma::settings()` en su lugar.
*   **Archivo relacionado:** [class-hooma-autoloader.php](../includes/class-hooma-autoloader.php)

---

## Versiones

### ¿Cómo sé si un método de un servicio está disponible en mi versión de Hooma?
Todos los archivos de la documentación del manual y la API Reference inician con una etiqueta de compatibilidad aclaratoria (ej. `*Disponible desde v0.3*` o `*Disponible desde v1.0*`). Comprueba la constante global `HOOMA_VERSION` de tu instalación para asegurar la compatibilidad.

### ¿Cómo debo migrar un módulo entre versiones de Hooma Core?
Si una nueva versión de Hooma Core introduce algún cambio disruptivo (breaking change) documentado en el `changelog`, debes declarar la dependencia mínima requerida de Hooma en las cabeceras de tu módulo (ej. `Requires Hooma: 1.2.0`) para prevenir que tu módulo intente ejecutarse en versiones antiguas e incompatibles del Core.
