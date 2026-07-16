# API Reference: Database Service

*Disponible desde v0.3*

El servicio `Database` provee un acceso simplificado, seguro y tipado sobre el motor de base de datos de WordPress (`$wpdb`), forzando el preparado automático de consultas con placeholders.

---

## Métodos Públicos

### `query()`

Ejecuta cualquier consulta SQL de escritura o actualización.

#### Sintaxis
```php
public function query(string $query, array $args = array()): int|bool
```

#### Parámetros
- **`$query`** *(string)*: Sentencia SQL (admite placeholders `%s`, `%d`).
- **`$args`** *(array, opcional)*: Valores ordenados que se inyectan en los placeholders.

#### Retorno
- **`int|bool`**: Número de filas afectadas en caso de éxito, o `false` si ocurre un error.

---

### `get_results()`

Ejecuta una consulta SELECT y devuelve el conjunto de resultados.

#### Sintaxis
```php
public function get_results(string $query, array $args = array()): array
```

#### Retorno
- **`array`**: Arreglo bidimensional (filas con arreglos asociativos).

---

### `get_row()`

Recupera una única fila de la base de datos.

#### Sintaxis
```php
public function get_row(string $query, array $args = array()): ?object
```

#### Retorno
- **`object|null`**: Objeto de la fila en formato stdClass, o `null` si no se encuentra.

---

### `get_var()`

Obtiene el valor de una única celda (columna 1 de la fila 1).

#### Sintaxis
```php
public function get_var(string $query, array $args = array()): mixed
```

---

### `insert()` | `update()` | `delete()`

Realizan inserciones, actualizaciones y borrados rápidos pasándoles arreglos de clave/valor.

#### Sintaxis
```php
public function insert(string $table, array $data): int|bool
public function update(string $table, array $data, array $where): int|bool
public function delete(string $table, array $where): int|bool
```

---

### `prefix()`

Obtiene el prefijo de tablas oficial de WordPress configurado en la base de datos.

#### Sintaxis
```php
public function prefix(): string
```

#### Ejemplo de uso
```php
$table = Hooma::database()->prefix() . 'hooma_logs';
```
