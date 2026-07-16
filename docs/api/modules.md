# API Reference: Modules Service

*Disponible desde v0.3*

El servicio `Modules` gestiona el descubrimiento, activación, desactivación y lectura de metadatos de los módulos de negocio cargados en Hooma Core.

---

## Métodos Públicos

### `get_all()`

Obtiene el listado completo de módulos detectados en el sistema (activos e inactivos).

#### Sintaxis
```php
public function get_all(): array
```

#### Retorno
- **`array`**: Arreglo asociativo de módulos indexados por su slug. Cada módulo contiene llaves como `name`, `version`, `description`, `active`, etc.

---

### `get_active()`

Obtiene únicamente los slugs de los módulos que se encuentran activos.

#### Sintaxis
```php
public function get_active(): array
```

#### Retorno
- **`array`**: Lista plana de slugs activos.

---

### `is_active()`

Verifica si un módulo concreto está activado.

#### Sintaxis
```php
public function is_active(string $slug): bool
```

#### Retorno
- **`bool`**: `true` si el módulo está activo, `false` en caso contrario.

---

### `activate()` | `deactivate()`

Activa o desactiva dinámicamente un módulo por su slug.

#### Sintaxis
```php
public function activate(string $slug): bool
public function deactivate(string $slug): bool
```

#### Retorno
- **`bool`**: `true` en caso de éxito en la operación, `false` si ocurre un error.
