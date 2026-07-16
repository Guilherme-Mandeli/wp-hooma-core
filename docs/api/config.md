# API Reference: Config Service

*Disponible desde v0.3*

El servicio `Config` centraliza el acceso a las variables de entorno, constantes físicas, rutas del sistema y namespaces de Hooma Core.

---

## Métodos Públicos

### `get()`

Obtiene un valor de la configuración interna de Hooma utilizando notación de puntos (dot-notation).

#### Sintaxis
```php
public function get(string $key, $default = null): mixed
```

#### Parámetros
- **`$key`** *(string)*: Clave de configuración (ej. `'paths.modules'`).
- **`$default`** *(mixed, opcional)*: Valor devuelto si la clave no existe. Por defecto es `null`.

#### Retorno
- **`mixed`**: El valor almacenado (puede ser string, array o booleano) o el valor `$default` si no se encuentra.

#### Ejemplo de uso
```php
// Obtener el namespace raíz de los módulos
$namespace = Hooma::config()->get('namespaces.modules', 'HoomaModules');
```

---

### `set()`

Define dinámicamente un valor de configuración interna en memoria para la petición actual.

#### Sintaxis
```php
public function set(string $key, $value): void
```

#### Parámetros
- **`$key`** *(string)*: Clave de configuración en notación por puntos (ej. `'mi_modulo.token'`).
- **`$value`** *(mixed)*: Valor a asignar en memoria.

#### Retorno
- **`void`**

#### Ejemplo de uso
```php
// Guardar dinámicamente una url para el hilo actual
Hooma::config()->set('mi_modulo.endpoints.sync', 'https://api.produccion.com');
```

---

## Claves de Configuración del Sistema
El framework inicializa y protege las siguientes variables globales por defecto:

| Clave | Tipo | Descripción |
|---|---|---|
| `version` | *string* | Versión de Hooma Core instalada. |
| `paths.root` | *string* | Ruta física raíz del plugin Hooma Core. |
| `paths.modules` | *string* | Ruta física donde residen los módulos de negocio. |
| `urls.root` | *string* | URL pública raíz de Hooma Core. |
| `urls.modules` | *string* | URL pública de la carpeta de módulos de negocio. |
| `namespaces.modules`| *string* | Namespace raíz por defecto (`HoomaModules`). |
