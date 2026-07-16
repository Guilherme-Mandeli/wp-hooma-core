# Concepto: Ciclo de Vida (Lifecycle)

*Disponible desde v0.1*

Comprender el ciclo de vida de ejecución de Hooma Core es fundamental para saber en qué momento registrar tus hooks, despachar eventos o interactuar con otros servicios.

---

## Flujo de Carga y Ejecución (Lifecycle)

El ciclo de vida se inicia cuando WordPress procesa la carga inicial de los plugins y se divide en 4 fases secuenciales claras:

### 1. Inicialización (Bootstrap)
- WordPress carga el archivo principal `hooma.php`.
- Se instancia el cargador automático `Hooma_Autoloader` para registrar los directorios PSR-4 del Core y Módulos.
- Se crea el `ServiceContainer` y se vinculan perezosamente (lazy) todos los servicios de infraestructura mediante `ServiceProvider`.

### 2. Arranque del Entorno (Boot)
- Se ejecuta la función `hooma_init()` en el hook `plugins_loaded` de WordPress.
- Se asocia el contenedor de servicios a la fachada global estática `Hooma`.
- Se instancia el cargador de módulos `Hooma_Loader` para escanear el directorio físico de módulos de negocio.

### 3. Descubrimiento y Activación de Módulos (Discovery)
- `Hooma_Loader` escanea y recupera los metadatos de todos los archivos `index.php` (o `[slug].php`) dentro de la carpeta de módulos.
- Se determina qué módulos están activos (según las opciones de la base de datos).
- **Ejecución del Módulo**: Para cada módulo activo, se incluye su archivo principal de forma segura. En este momento, el módulo declara sus namespaces, engancha callbacks a hooks de WordPress o se suscribe a eventos del sistema.

### 4. Petición y Ejecución (Runtime)
- Con los módulos ya inicializados y cargados, WordPress continúa su flujo de ejecución.
- Si se accede a la administración, el presentador de alertas (`NoticeRenderer`) captura y dibuja las notificaciones acumuladas en `NoticesService`.
- Si se dispara un evento programado de cron, `SchedulerService` intercepta la ejecución y procesa el callback correspondiente.

---

## Cronología del Lifecycle

El siguiente diagrama detalla la cronología de eventos durante el ciclo de vida:

```
 WordPress          Hooma Core Boot        Hooma Loader        Módulos Activos
    │                     │                     │                     │
    ├─ plugins_loaded ───>│                     │                     │ (Inicializa Contenedor)
    │                     ├─ init loader ──────>│                     │ (Escanea metadatos)
    │                     │                     ├─ Carga index.php ──>│ (Módulos se registran)
    │                     │                     │                     ├─ add_action()
    │                     │                     │                     ├─ Hooma::events()->listen()
    │                     │                     │                     └─ Hooma::scheduler()->schedule()
    ▼                     ▼                     ▼                     ▼
```
