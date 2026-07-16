<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module lifecycle contract.
 */
interface Hooma_Module_Lifecycle_Interface {

	/**
	 * Runs on first module installation.
	 *
	 * @param array $manifest Module manifest data.
	 * @return void
	 */
	public static function install( array $manifest );

	/**
	 * Runs on module update.
	 *
	 * @param array $old_manifest Previous version data.
	 * @param array $new_manifest New version data.
	 * @return void
	 */
	public static function update( array $old_manifest, array $new_manifest );

	/**
	 * Runs on module removal.
	 *
	 * @param array $manifest Module manifest data.
	 * @return void
	 */
	public static function uninstall( array $manifest );
}
