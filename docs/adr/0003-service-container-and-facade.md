# ADR 0003: Contenedor de Servicios y Fachada Global Estática

*Fecha: 15 de Julio de 2026*

---

## Contexto y Problema
En el desarrollo desacoplado, la inyección de dependencias (DI) es fundamental para evitar acoplar clases entre sí. Sin embargo, en WordPress (que es principalmente procedimental), forzar la inyección manual de dependencias en constructores de controladores de módulos de negocio genera un código repetitivo y complejo para el desarrollador común de WordPress.

---

## Alternativas Consideradas

### Opción A: Inyección de Dependencias Manual en constructores
- *Pros*: Máximo desacoplamiento teórico.
- *Contras*: Curva de aprendizaje empinada y mucho código boilerplate en los módulos.

### Opción B: Uso de variables globales o constantes
- *Pros*: Fácil acceso.
- *Contras*: Imposible mockear/testear, colisiones de nombres y código sucio.

### Opción C: Fachada estática sobre Service Container (Seleccionada)
- *Pros*: Sintaxis limpia y directa (`Hooma::settings()`), instanciación perezosa interna en el contenedor y posibilidad de intercambiar implementaciones por debajo de forma transparente.

---

## Decisión Tomada
Registrar un contenedor de servicios perezoso (`ServiceContainer`) y proveer una fachada estática unificada global llamada `Hooma`. Los módulos consumen los servicios estáticamente a través de la fachada, la cual delega la resolución de dependencias al contenedor interno.

---

## Consecuencias
- **Positivas**: Sintaxis elegante, mantenibilidad de código óptima y facilidad de testeo mediante mocks del contenedor de servicios.
- **Negativas**: Mayor dependencia de la clase global estática `Hooma` en los módulos.
