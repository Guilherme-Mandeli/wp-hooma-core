# Concepto: UI Kit (Interfaz Consistente)

*Disponible desde v0.1*

La consistencia visual en el panel de administración de WordPress es uno de los mayores desafíos del desarrollo. Cada plugin suele cargar sus propios estilos, fuentes y estructuras, convirtiendo la administración del sitio en una experiencia confusa y desorganizada para el usuario.

El **UI Kit** de Hooma Core resuelve este problema unificando la maquetación y la visualización de la interfaz administrativa de todos los módulos bajo una única guía de estilos consistente, moderna y responsiva.

---

## Filosofía del UI Kit

La filosofía visual del UI Kit se basa en tres pilares fundamentales:

1.  **Cero redundancia CSS**: Los módulos no deben cargar hojas de estilos pesadas ni duplicadas para pintar formularios, tablas o botones estándar. Deben reutilizar las clases y componentes del Core.
2.  **Consistencia de Marca**: Todos los módulos de Hooma comparten la misma paleta de colores, tipografía, espaciados y estados visuales (hover, focus, disabled).
3.  **Encapsulado Semántico**: El UI Kit provee envoltorios semánticos para maquetar paneles rápidamente (Header, Tabs, Containers y Footers) sin tener que escribir HTML repetitivo en cada pantalla de módulo.

---

## Estructura Visual de un Panel Administrativo

El UI Kit estructura los paneles bajo una jerarquía visual predecible y responsiva:

```
┌────────────────────────────────────────────────────────┐
│                        HEADER                          │ (Título, Versión y Logo)
├────────────────────────────────────────────────────────┤
│     TAB 1     │     TAB 2     │    TAB 3     │  ...    │ (Navegación fluida por Ajax)
├────────────────────────────────────────────────────────┤
│                                                        │
│                       CONTAINER                        │ (Formularios, Tablas de datos)
│                                                        │
├────────────────────────────────────────────────────────┤
│                        FOOTER                          │ (Créditos, Licencias y Soporte)
└────────────────────────────────────────────────────────┘
```

---

## Cuándo utilizar el UI Kit

- **Formularios de Configuración**: Para pintar campos, inputs, selects e interruptores con diseño moderno y validación consistente.
- **Tablas de Listados**: Para pintar tablas de información con cabeceras limpias, paginación y alineación preestablecida.
- **Páginas de Administración del Módulo**: Al maquetar las vistas de administración de tus módulos (vistas asociadas al menú administrativo de Hooma).
