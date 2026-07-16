# API Reference: Scheduler Service

*Disponible desde v1.0*

El servicio `Scheduler` gestiona y agrupa de forma segura las tareas programadas periódicas en segundo plano (WP-Cron), validando la serialización de firmas y evitando duplicados.

---

## Métodos Públicos

### `schedule()`

Agenda una tarea programada recurrente si no existe previamente en la base de datos de WordPress.

#### Sintaxis
```php
public function schedule(string $handle, string $recurrence, $callback, ?int $start_time = null): void
```

#### Parámetros
- **`$handle`** *(string)*: Identificador semántico de negocio único de la tarea (ej: `'booking.sync'`).
- **`$recurrence`** *(string)*: Frecuencia de la tarea programada (ej. `'hourly'`, `'daily'`).
- **`$callback`** *(callable)*: El método a ejecutar. Debe ser serializable (ej: `array(MyJob::class, 'run')` o `'MyJob::run'`).
- **`$start_time`** *(int o null, opcional)*: Timestamp de inicio de ejecución. Por defecto es `time()`.

#### Excepciones
- **`\InvalidArgumentException`**: Si se pasa una función anónima (Closure), dado que no es almacenable de forma persistente en base de datos.

---

### `unschedule()`

Elimina una tarea del planificador cron de WordPress.

#### Sintaxis
```php
public function unschedule(string $handle): bool
```

#### Retorno
- **`bool`**: `true` si se desprogramó con éxito, `false` en caso contrario.

---

### `is_scheduled()`

Verifica si la tarea ya está programada en base de datos.

#### Sintaxis
```php
public function is_scheduled(string $handle): bool
```

---

### `next_run()`

Obtiene el timestamp de la próxima fecha programada de ejecución de la tarea.

#### Sintaxis
```php
public function next_run(string $handle): int|bool
```

#### Retorno
- **`int|bool`**: Timestamp en segundos o `false` si no está agendada.

---

### `daily()` | `hourly()`

Atajos de conveniencia semántica que delegan en `schedule()`.

#### Sintaxis
```php
public function daily(string $handle, $callback): void
public function hourly(string $handle, $callback): void
```
