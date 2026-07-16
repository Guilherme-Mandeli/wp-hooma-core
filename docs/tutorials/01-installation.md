# Tutorial Capítulo 01: Instalación y Estructura

*Disponible desde v0.1*

Este primer capítulo te enseña cómo configurar el entorno y la estructura de directorios básica para empezar a desarrollar módulos con Hooma Core.

---

## Prerrequisitos
Se asume que dispones de:
- Un entorno de desarrollo local con WordPress activo (ej. LocalWP, Laragon o Docker).
- Acceso de escritura a la carpeta de uploads de WordPress.

---

## 1. El directorio de desarrollo
En Hooma, los módulos de negocio no viven en el directorio habitual `/plugins/`, sino en la carpeta de uploads dinámicos:
`wp-content/uploads/hooma/modules/`

1.  Navega a la carpeta `/wp-content/uploads/` de tu instalación de WordPress.
2.  Si no existen, crea las carpetas `hooma/` y dentro de ella `modules/`.

---

## 2. Creando la carpeta de tu primer módulo
Vamos a crear un módulo de ejemplo llamado **`mi-primer-modulo`**.

1.  Crea la carpeta `mi-primer-modulo/` en el directorio de módulos:
    `wp-content/uploads/hooma/modules/mi-primer-modulo/`
2.  Dentro de la carpeta, crea el archivo principal obligatorio de entrada: `index.php`.

En el siguiente capítulo aprenderemos a rellenar este archivo para que Hooma Core pueda descubrir y activar tu módulo.

---

Siguiente capítulo: **[Capítulo 02: Tu Primer Módulo](02-first-module.md)**
