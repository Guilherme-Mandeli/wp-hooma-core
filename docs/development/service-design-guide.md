# Guía de Diseño de Servicios (Service Design Guide)

Para garantizar la consistencia, escalabilidad y mantenibilidad de **Hooma Core** a largo plazo, todo servicio de infraestructura debe seguir de forma estricta las directrices de esta guía.

---

## Principios Fundamentales

### 1. Una Sola Responsabilidad (Single Responsibility Principle)
Cada servicio debe resolver un único problema de infraestructura. Si un servicio comienza a manejar lógica no relacionada (por ejemplo, que el `Logger` maneje caché o que `Notices` genere HTML directo), debe dividirse.

### 2. Aislamiento de WordPress (No WordPress Leak)
Los módulos no deben interactuar de forma directa con estructuras acopladas a WordPress desde la API pública de los servicios de Hooma Core:
- **No devolver objetos WP**: Por ejemplo, nunca retornar objetos como `\WP_Error`, `\WP_User`, `\WP_Post` o `$wpdb`. Si se necesita devolver información, se debe modelar una clase ligera de Hooma o devolver tipos nativos limpios de PHP.
- **No devolver arreglos crudos de WP**: Los arreglos que devuelven funciones de WordPress (como las cabeceras de peticiones HTTP de `wp_remote_get`) deben ser normalizados y expuestos mediante una API consistente.
- **No lanzar ni retornar `WP_Error`**: En su lugar, el servicio debe manejar los errores internamente o lanzar excepciones nativas (`\Exception`, `\RuntimeException`, etc.) con mensajes claros y descriptivos.

### 3. API Consistente y Limpia
- El diseño debe priorizar la facilidad de uso para el desarrollador del módulo.
- Evitar métodos "Utility" gigantescos. Los métodos deben ser pequeños y enfocados.
- Las interfaces (`Interface`) solo deben crearse si existe una posibilidad razonable de requerir implementaciones alternativas en el futuro (ej. `CacheInterface` para transients/redis, `FilesystemInterface` para local/S3). Para servicios de implementación única y definitiva (como `Auth` o `Notices`), la clase concreta es suficiente para mantener el código simple.

### 4. Tipado Fuerte (Strong Typing)
- Hacer uso de la declaración de tipos en los parámetros y retornos siempre que sea posible.
- Documentar detalladamente los tipos complejos y estructuras en las firmas PHPDoc.

### 5. Sin HTML ni Lógica de Negocio
- Los servicios de infraestructura no deben contener etiquetas HTML incrustadas. Si se requiere renderizar (como en `Notices`), el servicio gestiona el modelo de datos (la cola de avisos) y un presentador o renderer independiente se encarga de la salida HTML.
- Ningún servicio de infraestructura del Core debe implementar lógica de negocio (reglas de reserva, cálculos de impuestos, sincronizaciones externas, etc.). Todo ello pertenece en su totalidad a los módulos.

### 6. Carga Perezosa (Lazy Loading)
- Todos los servicios deben registrarse en el contenedor de servicios de forma perezosa (lazy). Ningún servicio debe ser instanciado hasta que el desarrollador invoque explícitamente su método en la fachada global (ej. `Hooma::logger()`).

---

## Checklist de Calidad para Nuevos Servicios

Todo servicio desarrollado debe cumplir con la siguiente lista de verificación:

- [ ] ¿Tiene una única responsabilidad bien definida?
- [ ] ¿Su API expone métodos pequeños y autoexplicativos?
- [ ] ¿Está completamente libre de acoplamiento a estructuras de retorno de WordPress (`WP_Error`, etc.)?
- [ ] ¿Lanza excepciones claras ante errores críticos en lugar de retornar códigos de fallo silenciosos?
- [ ] ¿Posee documentación técnica asociada en la carpeta `docs/services/`?
- [ ] ¿Está registrado en el `ServiceProvider` usando la carga perezosa (`singleton`)?
- [ ] ¿Tiene tipado estricto en parámetros y valores de retorno?
