# Arquitectura del Núcleo: Service Container y Fachada

*Disponible desde v0.2*

Este documento describe el diseño técnico del contenedor de servicios (`ServiceContainer`) y el funcionamiento interno de la fachada global estática `Hooma`.

---

## 1. El Service Container (Lazy Loading)

El contenedor de servicios centraliza la instanciación e inyección de dependencias de Hooma Core. 

### El Problema de Rendimiento:
Si instanciamos todos los servicios de infraestructura (conectar a base de datos, configurar logger, abrir sistema de archivos, registrar enrutadores) en cada carga de página de WordPress, ralentizaremos drásticamente el sitio web (incluso para peticiones simples de imágenes o APIs).

### La Solución: Registro Lazy (Perezoso):
El contenedor almacena "recetas de instanciación" (funciones anónimas/closures) en lugar de los objetos reales. La clase solo se construye en memoria la primera vez que un módulo invoca el servicio.

```php
// En ServiceProvider.php
$this->container->singleton('logger', function() {
    // Esta función no se ejecuta al arrancar Hooma
    return new \Hooma\Core\Services\Logger\LoggerService();
});
```

Cuando un módulo hace:
```php
Hooma::logger()->info('Mensaje');
```
El contenedor intercepta la llamada, ejecuta el closure de `'logger'` por primera vez, guarda el objeto resultante y lo devuelve. Las llamadas posteriores reutilizarán la misma instancia compartida (Singleton).

---

## 2. La Fachada Global Estática `Hooma`

Para evitar tener que pasar variables del contenedor a lo largo de todas las clases de tus módulos, Hooma provee una clase global estática llamada `Hooma` que vive en el namespace global (sin barras ni namespaces).

### Cómo resuelve la fachada la invocación:
La fachada delegará las llamadas estáticas al contenedor interno resolviendo los servicios de forma segura:

```php
class Hooma {
    protected static $container;

    public static function set_container($container) {
        self::$container = $container;
    }

    public static function get($id) {
        return self::$container->get($id);
    }

    // Retorna el contrato/interfaz del servicio
    public static function settings() {
        return self::get('settings');
    }
}
```

Esto proporciona a los desarrolladores de módulos una sintaxis sumamente limpia y expresiva (`Hooma::settings()->get(...)`) sin renunciar a las ventajas de testeabilidad e inyección de dependencias por debajo.
