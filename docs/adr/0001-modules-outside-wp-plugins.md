# ADR 0001: Módulos fuera del directorio plugins/

*Fecha: 15 de Julio de 2026*

---

## Contexto y Problema
En WordPress, cualquier extensión de funcionalidad suele empaquetarse e instalarse como un plugin individual dentro del directorio `wp-content/plugins/`.
Sin embargo, para una plataforma SaaS o aplicación empresarial modular, esto genera:
1.  **Fragmentación**: Decenas de plugins listados en el panel global de WordPress confundiendo al administrador.
2.  **Falta de Control**: El usuario puede desactivar componentes críticos de negocio que dependen de otros.
3.  **Dificultad de Despliegue**: Actualizar la plataforma modular requiere actualizar decenas de plugins independientes.

---

## Alternativas Consideradas

### Opción A: Mantener todo como Plugins de WordPress independientes
- *Pros*: Diseño nativo de WordPress.
- *Contras*: Dificultad de centralizar la gestión de actualizaciones e imposibilidad de gobernar el orden de carga y ciclo de activación coordinado.

### Opción B: Crear un único plugin gigante (Monolito)
- *Pros*: Un único punto de control.
- *Contras*: Imposible desactivar módulos específicos para clientes que no los necesitan; alto acoplamiento.

### Opción C: Carga dinámica desde la carpeta de Uploads (Seleccionada)
- *Ubicación*: `wp-content/uploads/hooma/modules/`
- *Pros*: Separación física limpia, control total del framework sobre el descubrimiento y activación de los módulos, listado administrativo unificado y centralizado dentro de Hooma Core.

---

## Decisión Tomada
Mover todos los módulos de negocio a un directorio dedicado fuera del gestor de plugins de WordPress (`wp-content/uploads/hooma/modules/`). El framework Hooma Core será el único responsable de escanear, validar los metadatos y activar/desactivar cada módulo mediante su propia base de datos.

---

## Consecuencias
- **Positivas**: Administración centralizada y limpia de cara al cliente. Desacoplamiento físico de funcionalidades.
- **Negativas**: Mayor dependencia del framework Hooma para la carga; las herramientas de depuración de WordPress estándar no ven los módulos como plugins independientes (se resuelven a través del autoloader del Core).
