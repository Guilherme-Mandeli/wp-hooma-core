# API Reference: Auth Service

*Disponible desde v0.3*

El servicio `Auth` centraliza la gestión de autorizaciones y usuarios, mapeando permisos lógicos de negocio a las capabilities nativas de WordPress de forma configurable y extensible.

---

## Métodos Públicos

### `can()`

Comprueba si el usuario actual posee un permiso semántico de Hooma o una capability nativa de WordPress.

#### Sintaxis
```php
public function can(string $permission): bool
```

#### Parámetros
- **`$permission`** *(string)*: Identificador del permiso (ej: `'booking.edit'`) o capability nativa de WordPress (ej: `'manage_options'`).

#### Retorno
- **`bool`**: `true` si el usuario tiene el permiso activo, `false` en caso contrario.

#### Ejemplo de uso
```php
if (!Hooma::auth()->can('booking.view')) {
    wp_die('Acceso denegado.');
}
```

---

### `user_id()`

Obtiene el ID del usuario actualmente autenticado en el sistema.

#### Sintaxis
```php
public function user_id(): int
```

#### Retorno
- **`int`**: ID único del usuario. Devuelve `0` si el visitante actual no ha iniciado sesión.

---

### `check()`

Determina si el visitante actual es un usuario autenticado.

#### Sintaxis
```php
public function check(): bool
```

#### Retorno
- **`bool`**: `true` si está autenticado, `false` si es un invitado anónimo.
