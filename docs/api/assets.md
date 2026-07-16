# API Reference: Assets Service

*Disponible desde v0.3*

El servicio `Assets` simplifica el registro y encolado de hojas de estilo (CSS) y scripts (JS) del módulo en WordPress, aplicando cache-busting automático basado en la fecha de modificación del archivo físico (`filemtime`).

---

## Métodos Públicos

### `register_script()` | `register_style()`

Registra un script o estilo en WordPress sin encolarlo inmediatamente en la página.

#### Sintaxis
```php
public function register_script(string $handle, string $relative_path, array $deps = array(), bool $in_footer = true): void
public function register_style(string $handle, string $relative_path, array $deps = array()): void
```

#### Parámetros
- **`$handle`** *(string)*: Nombre identificativo de negocio único del asset.
- **`$relative_path`** *(string)*: Ruta relativa al archivo dentro de la carpeta del módulo (ej: `'assets/js/app.js'`).
- **`$deps`** *(array, opcional)*: Dependencias requeridas (ej: `array('jquery')`).
- **`$in_footer`** *(bool, opcional)*: Cargar script en el footer. Por defecto es `true`.

---

### `enqueue_script()` | `enqueue_style()`

Encola y carga un script o estilo previamente registrado o lo registra y encola en una sola llamada.

#### Sintaxis
```php
public function enqueue_script(string $handle, string $relative_path = '', array $deps = array(), bool $in_footer = true): void
public function enqueue_style(string $handle, string $relative_path = '', array $deps = array()): void
```
