# API Reference: Notices Service

*Disponible desde v0.3*

El servicio `Notices` administra una cola en memoria para registrar avisos administrativos (`admin_notices`), separando los datos del renderizado final HTML en WordPress.

---

## Métodos Públicos

### `success()`

Encola una alerta administrativa de tipo éxito (recuadro verde en WordPress).

#### Sintaxis
```php
public function success(string $message, bool $dismissible = true): void
```

#### Parámetros
- **`$message`** *(string)*: El cuerpo o texto del mensaje a mostrar.
- **`$dismissible`** *(bool, opcional)*: Permite al usuario cerrar la alerta mediante un botón. Por defecto es `true`.

---

### `warning()`

Encola una alerta administrativa de tipo advertencia (recuadro amarillo).

#### Sintaxis
```php
public function warning(string $message, bool $dismissible = true): void
```

---

### `error()`

Encola una alerta de error (recuadro rojo).

#### Sintaxis
```php
public function error(string $message, bool $dismissible = true): void
```

---

### `info()`

Encola una alerta administrativa de carácter informativo (recuadro azul).

#### Sintaxis
```php
public function info(string $message, bool $dismissible = true): void
```

---

### `get_and_clear()`

Obtiene y vacía la cola de notificaciones encoladas. Este método es consumido por el presentador del core y no debe ser llamado por los módulos de negocio.

#### Sintaxis
```php
public function get_and_clear(): array
```
