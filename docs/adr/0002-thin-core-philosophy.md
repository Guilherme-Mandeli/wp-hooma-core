# ADR 0002: Filosofía Thin Core y Servicios Modulares

*Fecha: 15 de Julio de 2026*

---

## Contexto y Problema
Al construir frameworks o sistemas de soporte sobre WordPress, es habitual caer en la tentación de acumular utilidades pesadas, ORMs propios y controladores complejos en el núcleo (Core). Con el tiempo, esto provoca un núcleo sobredimensionado, lento y difícil de actualizar sin generar cambios disruptivos (breaking changes) en los módulos de negocio.

---

## Alternativas Consideradas

### Opción A: Fat Core (Núcleo con lógica de negocio y utilidades pesadas)
- *Pros*: Todo está listo dentro del framework.
- *Contras*: Alto consumo de memoria y rigidez evolutiva.

### Opción B: Thin Core (Núcleo mínimo con registro perezoso) (Seleccionada)
- *Pros*: Máximo rendimiento, consumo de memoria mínimo, e independencia evolutiva. Los servicios se instancian en memoria bajo demanda (lazy).

---

## Decisión Tomada
Adoptar la filosofía **Thin Core**. El núcleo de Hooma Core se limitará estrictamente a proveer el pegamento de infraestructura: el cargador automático, el contenedor de servicios y el renderizador visual básico (UI Kit). Todas las demás funcionalidades se delegan a módulos o a servicios perezosos del contenedor.

---

## Consecuencias
- **Positivas**: Tiempos de respuesta óptimos. Carga ligera en el frontend de WordPress. Mantenibilidad del Core muy sencilla.
- **Negativas**: Los desarrolladores de módulos deben escribir su propia lógica de base de datos o controladores en lugar de heredar un gran volumen de clases abstractas predefinidas del Core.
