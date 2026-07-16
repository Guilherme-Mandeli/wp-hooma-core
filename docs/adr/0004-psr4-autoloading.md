# ADR 0004: Autoloading basado en PSR-4

*Fecha: 15 de Julio de 2026*

---

## Contexto y Problema
En WordPress, es común utilizar llamadas manuales `require_once` o `include` para cargar archivos de clases y funciones secundarias.
En Hooma Core, esto presenta problemas graves:
1.  **Dificultad de Mantenimiento**: Cada cambio de ruta física requiere modificar múltiples llamadas de inclusión.
2.  **Consumo Innecesario**: PHP carga archivos en memoria que tal vez nunca se ejecuten en la petición actual.

---

## Alternativas Consideradas

### Opción A: Carga manual via `require_once`
- *Pros*: Cero configuración inicial.
- *Contras*: Código muy sucio e ineficiente.

### Opción B: Integrar Composer a nivel global
- *Pros*: Estándar de la industria.
- *Contras*: Obliga a los desarrolladores a ejecutar `composer install` para correr el core; añade peso e infraestructura externa innecesaria.

### Opción C: Autocargador ligero nativo compatible con PSR-4 (Seleccionada)
- *Pros*: Sin dependencias externas, ligero, rápido y compatible con las herramientas modernas de edición (IDE autocomplete).

---

## Decisión Tomada
Escribir una clase autocargadora nativa ligera (`Hooma_Autoloader`) compatible con el estándar PSR-4 de PHP. Registrará los namespaces del Core (`Hooma\Core\`) y de los módulos dinámicos (`HoomaModules\`).

---

## Consecuencias
- **Positivas**: Cero llamadas manuales `require_once`. Carga óptima en memoria bajo demanda y código de módulos limpio compatible con estándares PSR.
- **Negativas**: Obliga a los desarrolladores de módulos a organizar físicamente sus clases bajo directorios y usar nombres de archivo que coincidan exactamente con el nombre de la clase.
