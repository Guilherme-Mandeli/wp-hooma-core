# API Reference: Logger Service

*Disponible desde v0.3*

El servicio `Logger` provee un motor de registro de eventos con rotación diaria y protección integrada contra accesos externos vía web.

---

## Métodos Públicos

### Métodos PSR-3 de Registro
El servicio implementa todos los niveles de severidad del estándar RFC 5424. Todos los métodos aceptan un mensaje textual y un contexto estructurado:

```php
public function emergency(string $message, array $context = array()): void
public function alert(string $message, array $context = array()): void
public function critical(string $message, array $context = array()): void
public function error(string $message, array $context = array()): void
public function warning(string $message, array $context = array()): void
public function notice(string $message, array $context = array()): void
public function info(string $message, array $context = array()): void
public function debug(string $message, array $context = array()): void
```

#### Parámetros
- **`$message`** *(string)*: Texto explicativo del log.
- **`$context`** *(array, opcional)*: Datos estructurados adicionales (ej. excepciones, valores devueltos).

#### Ejemplo de uso
```php
Hooma::logger()->error('Error de pasarela de pago', array(
    'gateway' => 'stripe',
    'code'    => 402
));
```

---

## Estructura y Protección de Archivos
Los archivos de logs se guardan de forma aislada en la carpeta del módulo:
`wp-content/uploads/hooma/logs/{nombre-modulo}-{fecha}.log`

El core protege automáticamente el directorio completo de logs generando un archivo de seguridad `.htaccess` con la instrucción:
`Deny from all`
