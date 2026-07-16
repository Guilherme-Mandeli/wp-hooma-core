# API Reference: HTTP Service

*Disponible desde v0.3*

El servicio `HTTP` provee un cliente de peticiones de red robusto, que encapsula los resultados en objetos de respuesta estructurados y maneja errores de DNS/Timeout mediante excepciones nativas de PHP.

---

## Métodos Públicos

### `get()`

Realiza una petición HTTP GET a una URL remota.

#### Sintaxis
```php
public function get(string $url, array $args = array()): HttpResponse
```

#### Parámetros
- **`$url`** *(string)*: URL de destino.
- **`$args`** *(array, opcional)*: Argumentos de cabecera, timeout o cookies.

#### Retorno
- **`HttpResponse`**: Objeto de respuesta normalizado.

#### Excepciones
- **`\RuntimeException`**: Si falla la resolución DNS o la conexión física del servidor.

---

### `post()`

Realiza una petición HTTP POST enviando datos en el cuerpo del request.

#### Sintaxis
```php
public function post(string $url, array $data = array(), array $args = array()): HttpResponse
```

#### Parámetros
- **`$url`** *(string)*: URL de destino.
- **`$data`** *(array, opcional)*: Claves y valores a enviar en el body de la petición.
- **`$args`** *(array, opcional)*: Cabeceras y configuraciones extra.

#### Retorno
- **`HttpResponse`**: Objeto de respuesta normalizado.

---

### `request()`

Realiza una petición HTTP genérica utilizando cualquier método HTTP estándar.

#### Sintaxis
```php
public function request(string $method, string $url, array $args = array()): HttpResponse
```

---

## Métodos del Objeto `HttpResponse`

Cuando realizas una petición, el servicio devuelve un objeto de respuesta con los siguientes métodos de consulta rápidos:

- **`status(): int`**: Código de estado HTTP de respuesta (ej. `200`, `404`).
- **`body(): string`**: El contenido plano del cuerpo del mensaje.
- **`json(bool $assoc = true): array`**: Decodifica el cuerpo como JSON.
- **`headers(): array`**: Arreglo asociativo con las cabeceras HTTP de respuesta.
- **`successful(): bool`**: Determina si el código de estado es un éxito (2xx).
- **`failed(): bool`**: Determina si el código de estado indica error (fuera de la franja 2xx).
- **`raw(): array`**: Devuelve la estructura original de WordPress.
