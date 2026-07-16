<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Constants
 */

define( 'HOOMA_VERSION', '1.0.0' );
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
