# Concepto: Servicios (Services)

*Disponible desde v0.2*

Los **Servicios** son los componentes de infraestructura provistos por el núcleo de Hooma Core. Representan herramientas y utilidades comunes que los módulos consumen de forma unificada.

---

## ¿Qué es un Servicio?

Un servicio resuelve una necesidad técnica común de infraestructura (ej. registrar logs de error, guardar configuraciones, realizar peticiones HTTP o manejar la caché). 

En Hooma, todos los servicios están enlazados en el **Service Container** y se accede a ellos a través de la fachada global estática `Hooma`:

```php
// Acceso al servicio de configuración
Hooma::settings()->get('key');

// Acceso al servicio de peticiones de red
Hooma::http()->get('url');
```

---

## ¿Por qué abstraemos WordPress?

WordPress provee cientos de funciones globales de utilidad. Sin embargo, en el desarrollo a gran escala, confiar ciegamente en ellas presenta tres grandes problemas:

1.  **Falta de Tipado y Retornos inconsistentes**: Muchas funciones de WordPress devuelven estructuras impredecibles (por ejemplo, arrays, booleanos o el temido objeto `WP_Error`).
2.  **Dificultad de Testeo**: Es muy difícil escribir pruebas unitarias de tus módulos si están acoplados directamente a funciones globales de WordPress que requieren una base de datos activa.
3.  **Rigidez**: Si WordPress decide deprecación de APIs o si deseas cambiar la persistencia de datos (por ejemplo, mover el caché de Transients locales a Redis), tendrías que modificar todos tus módulos.

### El rol de abstracción de Hooma Services:
Los servicios actúan como traductores y escudos protectores entre tus módulos y WordPress:
- **Normalización**: Convierten los errores nativos de WordPress en excepciones estándar de PHP (`\RuntimeException`, `\InvalidArgumentException`).
- **Encapsulación**: Tus módulos solo ven contratos de Hooma (ej. el objeto `HttpResponse`), garantizando que la implementación interna pueda mutar o actualizarse en el Core sin romper tus módulos de negocio.
