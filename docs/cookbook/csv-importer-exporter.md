# Cookbook: Importador y Exportador de CSV

*Disponible desde v0.3*

Esta receta proporciona el código necesario para leer y generar archivos CSV planos estructurando los flujos de lectura y escritura a través de los servicios oficiales de Hooma.

---

## Solución Completa

```php
<?php

namespace HoomaModules\MiModulo\Services;

use Hooma;

class BookingCsvManager
{
    /**
     * Exporta reservas a un archivo CSV en el directorio de descargas.
     */
    public function exportarReservas(): string
    {
        $upload_dir = wp_upload_dir();
        $target_path = wp_normalize_path($upload_dir['basedir'] . '/hooma/export/reservas.csv');

        // Obtener datos
        $reservas = Hooma::database()->get_results(
            "SELECT id, cliente_id, fecha, monto FROM %swp_reservas"
        );

        // Generar estructura CSV
        $output = "ID,ClienteID,Fecha,Monto\n";
        foreach ($reservas as $reserva) {
            $output .= sprintf(
                "%d,%d,%s,%.2f\n",
                $reserva['id'],
                $reserva['cliente_id'],
                $reserva['fecha'],
                $reserva['monto']
            );
        }

        // Guardar archivo usando el Filesystem Service
        Hooma::filesystem()->write($target_path, $output);

        return $target_path;
    }

    /**
     * Lee e importa datos desde un archivo CSV.
     */
    public function importarReservas(string $path): int
    {
        if (!Hooma::filesystem()->exists($path)) {
            throw new \RuntimeException("El archivo de importación no existe.");
        }

        // Leer contenido usando el Filesystem Service
        $content = Hooma::filesystem()->read($path);
        $lines = explode("\n", str_replace("\r", "", $content));
        
        $imported = 0;
        foreach ($lines as $index => $line) {
            if ($index === 0 || empty($line)) {
                continue; // Omitir cabecera o líneas vacías
            }

            $data = str_getcsv($line);
            
            // Insertar datos en BD
            Hooma::database()->insert(
                Hooma::database()->prefix() . 'wp_reservas',
                array(
                    'cliente_id' => (int) $data[1],
                    'fecha'      => sanitize_text_field($data[2]),
                    'monto'      => (float) $data[3]
                )
            );
            $imported++;
        }

        return $imported;
    }
}
```
