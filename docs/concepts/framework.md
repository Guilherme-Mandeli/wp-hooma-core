# Concepto: El Framework

*Disponible desde v0.1*

Hooma Core no es un CMS, ni reemplaza a WordPress; actúa como un **micro-framework de infraestructura** (Thin Core) que introduce orden, modularidad y separación de responsabilidades en el desarrollo de WordPress.

---

## Filosofía Thin Core (Núcleo Mínimo)

En el ecosistema de WordPress, es habitual ver plugins gigantes que mezclan la lógica de base de datos, maquetación HTML y llamadas externas en un solo archivo. Esto genera código imposible de mantener y testear.

La filosofía **Thin Core** establece que el núcleo del framework debe ser lo más compacto posible:
- **Responsabilidad Única**: El núcleo solo provee la infraestructura básica (carga, autocarga, contenedor de servicios y UI Kit).
- **Carga Diferida (Lazy Loading)**: Ningún servicio se instancia hasta que un módulo lo solicita explícitamente, manteniendo el consumo de memoria en mínimos históricos.
- **Lógica en Módulos**: Toda la lógica de negocio (las funcionalidades que ve el usuario final) vive y muere dentro de los módulos. El framework es solo el pegamento que los mantiene organizados.

---

## Diagrama de la Arquitectura

El flujo de interacción de Hooma Core se estructura en capas bien definidas:

```
┌────────────────────────────────────────┐
│               WordPress                │ (Carga inicial y ciclo de vida global)
└───────────────────┬────────────────────┘
                    │
                    ▼
┌────────────────────────────────────────┐
│           Hooma Core Boot              │ (Inicializa Autoloader e inyecta)
└───────────────────┬────────────────────┘
                    │
                    ▼
┌────────────────────────────────────────┐
│           Service Container            │ (Inyección perezosa de dependencias)
└───────────────────┬────────────────────┘
                    │
                    ▼
┌────────────────────────────────────────┐
│          Fachada Global (Hooma)        │ (Puerta de enlace estática limpia)
└───────┬────────────────────────┬───────┘
        │                        │
        ▼                        ▼
┌───────────────┐        ┌───────────────┐
│  Core Services│        │    Modules    │ (Módulos independientes de negocio)
│  (Logger, DB) │        │ (Booking, etc)│
└───────────────┘        └───────────────┘
```

---

## Lo que Hooma HACE vs lo que Hooma NO HACE

| Hooma HACE | Hooma NO HACE |
|---|---|
| Autocargar clases bajo el estándar PSR-4 de forma automatizada. | Modificar las tablas nativas de WordPress de forma oculta. |
| Proveer wrappers limpios que encapsulan y securizan WordPress. | Reemplazar hooks nativos (`add_action`) cuando no es necesario. |
| Agrupar y aislar configuraciones de módulos para optimizar BD. | Tomar decisiones sobre cómo debes pintar tu frontend de WordPress. |
| Forzar el desacoplamiento mediante un bus de Eventos integrado. | Convertirse en un ORM complejo y pesado de mantener. |
