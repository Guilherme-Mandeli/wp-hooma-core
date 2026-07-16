# Cookbook: Compartir Datos entre Módulos de forma segura

*Disponible desde v0.3*

Esta receta muestra cómo compartir variables globales de configuración en memoria entre distintos módulos en tiempo de ejecución de forma limpia y tipada, previniendo el uso de variables globales de PHP (`$GLOBALS`) o constantes dinámicas.

---

## Solución Completa

### 1. El Módulo Proveedor de Datos
El módulo que inicializa la configuración guarda los datos en memoria en el servicio `Config` de Hooma Core durante el hook `hooma_init`:

```php
// index.php de Módulo Proveedor
add_action('hooma_init', function() {
    // Definimos configuraciones globales compartidas para la petición actual
    Hooma::config()->set('empresa.nombre', 'Hooma Business SL');
    Hooma::config()->set('empresa.soporte_email', 'soporte@hooma.com');
    Hooma::config()->set('empresa.moneda_default', 'EUR');
});
```

---

### 2. El Módulo Consumidor
Cualquier otro módulo activo puede recuperar estas configuraciones en cualquier punto de su ejecución utilizando la notación por puntos:

```php
// En el módulo de Facturación:
class InvoiceGenerator
{
    public function renderInvoiceHeader()
    {
        // Recuperar valores de configuración global compartida
        $nombre_empresa = Hooma::config()->get('empresa.nombre', 'Empresa Genérica');
        $email_contacto = Hooma::config()->get('empresa.soporte_email');

        echo "Factura emitida por: " . esc_html($nombre_empresa);
        echo "Contacto: " . esc_html($email_contacto);
    }
}
```
