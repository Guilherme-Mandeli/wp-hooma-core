# API Reference: Cache Service

*Disponible desde v0.3*

El servicio `Cache` abstrae el almacenamiento temporal de datos (WordPress Transients), asegurando namespacing automático y hashing de claves para evitar límites de almacenamiento.

---

## Métodos Públicos

### `get()`

Recupera un elemento de la caché.

#### Sintaxis
```php
public function get(string $key, $default = null): mixed
```

#### Parámetros
- **`$key`** *(string)*: Clave única del elemento.
- **`$default`** *(mixed, opcional)*: Valor devuelto si el elemento no existe o expiró.

#### Retorno
- **`mixed`**: El valor deserializado o el valor `$default`.

---

### `set()`

Guarda un elemento en la caché con un tiempo de vida (TTL) específico.

#### Sintaxis
```php
public function set(string $key, $value, ?int $ttl = null): bool
```

#### Parámetros
- **`$key`** *(string)*: Clave única del elemento.
- **`$value`** *(mixed)*: Datos a almacenar (arrays, objetos, strings o booleanos).
- **`$ttl`** *(int o null, opcional)*: Segundos de validez. Si es `null`, se asume 24 horas (86400 segundos).

#### Retorno
- **`bool`**: `true` en caso de éxito, `false` en caso de fallo.

---

### `delete()`

Elimina permanentemente un elemento de la caché.

#### Sintaxis
```php
public function delete(string $key): bool
```

#### Parámetros
- **`$key`** *(string)*: Clave única del elemento.

#### Retorno
- **`bool`**: `true` si se eliminó con éxito, `false` si no existía o falló.

---

### `remember()`

Obtiene un elemento de la caché, o ejecuta un callback para generarlo y guardarlo si no existe.

#### Sintaxis
```php
public function remember(string $key, ?int $ttl, callable $callback): mixed
```

#### Parámetros
- **`$key`** *(string)*: Clave única del elemento.
- **`$ttl`** *(int o null)*: Segundos de validez si se guarda.
- **`$callback`** *(callable)*: Función anónima o callback que genera el valor.

#### Retorno
- **`mixed`**: El valor almacenado o el recién generado por el callback.

#### Ejemplo de uso
```php
$data = Hooma::cache()->remember('booking.api_users', 3600, function() {
    return Hooma::http()->get('https://api.github.com/users')->json();
});
```

---

## Mitigación de WordPress Transients
El servicio aplica hashing SHA-1 a la clave pasada (ej. `hooma_c_` + hash) reduciéndola siempre a 48 caracteres. Esto evita el error de truncado a **172 caracteres** de la base de datos de WordPress.
