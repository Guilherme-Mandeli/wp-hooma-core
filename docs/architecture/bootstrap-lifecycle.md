# Arquitectura del Núcleo: Bootstrap y Ciclo de Vida

*Disponible desde v0.1*

Este documento describe en profundidad el proceso interno de inicialización (bootstrap) de Hooma Core y cómo interactúa con el flujo de carga tradicional de WordPress.

---

## 1. El Flujo de Carga (Bootstrap Timeline)

Cuando WordPress procesa una petición HTTP, el ciclo de vida de Hooma se desglosa cronológicamente de la siguiente forma:

1.  **Carga del Plugin Principal**: WordPress carga `hooma.php` desde el directorio de plugins.
2.  **Carga del Autocargador**: Se incluye e inicializa la clase `Hooma_Autoloader`. Esta clase registra el namespace raíz `Hooma\Core\` mapeado a `includes/` y `services/`.
3.  **Construcción del Contenedor**: Se crea el contenedor `ServiceContainer`.
4.  **Vinculación de Servicios**: Se instancia `ServiceProvider`, el cual registra los cierres (closures) perezosos para instanciar todos los servicios base (`logger`, `settings`, `http`, etc.) solo si se solicitan.
5.  **Inicialización (`plugins_loaded`)**:
    - Hooma ejecuta la función `hooma_init()` asociada al hook `plugins_loaded`.
    - Vincula el contenedor a la fachada global estática `Hooma::set_container()`.
    - Inicializa el cargador `Hooma_Loader`.
6.  **Descubrimiento de Módulos (Discovery)**:
    - `Hooma_Loader` lee el directorio físico de módulos en `uploads/hooma/modules/`.
    - Escanea las cabeceras de comentarios para recuperar los metadatos de los módulos.
    - Autocarga el archivo principal `index.php` de todos los módulos que estén activos en la base de datos.
7.  **Arranque de Módulos**: Cada módulo ejecuta su código inicial en `index.php` registrando sus propios ganchos administrativos o de frontend en WordPress y asociando escuchadores al bus de eventos de Hooma.

---

## 2. Diagrama del Ciclo de Arranque (Bootstrap Flowchart)

```
[ WordPress Core ]
       │
       ▼
[ Carga hooma.php ] ───> [ Inicializa Hooma_Autoloader ]
                                      │
                                      ▼
                         [ Crea ServiceContainer ]
                                      │
                                      ▼
                         [ Vincula ServiceProvider (Lazy) ]
                                      │
                                      ▼
[ plugins_loaded ] ────> [ Hooma::set_container($container) ]
                                      │
                                      ▼
                         [ Hooma_Loader->run() ]
                                      │
                                      ▼
                         [ Escaneo y carga de index.php ]
                                      │
                                      ▼
                         [ Módulos registran add_action() ]
```
---

## 3. Hooks y Eventos del Ciclo de Vida

Hooma Core provee eventos propios de control para que los desarrolladores de módulos ejecuten tareas durante la activación o desactivación de un módulo:

### `hooma_module_activated_{slug}`
Se dispara una sola vez en el instante en que el administrador activa el módulo desde la pantalla de gestión de módulos. Es ideal para crear tablas SQL iniciales, registrar opciones por defecto o limpiar directorios temporales.

### `hooma_module_deactivated_{slug}`
Se dispara una vez al desactivar el módulo. Útil para desprogramar tareas cron programadas por dicho módulo o limpiar archivos temporales.
