# Hooma Core

Hooma Core es un framework de infraestructura ligero (Thin Core) y contenedor de servicios para el desarrollo y gestion de modulos de negocio desacoplados en WordPress.

## Filosofia y Proposito

Hooma Core existe para solucionar la fragmentacion, el acoplamiento rigido y la falta de testabilidad en desarrollos de gran escala sobre WordPress. 

Resuelve tres problemas especificos:
1. **Acoplamiento rigido**: Sustituye las llamadas directas entre componentes por comunicacion basada en un bus de eventos y filtros desacoplados.
2. **Ruido de administracion**: Evita la proliferacion de decenas de plugins independientes en el panel de WordPress, centralizando los modulos en un gestor unificado fuera de la ruta plugins/.
3. **Dependencia de APIs legacy**: Abstrae las funciones globales de WordPress (como transients, wp_remote o cron) en interfaces limpias, estandarizadas y testeables mediante inyeccion de dependencias perezosa.

## Requisitos

* PHP 8.0 o superior
* WordPress 6.0 o superior

## Características principales

* Contenedor de servicios con carga diferida (lazy load).
* Autocargador PSR-4 nativo para el núcleo y módulos.
* Sistema de descubrimiento y ciclo de vida de módulos fuera de la carpeta de plugins tradicional.
* Sistema de Packages para centralizar recursos comunes de infraestructura (Vue, Alpine, Stripe SDK, etc.) de forma inmutable y desacoplada.
* **Descubrimiento automático de Documentación y Demos**: Escaneo dinámico de múltiples carpetas de documentación (`docs`, `documentation`, `documents`, `manual`, `guide`, `guides`, `wiki`, `help`) y pestañas de demostración (`demo`, `demos`, `example`, `examples`, `sample`, `samples`, `playground`, `preview`, `showcase`) con ejecución en `iframe`.
* **Normalizador de Slugs a Títulos (Slug Title Normalization)**: Conversión conservadora de nombres de archivo y slugs a frases legibles en la interfaz, conservando prefijos numéricos de orden y acrónimos técnicos (`API`, `AI`, `UI`, `UX`, `WordPress`, `GA4`, `WP`, `JS`, `TS`, `PHP`, `HTML`, `CSS`, etc.).
* Abstracción de APIs críticas de WordPress (base de datos, caché, peticiones HTTP, cron y eventos).

## Estructura del proyecto

* **admin/**: Controlador y tablas de la interfaz de administracion de modulos.
* **assets/**: Recursos estaticos del framework.
* **config/**: Definicion de constantes globales del core.
* **docs/**: Documentacion oficial y manual tecnico.
* **includes/**: Infraestructura del nucleo, fachada principal, instalador y cargador.
* **services/**: Servicios desacoplados de infraestructura del sistema.

## Instalacion y Carga de Modulos

1. Instalar y activar Hooma Core como un plugin estandar de WordPress.
2. Los modulos de desarrollo y negocio deben ubicarse en la ruta `wp-content/uploads/hooma/modules/`.
3. Administrar el estado de activacion de los modulos desde el panel administrativo de Hooma en el escritorio de WordPress.

---

## Guia de Documentacion y Ejemplos

Toda la documentacion técnica detallada esta estructurada de forma relativa dentro del directorio `docs/`:

### Conceptos e Inicio
* **[Indice del Manual](docs/README.md)**: Estructura general de aprendizaje.
* **[Preguntas Frecuentes (FAQ)](docs/faq.md)**: Dudas de diseño, rendimiento y compatibilidad con WordPress.
* **[Conceptos del Framework](docs/concepts/framework.md)**: Explicacion del Thin Core y las fronteras de desarrollo.
* **[Sistema de Packages](docs/packages/packages-system.md)**: Documentación y especificaciones técnicas de la infraestructura de paquetes reutilizables.

### Desarrollo de Modulos
* **[Guia de Desarrollo de Modulos](docs/modules/development-guide.md)**: Estructura de archivos, cabeceras de metadatos y namespaces.
* **[Buenas Practicas](docs/modules/best-practices.md)**: Reglas de diseño y limites de acoplamiento.
* **[Anti-Patrones](docs/modules/anti-patterns.md)**: Tabla comparativa de sustitucion de llamadas nativas de WordPress.

### Referencias Tecnicas
* **[Referencia de la API](docs/api/)**: Detalle de firmas de metodos, excepciones y retornos de los 11 servicios principales.
* **[Decision Records (ADR)](docs/adr/)**: Registro historico de decisiones de arquitectura tomadas en el framework.

### Tutoriales y Ejemplos
* **[Tutoriales Progresivos](docs/tutorials/)**: Curso estructurado de 11 capitulos para construir un modulo desde cero.
* **[Cookbook (Recetario)](docs/cookbook/)**: Coleccion de 10 soluciones de codigo listas para copiar y pegar en casos de uso reales.
* **[Modulos de Ejemplo](docs/examples/)**: Plantillas estaticas de modulos listas para analizar y clonar.
