<?php
/**
 * Plugin Name: Hooma Core
 * Description: Container de serviços para módulos de negócios personalizados da Hooma.
 * Version: 1.1.260717
 * Author: Hooma
 * Text Domain: hooma
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
	exit;
}

// Load Configuration
require_once plugin_dir_path(__FILE__) . 'config/constants.php';
require_once plugin_dir_path(__FILE__) . 'includes/contracts/interface-hooma-module-lifecycle.php';

/**
 * Initialize the plugin
 */
function hooma_init()
{
	if (defined('WP_DEBUG') && WP_DEBUG) {
		error_log('Hooma Core: hooma_init called.');
	}
	// Load Text Domain
	load_plugin_textdomain(
		'hooma',
		false,
		dirname(plugin_basename(__FILE__)) . '/languages'
	);

	// Autoloader
	require_once HOOMA_PATH . 'includes/class-hooma-autoloader.php';

	$autoloader = new Hooma_Autoloader();
	$autoloader->register();

	// Load Global Facade
	require_once HOOMA_PATH . 'includes/Hooma.php';

	// Initialize Service Container & Provider
	$container = new \Hooma\Core\ServiceContainer();
	\Hooma::set_container($container);

	$service_provider = new \Hooma\Core\ServiceProvider($container);
	$service_provider->register();

	// UI Kit
	require_once HOOMA_PATH . 'includes/class-hooma-ui.php';

	// Loader
	require_once HOOMA_PATH . 'includes/class-hooma-loader.php';
	$loader = new Hooma_Loader();
	$loader->run();

	if (is_admin()) {
		require_once HOOMA_PATH . 'admin/class-hooma-admin.php';

		$admin = new Hooma_Admin();
		$admin->run();

		// Inicializar y registrar el renderizador de notificaciones administrativas
		$notices_renderer = new \Hooma\Core\Services\Notices\NoticeRenderer($container->get('notices'));
		$notices_renderer->register();
	}
}
add_action('plugins_loaded', 'hooma_init');

// Register activation hook
function hooma_activate()
{
	require_once plugin_dir_path(__FILE__) . 'config/constants.php';

	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();
	global $wp_filesystem;

	if (defined('FS_CHMOD_DIR')) {
		$chmod = FS_CHMOD_DIR;
	} else {
		$chmod = 0755;
	}

	// Create parent hooma folder first
	$hooma_dir = WP_CONTENT_DIR . '/hooma/';
	if (!$wp_filesystem->exists($hooma_dir)) {
		$wp_filesystem->mkdir($hooma_dir, $chmod);
	}

	// Prioritize and create subfolders inside wp-content/hooma/
	$new_modules_path = $hooma_dir . 'modules/';
	$packages_path    = $hooma_dir . 'packages/';

	if (!$wp_filesystem->exists($new_modules_path)) {
		$wp_filesystem->mkdir($new_modules_path, $chmod);
	}

	if (!$wp_filesystem->exists($packages_path)) {
		$wp_filesystem->mkdir($packages_path, $chmod);
	}
}
register_activation_hook(__FILE__, 'hooma_activate');
