# Cookbook: Página de Administración Avanzada con Hooma UI

*Disponible desde v0.1*

Esta receta proporciona la plantilla recomendada para maquetar un panel de administración del módulo con navegación Ajax, múltiples pestañas y formularios consistentes con el UI Kit.

---

## Solución Completa

### 1. La Vista HTML (`views/admin-panel.php`)
```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

// Obtener la pestaña activa (por defecto es 'general')
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
?>
<div class="hooma-ui-wrapper">
    <!-- Cabecera Oficial del UI Kit -->
    <?php echo Hooma_UI::header('Configuración de Reservas', 'v1.2.0'); ?>

    <!-- Pestañas de Navegación del UI Kit -->
    <?php echo Hooma_UI::tabs(array(
        'general'     => 'Ajustes Generales',
        'integracion' => 'Integración API',
        'auditoria'   => 'Registro de Auditoría'
    ), $active_tab); ?>

    <!-- Cuerpo del Contenedor del UI Kit -->
    <div class="hooma-ui-container">
        <?php if ($active_tab === 'general') : ?>
            <h3>Ajustes Generales de Reservas</h3>
            <form method="POST" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="moneda">Moneda Base</label></th>
                        <td>
                            <select id="moneda" name="moneda">
                                <option value="USD">Dólar Estadounidense ($)</option>
                                <option value="EUR">Euro (€)</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Guardar Cambios'); ?>
            </form>
        <?php elseif ($active_tab === 'integracion') : ?>
            <h3>Ajustes de Integración Externa</h3>
            <!-- Contenido de la pestaña 2 -->
        <?php endif; ?>
    </div>

    <!-- Pie de Página -->
    <?php echo Hooma_UI::footer('Soporte: info@tuempresa.com'); ?>
</div>
```
*Nota: Hooma_UI gestionará automáticamente la adición del parámetro `&tab=` en las URLs de las pestañas para facilitar la recarga limpia.*
