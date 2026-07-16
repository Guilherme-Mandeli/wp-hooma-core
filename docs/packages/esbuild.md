# esbuild Package

esbuild es un empaquetador y minificador de JavaScript y CSS extremadamente rápido escrito en Go. En Hooma Core, esbuild se integra como un Package de tipo binario (`binary`) para compilar assets dinámicamente sin necesidad de Node.js en el servidor de producción.

## 1. ¿Qué es este Package?
Este paquete encapsula el binario ejecutable de esbuild para la plataforma del servidor.

- **Identificador**: `esbuild`
- **Tipo**: `binary`
- **Puntos de entrada**:
  - `bin`: `bin/esbuild.exe` (Windows) o `bin/esbuild` (Linux/macOS)

---

## 2. ¿Cuándo debería usarlo?
Deberías utilizar este Package cuando:
- Tengas un módulo con archivos JavaScript modernos (ES6+, JSX, TypeScript) o hojas de estilo (CSS con imports/nesting) y necesites compilar, empaquetar y minificar todo al vuelo.
- Desees implementar un modo de compilación en tiempo real (watch) en el servidor de desarrollo sin depender de herramientas locales de Node.js.
- Quieras automatizar la distribución optimizada de los recursos estáticos del módulo al activarlo o guardarlo.

---

## 3. Compatibilidad con Servicios de Hooma
Este paquete interactúa de forma directa con los siguientes servicios del Core:

- **Build Service** (`Hooma::build()` / personalizado): Para procesar y compilar colecciones de archivos JS/CSS automáticamente.
- **Filesystem Service** (`Hooma::filesystem()`): Para validar la presencia de archivos fuentes y escribir los binarios compilados de forma segura en las carpetas públicas de assets.

---

## 4. Estructura de Carpetas Recomendada

Coloca el paquete en tu servidor WordPress bajo la siguiente estructura:

```text
wp-content/
└── hooma/
    └── packages/
        └── esbuild/
            ├── manifest.json
            ├── README.md
            ├── bin/
            │   └── esbuild       <-- El ejecutable correspondiente a tu S.O. (ej. Linux/macOS)
            └── examples/
                └── compile/
                    ├── index.php <-- Código PHP de ejemplo de compilación
                    └── app.js    <-- Archivo JS de origen de ejemplo
```

### Contenido de `manifest.json`:
```json
{
    "name": "esbuild",
    "version": "0.20.1",
    "type": "binary",
    "description": "esbuild compiler binary",
    "author": "Evan Wallace",
    "license": "MIT",
    "homepage": "https://esbuild.github.io",
    "documentation": "https://esbuild.github.io/getting-started/",
    "compatibility": ["build"],
    "entries": {
        "bin": "bin/esbuild"
    }
}
```

---

## 5. ¿Cómo se ejecuta con Hooma?

Para compilar un archivo JS desde tu Módulo, obtén la ruta física del binario mediante la API de Hooma Core y ejecútala usando la consola del sistema en PHP:

```php
// Comprobar si el binario de esbuild está disponible
if (Hooma::packages()->exists('esbuild')) {
    $esbuild = Hooma::packages()->get('esbuild');
    $esbuild_bin = $esbuild->get_entry_path('bin');

    // Rutas de entrada y salida
    $input_file  = HOOMA_MODULES_PATH . 'mi-modulo/assets/src/app.js';
    $output_file = HOOMA_MODULES_PATH . 'mi-modulo/assets/dist/app.min.js';

    // Construir comando (compilar, minificar y empaquetar)
    $cmd = sprintf(
        '%s %s --bundle --minify --outfile=%s 2>&1',
        escapeshellarg($esbuild_bin),
        escapeshellarg($input_file),
        escapeshellarg($output_file)
    );

    // Ejecutar el compilador
    exec($cmd, $output, $result_code);

    if ($result_code === 0) {
        // Compilación exitosa
        Hooma::logger()->info('esbuild: Compilación finalizada correctamente.');
    } else {
        // Error de compilación
        $error_message = implode("\n", $output);
        Hooma::logger()->error('esbuild Falló con el mensaje: ' . $error_message);
    }
}
```

---

## 6. Buenas Prácticas
1. **Permisos de Ejecución**: En sistemas Linux/macOS, asegúrate de que el archivo binario tenga permisos de ejecución asignados (`chmod +x bin/esbuild`), de lo contrario PHP lanzará un error de denegación de permisos al intentar invocar `exec()`.
2. **Evitar Ejecuciones Repetitivas**: Ejecutar procesos externos en el servidor mediante `exec` es una tarea costosa para la CPU. Realiza la compilación solo al guardar la configuración, durante la activación del Módulo, o detectando cambios mediante una lógica de hashing; nunca lo ejecutes en cada recarga de página ordinaria del usuario.
3. **Escapar Argumentos**: Utiliza siempre `escapeshellarg()` para todos los parámetros pasados al binario de esbuild, protegiendo al servidor contra inyecciones de comandos accidentales o maliciosas.

---

## 7. Recursos Adicionales
- [Documentación oficial de esbuild](https://esbuild.github.io)
- [API de esbuild en línea de comandos (CLI)](https://esbuild.github.io/api/#cli)
