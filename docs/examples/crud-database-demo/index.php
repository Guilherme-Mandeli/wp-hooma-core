<?php
/**
 * Module Name: Ejemplo CRUD Database
 * Description: Demostración de creación de tablas personalizadas, inserción y listado de datos con Hooma Core.
 * Version: 1.0.0
 * Author: Technical Writer Hooma
 * Text Domain: hooma-db-demo
 * 
 * Nota: Este archivo es meramente ilustrativo para servir como referencia y ejemplo.
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Hook de activación para crear la tabla de datos
add_action('hooma_module_activated_crud-database-demo', function() {
    $db = Hooma::database();
    $tabla = $db->prefix() . 'hooma_demo_items';

    $sql = "CREATE TABLE IF NOT EXISTS `{$tabla}` (
        `id` bigint(20) NOT NULL AUTO_INCREMENT,
        `nombre` varchar(100) NOT NULL,
        `cantidad` int(11) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $db->query($sql);
});

// 2. Registro del menú de administración
add_action('admin_menu', function() {
    add_menu_page(
        'DB CRUD Demo',
        'DB CRUD Demo',
        'manage_options',
        'hooma-db-demo',
        'hooma_db_demo_render_page'
    );
});

/**
 * Renderiza la pantalla y procesa el CRUD.
 */
function hooma_db_demo_render_page() {
    $db = Hooma::database();
    $table = $db->prefix() . 'hooma_demo_items';

    // A) Procesar Inserción
    if (isset($_POST['demo_submit'])) {
        $nombre = sanitize_text_field($_POST['nombre']);
        $cantidad = (int) $_POST['cantidad'];

        $db->insert($table, array(
            'nombre'   => $nombre,
            'cantidad' => $cantidad
        ));
        Hooma::notices()->success('Elemento guardado con éxito en la base de datos.');
    }

    // B) Procesar Eliminación
    if (isset($_GET['delete_id'])) {
        $delete_id = (int) $_GET['delete_id'];
        $db->delete($table, array('id' => $delete_id));
        Hooma::notices()->success('Elemento eliminado correctamente.');
    }

    // C) Obtener datos
    $items = $db->get_results("SELECT * FROM {$table} ORDER BY id DESC");
    ?>
    <div class="hooma-ui-wrapper">
        <?php echo Hooma_UI::header('CRUD Database Demo', 'v1.0.0'); ?>

        <div class="hooma-ui-container">
            <h2>Registrar Nuevo Elemento</h2>
            <form method="POST" action="">
                <table class="form-table">
                    <tr>
                        <th><label for="nombre">Nombre</label></th>
                        <td><input type="text" id="nombre" name="nombre" class="regular-text" required /></td>
                    </tr>
                    <tr>
                        <th><label for="cantidad">Cantidad</label></th>
                        <td><input type="number" id="cantidad" name="cantidad" class="small-text" required /></td>
                    </tr>
                </table>
                <?php submit_button('Insertar Registro', 'primary', 'demo_submit'); ?>
            </form>

            <hr />

            <h2>Listado de Elementos Registrados</h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Cantidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)) : ?>
                        <tr>
                            <td colspan="4">No hay registros en la base de datos.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($items as $item) : ?>
                            <tr>
                                <td><?php echo esc_html($item['id']); ?></td>
                                <td><?php echo esc_html($item['nombre']); ?></td>
                                <td><?php echo esc_html($item['cantidad']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=hooma-db-demo&delete_id=' . $item['id'])); ?>" class="button button-link-delete">
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo Hooma_UI::footer('Ejemplo de Base de Datos Estructurado'); ?>
    </div>
    <?php
}
