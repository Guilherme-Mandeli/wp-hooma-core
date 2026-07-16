# API Reference: Settings Service

*Disponible desde v0.3*

El servicio `Settings` unifica la configuración de cada módulo de negocio, guardando los datos en formato JSON de forma agrupada en WordPress para minimizar las consultas a base de datos.

---

## Métodos Públicos

### `get()`

Obtiene un ajuste guardado en el módulo utilizando notación de puntos (dot-notation).

#### Sintaxis
```php
public function get(string $key, $default = null): mixed
```

#### Parámetros
- **`$key`** *(string)*: Clave del ajuste.
- **`$default`** *(mixed, opcional)*: Valor por defecto si la clave no existe.

#### Retorno
- **`mixed`**: El valor almacenado o el valor `$default`.

---

### `set()`

Guarda o sobrescribe un valor de configuración para el módulo actual en base de datos.

#### Sintaxis
```php
public function set(string $key, $value): bool
```

#### Parámetros
- **`$key`** *(string)*: Clave del ajuste.
- **`$value`** *(mixed)*: Valor a guardar (cualquier tipo de datos serializable).

#### Retorno
- **`bool`**: `true` en caso de éxito, `false` en caso de fallo.

---

### `exists()`

Verifica si un ajuste está registrado en el módulo.

#### Sintaxis
```php
public function exists(string $key): bool
```

---

### `delete()`

Elimina permanentemente una clave de configuración.

#### Sintaxis
```php
public function delete(string $key): bool
```
