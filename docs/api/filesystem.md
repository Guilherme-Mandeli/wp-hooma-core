# API Reference: Filesystem Service

*Disponible desde v0.3*

El servicio `Filesystem` provee una API de abstracción completa sobre el almacenamiento local del servidor, encapsulando y protegiendo las llamadas contra permisos del servidor mediante `WP_Filesystem`.

---

## Métodos Públicos

### `read()`

Lee el contenido plano de un archivo físico local.

#### Sintaxis
```php
public function read(string $path): string
```

#### Parámetros
- **`$path`** *(string)*: Ruta absoluta del archivo en el servidor.

#### Retorno
- **`string`**: Contenido plano del archivo.

#### Excepciones
- **`\RuntimeException`**: Si el archivo no existe o no se poseen privilegios de lectura.

---

### `write()`

Escribe o sobrescribe el contenido en un archivo del servidor. Crea de forma recursiva los directorios padre si no existen.

#### Sintaxis
```php
public function write(string $path, string $content): bool
```

#### Parámetros
- **`$path`** *(string)*: Ruta absoluta del archivo de destino.
- **`$content`** *(string)*: Contenido textual a volcar.

#### Retorno
- **`bool`**: `true` en caso de éxito, `false` de lo contrario.

---

### `exists()`

Verifica si un archivo o carpeta existe en el servidor.

#### Sintaxis
```php
public function exists(string $path): bool
```

---

### `delete()`

Elimina un archivo o directorio de forma permanente.

#### Sintaxis
```php
public function delete(string $path, bool $recursive = false): bool
```

---

### `copy()`

Copia un archivo de un punto a otro. Sobrescribe el destino si ya existe.

#### Sintaxis
```php
public function copy(string $src, string $dest): bool
```

---

### `mkdir()`

Crea un directorio de forma recursiva con permisos 0755 por defecto.

#### Sintaxis
```php
public function mkdir(string $path): bool
```
