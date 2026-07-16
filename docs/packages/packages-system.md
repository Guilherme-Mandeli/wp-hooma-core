# Sistema de Packages (Packages System)

*Disponible desde v1.1*

El sistema de **Packages** introduce una capa desacoplada dentro del ecosistema de **Hooma Core** para centralizar dependencias y recursos de infraestructura reutilizables por cualquier módulo, garantizando el aislamiento absoluto de la lógica de negocio.

---

## 1. Filosofía y Reglas Fundamentales

Los Packages son piezas de infraestructura pura que **no contienen lógica de negocio**. Tienen como único propósito dotar de capacidades tecnológicas comunes a los módulos del ecosistema.

```
WordPress
    │
    ▼
Hooma Core
 ┌──┴────────┐
 ▼           ▼
Modules   Packages
```

### Reglas de Diseño de la Arquitectura

1. **Un Módulo implementa negocio; un Package proporciona infraestructura.**
   - *Ejemplos de Módulos:* Booking, CRM, Newsletter, Analytics.
   - *Ejemplos de Packages:* Vue.js, esbuild, Stripe SDK, fuentes, iconos.
2. **Un Módulo nunca depende directamente de otro Módulo.** La comunicación entre módulos se realiza exclusivamente de forma asíncrona mediante el bus de eventos y filtros.
3. **Un Módulo sí puede utilizar cualquier Package.**
4. **Un Package es completamente agnóstico y reutilizable.** Nunca conoce los módulos que lo están consumiendo, no tiene acceso al ciclo de vida de Hooma ni ejecuta código automáticamente al arrancar.
5. **Acceso exclusivo mediante la API pública de Hooma Core.** Los módulos nunca deben acceder directamente al sistema de archivos ni usar rutas relativas para leer los archivos de un paquete. Todo se gestiona mediante `Hooma::packages()`.

---

## 2. Ubicación en el Ecosistema

El sistema de carpetas de Hooma se organiza bajo `wp-content/hooma/` para aislar el framework de otros plugins de WordPress:

```text
wp-content/
└── hooma/
    ├── modules/       <-- Módulos de negocio
    └── packages/      <-- Paquetes comunes de infraestructura
```

Cada paquete reside en su propio subdirectorio y requiere obligatoriamente un manifiesto llamado `manifest.json`.

```text
packages/
└── vue/
    ├── manifest.json  <-- Metadatos obligatorios
    ├── dist/          <-- Archivos distribuidos del paquete
    │   ├── vue.global.js
    │   └── vue.global.prod.js
    ├── README.md
    └── LICENSE
```

---

## 3. Especificación del Manifiesto (`manifest.json`)

Para evitar colisiones con entornos de Node.js/npm, Hooma utiliza un archivo llamado `manifest.json`. Este manifiesto declara la estructura, versión y puntos de entrada del paquete.

### Esquema del Manifiesto
```json
{
    "name": "vue",
    "version": "3.5.22",
    "type": "javascript",
    "description": "Vue.js Production Build",
    "author": "Evan You",
    "license": "MIT",
    "homepage": "https://vuejs.org",
    "keywords": ["frontend", "vue", "js"],
    "entries": {
        "production": "dist/vue.global.prod.js",
        "development": "dist/vue.global.js"
    }
}
```

### Propiedades del Esquema

| Propiedad | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `name` | `string` | **Sí** | Identificador único del paquete (slug). |
| `version` | `string` | **Sí** | Versión semántica del recurso (ej: `3.5.22`). |
| `type` | `string` | **Sí** | Tipo de recurso. Debe coincidir con los valores de `PackageType`. |
| `description`| `string` | No | Breve explicación del paquete. |
| `author` | `string` | No | Creador o mantenedor de la librería. |
| `license` | `string` | No | Licencia del recurso (ej: `MIT`, `GPL`). |
| `homepage` | `string` | No | URL del sitio oficial. |
| `keywords` | `array` | No | Colección de palabras clave para búsquedas internas. |
| `entries` | `object` | No | Mapeo de puntos de entrada nombrados (ej: `production`, `development`, `esm`) a sus archivos físicos relativos. |

### Ciclo de Vida y Actualización del Manifiesto

#### ¿Dónde se actualiza?
El archivo `manifest.json` se encuentra físicamente ubicado en la raíz de cada directorio de paquete:
`wp-content/hooma/packages/{slug-del-paquete}/manifest.json`

Cualquier cambio de versión, adición de metadatos o reestructuración de puntos de entrada (`entries`) debe realizarse directamente editando este archivo.

#### ¿Cómo y cuándo se aplican los cambios?
1. **Bootstrap Parsing**: Hooma Core no lee el disco continuamente ni realiza comprobaciones repetitivas del sistema de archivos para consultar los manifiestos. El descubrimiento, lectura del JSON y validación completa de los paquetes se ejecuta **únicamente una vez durante la fase de arranque** del núcleo (dentro del gancho de inicialización `plugins_loaded`).
2. **Registro en Memoria**: Una vez validados, todos los metadatos se almacenan de forma inmutable en el `PackagesRegistry` en memoria RAM para ser consultados por el resto de la petición.
3. **Propagación**:
   - **En Desarrollo**: Cualquier cambio que realices en el archivo `manifest.json` en disco se aplicará **de inmediato en la siguiente recarga de la página**, puesto que cada nueva petición HTTP inicia el bootstrap de WordPress de cero.
   - **En Producción**: Si el servidor web utiliza OPcache o caches de objetos persistentes, los cambios se propagarán al refrescarse estas caches tras un despliegue de código tradicional.

> [!WARNING]
> Si cometes un error al actualizar un `manifest.json` (ej. JSON malformado, omitir la propiedad `version`, o declarar un entry point a un archivo que no existe en el disco), el `PackageLoader` descartará el paquete completo escribiendo un log de error detallado en el servidor para evitar que el sitio sufra una pantalla en blanco.

---

## 4. Tipos de Package (`PackageType`)

Los tipos admisibles están fuertemente tipados mediante el Backed Enum `Hooma\Core\Services\Packages\PackageType`.

- **`javascript`** (PackageType::JavaScript): Scripts frontend de JS (ej: Vue, Alpine, Chart.js).
- **`php`** (PackageType::Php): Librerías y SDKs en PHP (ej: Stripe SDK, Carbon).
- **`binary`** (PackageType::Binary): Ejecutables externos o binarios de compilación (ej: esbuild, ffmpeg, wkhtmltopdf).
- **`asset`** (PackageType::Asset): Hojas de estilo CSS, fuentes o paquetes de iconos (ej: Font Awesome, Tailwind CSS).
- **`template`** (PackageType::Template): Plantillas de correo electrónico HTML o esquemas de generación de PDF.
- **`schema`** (PackageType::Schema): Archivos de definición de datos (ej: JSON Schema o archivos XML estructurados).

---

## 5. El Ciclo de Arranque y Descubrimiento (Symmetry Pattern)

El sistema de paquetes se inicializa de forma simétrica al cargador de módulos:

```
Bootstrap (hooma_init)
   │
   ├── Module Loader (Hooma_Loader) 
   │       └── Descubre y carga archivos PHP de módulos activos
   │
   └── Package Loader (PackageLoader)
           └── Escanea /packages/, valida y rellena el Registry
```

### Proceso de Carga y Validación
1. El **PackageLoader** lee recursivamente el directorio `wp-content/hooma/packages/`.
2. Para cada directorio que contiene un `manifest.json`:
   - Decodifica y valida la estructura sintáctica del archivo.
   - Crea un objeto inmutable `PackageManifest` (verifica la presencia de `name`, `version`, `type` y valida el formato de versión).
   - Valida que todos los archivos declarados en `entries` existan físicamente en el disco.
   - Verifica conflictos de nombres (si un paquete con el mismo nombre ya fue registrado, se descarta el segundo con un mensaje de error).
3. Si el paquete supera todas las pruebas, se instancia un objeto inmutable `Package` con un **UUID único generado al vuelo** y se guarda en el **PackagesRegistry** en memoria.
4. Una vez completado el arranque, el disco **nunca se vuelve a leer** para consultas de metadatos o rutas, garantizando un rendimiento óptimo.

---

## 6. API Pública (`Hooma::packages()`)

La fachada global de Hooma expone el servicio de paquetes mediante `Hooma::packages()`, el cual implementa la interfaz `Hooma\Core\Services\Packages\PackagesInterface`.

### Métodos del Servicio

#### `exists(string $name): bool`
Comprueba si un paquete está cargado y validado en el sistema.
```php
if (Hooma::packages()->exists('vue')) {
    // ...
}
```

#### `get(string $name): Package`
Obtiene la instancia inmutable de la clase `Package`. Lanza una `InvalidArgumentException` si el paquete no existe.
```php
$vue = Hooma::packages()->get('vue');
```

#### `version(string $name): string`
Obtiene de forma rápida la versión del paquete.
```php
$version = Hooma::packages()->version('chartjs'); // Devuelve "4.4.1"
```

#### `path(string $name): string`
Devuelve la ruta física absoluta de la carpeta del paquete.
```php
$path = Hooma::packages()->path('esbuild'); 
// Devuelve "/var/www/wp-content/hooma/packages/esbuild/"
```

#### `url(string $name): string`
Devuelve la URL web pública de la carpeta del paquete.
```php
$url = Hooma::packages()->url('vue');
// Devuelve "https://example.com/wp-content/hooma/packages/vue/"
```

#### `manifest(string $name): PackageManifest`
Obtiene el objeto de metadatos inmutable `PackageManifest`.
```php
$manifest = Hooma::packages()->manifest('vue');
echo $manifest->get_author();
```

#### `entry(string $name, string $entry_key = 'production'): string`
Resuelve y devuelve la ruta física absoluta del punto de entrada correspondiente a la clave solicitada. Lanza una excepción si la entrada no está configurada.
```php
$entryPath = Hooma::packages()->entry('vue', 'production');
// Devuelve "/var/www/wp-content/hooma/packages/vue/dist/vue.global.prod.js"
```

#### `all(): Package[]`
Devuelve un array asociativo con todas las instancias de `Package` indexadas por su nombre.
```php
$allPackages = Hooma::packages()->all();
```

#### `findByType(PackageType $type): Package[]`
Filtra los paquetes registrados por su tipo enum.
```php
use Hooma\Core\Services\Packages\PackageType;

$binaries = Hooma::packages()->findByType(PackageType::Binary);
```

#### `findByKeyword(string $keyword): Package[]`
Busca paquetes que tengan la palabra clave dada en su manifiesto.
```php
$jsLibs = Hooma::packages()->findByKeyword('js');
```

---

## 7. Ejemplos Prácticos de Integración

### Caso A: Encolar un Script JS (Vue.js) desde un Módulo

Un módulo de facturación quiere cargar Vue en la pantalla de administración de WordPress.

```php
// En el index.php o controlador de un módulo:
add_action('admin_enqueue_scripts', function() {
    if (!Hooma::packages()->exists('vue')) {
        return;
    }

    $vue = Hooma::packages()->get('vue');
    
    // Determinar si estamos en modo debug para usar la versión de desarrollo o producción
    $entry_key = (defined('WP_DEBUG') && WP_DEBUG) ? 'development' : 'production';
    
    $vue_url  = $vue->get_entry_url($entry_key);
    $version  = $vue->get_version();

    wp_enqueue_script(
        'hooma-vue',
        $vue_url,
        array(),
        $version,
        true
    );
});
```

### Caso B: Cargar un SDK de PHP (Stripe SDK) en el backend

Un módulo de pagos necesita usar el SDK oficial de Stripe centralizado en Packages.

```php
// En el index.php o constructor de un módulo de pasarela:
if (Hooma::packages()->exists('stripe-sdk')) {
    $stripe_path = Hooma::packages()->path('stripe-sdk');
    
    // Si el paquete expone un cargador autoiniciado o archivo bootstrap
    $autoloader = Hooma::packages()->entry('stripe-sdk', 'production');
    
    if (file_exists($autoloader)) {
        require_once $autoloader;
    }
}
```

### Caso C: Ejecutar un Binario (wkhtmltopdf) desde la línea de comandos

Un módulo generador de informes necesita exportar a PDF usando un binario centralizado.

```php
if (Hooma::packages()->exists('wkhtmltopdf')) {
    // Obtener la ruta del binario directamente
    $binary_path = Hooma::packages()->entry('wkhtmltopdf', 'production');
    
    if (file_exists($binary_path)) {
        $output_pdf = '/tmp/reporte.pdf';
        $input_html = '/tmp/reporte.html';
        
        $command = escapeshellcmd($binary_path) . ' ' . escapeshellarg($input_html) . ' ' . escapeshellarg($output_pdf);
        exec($command, $output, $status);
    }
}
```

---

## 8. Robustez e Inmutabilidad del Sistema

Para asegurar la máxima estabilidad en ejecución paralela o hilos complejos de WordPress:

- **Immutability total**: Las clases `Package` y `PackageManifest` no implementan ningún método mutador (`set`). Son de solo lectura tras su inicialización en el arranque del core.
- **Validación Temprana**: Si el manifiesto JSON de un paquete tiene errores tipográficos, carece de versión o declara un punto de entrada a un archivo inexistente en disco, Hooma Core escribe un mensaje descriptivo en el registro de errores (`error_log`) y **omite el registro de dicho paquete** sin tumbar la ejecución de la web.
- **Preparado para múltiples versiones**: Cada paquete cuenta con una propiedad interna `$uuid`. En futuras versiones de Hooma Core, esto permitirá soportar múltiples versiones coexistentes del mismo paquete (ej. `vue` v2.x y `vue` v3.x en la misma web) sin romper la API de búsqueda y resolución.
