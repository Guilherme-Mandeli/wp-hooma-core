# Manual Oficial de Hooma Core

*Disponible desde v0.1*

Bienvenido al manual oficial de **Hooma Core**, un framework de infraestructura ligero (Thin Core) diseñado para estructurar módulos de negocio desacoplados y reutilizables en WordPress.

Nuestra documentación está organizada según tu perfil y objetivo de desarrollo. Selecciona el camino adecuado para comenzar:

---

## Perfiles de Desarrollo

### Desarrollo de Módulos (Module Developer)
*Si tu objetivo es crear funcionalidades de negocio (ej. reservas, facturación, pasarelas de pago) sin modificar el núcleo de Hooma.*

1.  **[Conceptos Básicos](concepts/framework.md)**: Entiende la filosofía del Thin Core y las responsabilidades del sistema.
2.  **[Guía del Desarrollador](modules/development-guide.md)**: Aprende la estructura de carpetas, metadatos, cómo incluir **documentación (`docs/`, `manual/`)** y **demos interactivas (`demo/`, `playground/`)**, y cómo escribir tu primer módulo.
3.  **[Buenas Prácticas](modules/best-practices.md)** y **[Anti-Patrones](modules/anti-patterns.md)**: Reglas de oro para escribir código limpio, seguro y modular.
4.  **[Serie de Tutoriales Progresivos](tutorials/01-installation.md)**: Una serie paso a paso para dominar Hooma Core.
5.  **[Cookbook (Recetario)](cookbook/custom-database-table.md)**: Soluciones prácticas y directas a problemas habituales del día a día.
6.  **[API Reference (Servicios)](api/settings.md)**: Consulta rápida de las firmas y parámetros de todos los métodos disponibles.
7.  **[Módulos de Ejemplo](examples/)**: Código fuente real y listo para clonar.
8.  **[Sistema de Packages](packages/packages-system.md)**: Aprende cómo consumir dependencias y recursos de infraestructura (como Vue, Alpine o Stripe SDK) desde tus módulos de negocio de forma desacoplada, incluyendo la adición de pestañas de documentación y demostraciones.

### Desarrollo del Núcleo (Core Developer)
*Si estás contribuyendo al framework, extendiendo sus capacidades base o quieres entender cómo funciona por debajo.*

1.  **[Flujo de Bootstrap y Ciclo de vida](architecture/bootstrap-lifecycle.md)**: Diagramas del proceso de arranque y descubrimiento.
2.  **[Service Container y Fachada](architecture/service-container.md)**: Inyección lazy y el funcionamiento estático de `Hooma`.
3.  **[Autocargador PSR-4](core/autoloader.md)**: Resolución interna de namespaces de Hooma y módulos de negocio.
4.  **[Arquitectura del Sistema de Packages](packages/packages-system.md)**: Entiende el cargador Package Loader, el Registry en memoria, las validaciones de manifiesto y el diseño inmutable.
5.  **[Architecture Decision Records (ADR)](adr/0001-modules-outside-wp-plugins.md)**: Historial de por qué se tomaron las decisiones de diseño clave.
6.  **[Changelog](changelog/changelog.md)**: Historial de versiones y cambios del Core.

---

## ¿Tienes Dudas?
Consulta nuestra sección de **[Preguntas Frecuentes (FAQ)](faq.md)**, donde resolvemos las inquietudes operacionales más comunes sobre la compatibilidad con WordPress y la separación de responsabilidades.
