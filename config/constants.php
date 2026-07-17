<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Constants
 */

// Version definition (dynamically read from hooma.php header)
$hooma_version = '1.1.260717'; // Fallback

$plugin_file = plugin_dir_path( dirname( __FILE__ ) ) . 'hooma.php';

if ( function_exists( 'get_file_data' ) ) {
	$plugin_data = get_file_data( $plugin_file, array( 'Version' => 'Version' ) );
	if ( ! empty( $plugin_data['Version'] ) ) {
		$hooma_version = $plugin_data['Version'];
	}
} elseif ( file_exists( $plugin_file ) ) {
	$file_data = file_get_contents( $plugin_file, false, null, 0, 8192 );
	if ( preg_match( '/^[ \t\/*#@]*Version:(.*)$/mi', $file_data, $match ) ) {
		$hooma_version = trim( $match[1] );
	}
}
define( 'HOOMA_VERSION', $hooma_version );
define( 'HOOMA_MODULES_NAMESPACE', 'HoomaModules' );

// Path constants
// dirname( __FILE__ ) is .../config
// plugin_dir_path( dirname( __FILE__ ) ) gets the parent directory of config with a trailing slash -> .../Hooma/
if ( ! defined( 'HOOMA_PATH' ) ) {
	define( 'HOOMA_PATH', plugin_dir_path( dirname( __FILE__ ) ) );
}

if ( ! defined( 'HOOMA_URL' ) ) {
	define( 'HOOMA_URL', plugin_dir_url( dirname( dirname( __FILE__ ) ) . '/hooma.php' ) );
}

// Modules Path
if ( ! defined( 'HOOMA_MODULES_PATH' ) ) {
	$new_modules_path = WP_CONTENT_DIR . '/hooma/modules/';
	$old_modules_path = WP_CONTENT_DIR . '/hooma-modules/';
	define( 'HOOMA_MODULES_PATH', ( is_dir( $old_modules_path ) && ! is_dir( $new_modules_path ) ) ? $old_modules_path : $new_modules_path );
}

if ( ! defined( 'HOOMA_MODULES_URL' ) ) {
	$new_modules_url  = content_url( 'hooma/modules/' );
	$old_modules_url  = content_url( 'hooma-modules/' );
	$new_modules_path = WP_CONTENT_DIR . '/hooma/modules/';
	$old_modules_path = WP_CONTENT_DIR . '/hooma-modules/';
	define( 'HOOMA_MODULES_URL', ( is_dir( $old_modules_path ) && ! is_dir( $new_modules_path ) ) ? $old_modules_url : $new_modules_url );
}

// Packages Path
if ( ! defined( 'HOOMA_PACKAGES_PATH' ) ) {
	define( 'HOOMA_PACKAGES_PATH', WP_CONTENT_DIR . '/hooma/packages/' );
}

if ( ! defined( 'HOOMA_PACKAGES_URL' ) ) {
	define( 'HOOMA_PACKAGES_URL', content_url( 'hooma/packages/' ) );
}
