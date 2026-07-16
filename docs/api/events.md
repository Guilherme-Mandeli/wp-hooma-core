# API Reference: Events Service

*Disponible desde v1.0*

El servicio `Events` provee un canal de comunicación de bajo acoplamiento (suscripción/publicación) que encapsula de forma transparente las acciones y filtros de WordPress.

---

## Métodos Públicos

### `dispatch()`

Dispara un evento global y notifica a todos los escuchadores registrados.

#### Sintaxis
```php
public function dispatch(string $event, ...$payload): EventResult
```

#### Parámetros
- **`$event`** *(string)*: Nombre del evento en formato namespaced (ej: `'booking.created'`).
- **`$payload`** *(mixed)*: Argumentos y variables contextuales que se propagan.

#### Retorno
- **`EventResult`**: Objeto que contiene métricas del disparo, como `listeners_executed()`.

---

### `listen()`

Registra un escuchador o callback para un evento determinado.

#### Sintaxis
```php
public function listen(string $event, callable $callback, int $priority = 10, int $accepted_args = 1): void
```

#### Parámetros
- **`$event`** *(string)*: Nombre del evento.
- **`$callback`** *(callable)*: Función, método estático o array-callback a ejecutar.
- **`$priority`** *(int, opcional)*: Orden de prioridad de ejecución (menor ejecuta antes). Por defecto es `10`.
- **`$accepted_args`** *(int, opcional)*: Número de parámetros que acepta el callback. Por defecto es `1`.

---

### `filter()`

Pasa un valor a través de una cadena de filtros modificadores de datos, retornando el valor final mutado.

#### Sintaxis
```php
public function filter(string $event, $value, ...$payload): mixed
```

#### Parámetros
- **`$event`** *(string)*: Nombre del filtro (ej. `'booking.price'`).
- **`$value`** *(mixed)*: El dato principal a modificar.
- **`$payload`** *(mixed)*: Argumentos contextuales adicionales no mutables.

#### Retorno
- **`mixed`**: El valor modificado final.
