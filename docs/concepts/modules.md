# Concepto: Módulos

*Disponible desde v0.1*

En Hooma, un **Módulo** es una unidad funcional autocontenida que encapsula una lógica de negocio específica (por ejemplo, gestión de reservas, sincronización con APIs de CRM, pasarelas de pago, etc.).

---

## ¿Qué es un Módulo?

A diferencia de WordPress, donde las funciones de distintas características se mezclan en el archivo `functions.php`, en Hooma cada característica vive dentro de su propio directorio de módulo. 

Un módulo tiene las siguientes características:
1.  **Aislamiento**: Todo su código fuente, assets (CSS/JS) y configuración vive bajo su propia carpeta en `wp-content/uploads/hooma/modules/` (o la ruta configurada).
2.  **Ciclo de vida**: Puede ser activado, desactivado o actualizado de forma independiente desde la administración de Hooma sin afectar el funcionamiento de otros módulos.
3.  **Namespace Único**: Todas sus clases PHP deben estar bajo el namespace raíz `HoomaModules\{NombreModulo}`.

---

## Límites de Negocio: Qué debe (y qué NO debe) hacer un módulo

Para garantizar la estabilidad a largo plazo de la plataforma, los módulos deben adherirse a estrictos límites de diseño:

### ✔ Lo que un Módulo DEBE hacer:
- **Consumir Services**: Utilizar siempre la fachada `Hooma::` para interactuar con la base de datos, logs, peticiones HTTP y caché.
- **Declarar Metadatos**: Incluir un bloque de metadatos estandarizado en su archivo principal (`index.php` o `[slug].php`) para que el framework pueda descubrirlo.
- **Comunicarse por Eventos**: Si necesita que otro módulo reaccione a sus acciones, debe disparar un evento (ej. `booking.created`) en lugar de llamar directamente a clases externas.

### ❌ Lo que un Módulo NO DEBE hacer:
- **Instanciar clases de otros módulos**: Esto genera dependencias rígidas. Si desactivas el módulo de facturación, el módulo de reservas se romperá si hay acoplamiento directo.
- **Llamar directamente a APIs globales de WordPress para persistencia**: Evita el uso directo de `update_option()`, `wp_remote_get()` o escrituras crudas sobre `$wpdb` sin sanitizar.
- **Escribir HTML directamente en sus controladores**: Mantén separada la lógica de negocio del renderizado visual.
