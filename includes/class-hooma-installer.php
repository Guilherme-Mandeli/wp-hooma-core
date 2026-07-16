<?php

if (!defined('ABSPATH')) {
	exit;
}

class Hooma_Installer
{

	/**
	 * Install a module from a ZIP file.
	 *
	 * @param array $file $_FILES['module_zip'] or similar.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	public function install($file)
	{
		// 1. Basic PHP Upload Validation
		if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
			return new WP_Error('upload_failed', __('File upload failed.', 'hooma'));
		}

		if ($file['error'] !== UPLOAD_ERR_OK) {
			return new WP_Error('upload_error', __('Upload error: ', 'hooma') . $file['error']);
		}

		// 2. Validate MIME Type (simple check)
		$file_type = wp_check_filetype($file['name']);
		if ($file_type['ext'] !== 'zip') {
			return new WP_Error('invalid_type', __('Only .zip files are allowed.', 'hooma'));
		}

		// 3. Prepare Environment
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		WP_Filesystem();
		global $wp_filesystem;

		// 4. Unzip to Temp
		$temp_dir = get_temp_dir() . 'hooma-install-' . uniqid() . '/';
		if (!$wp_filesystem->exists($temp_dir)) {
			$wp_filesystem->mkdir($temp_dir);
		}

		$unzip_result = unzip_file($file['tmp_name'], $temp_dir);
		if (is_wp_error($unzip_result)) {
			$wp_filesystem->delete($temp_dir, true);
			return $unzip_result;
		}

		// 5. Validation Logic
		// Returns array with keys: 'path' (root), 'main_file', 'headers'
		$validation = $this->validate_module($temp_dir);
		if (is_wp_error($validation)) {
			$wp_filesystem->delete($temp_dir, true); // Clean up
			return $validation;
		}

		// The validated module directory (the folder inside temp)
		$source = $validation['path'];
		$new_headers = $validation['headers'];

		$dirname = basename($source); // Module folder name
		
		$target_modules_dir = WP_CONTENT_DIR . '/hooma/modules/';
		$can_use_new_path = false;
		if ($wp_filesystem->exists($target_modules_dir)) {
			$can_use_new_path = true;
		} else {
			$hooma_dir = WP_CONTENT_DIR . '/hooma/';
			if (!$wp_filesystem->exists($hooma_dir)) {
				$wp_filesystem->mkdir($hooma_dir);
			}
			if ($wp_filesystem->exists($hooma_dir)) {
				$wp_filesystem->mkdir($target_modules_dir);
				if ($wp_filesystem->exists($target_modules_dir)) {
					$can_use_new_path = true;
				}
			}
		}

		$destination = $can_use_new_path ? ($target_modules_dir . $dirname) : (HOOMA_MODULES_PATH . $dirname);

		// 6. Install or Update
		// Check for existing module
		if ($wp_filesystem->exists($destination)) {
			// Conflict Prevention: Do not delete temp dir yet.
			// Return error with data for the admin to handle confirmation.

			return new WP_Error(
				'folder_exists',
				sprintf(
					__('The module directory "%s" already exists.', 'hooma'),
					$dirname
				),
				array(
					'temp_dir' => $temp_dir,
					'module_slug' => $new_headers['HOOMA_MODULE_SLUG'],
					'destination' => $destination,
					'new_headers' => $new_headers
				)
			);

		} else {
			// New Install Flow
			return $this->finalize_install($temp_dir, false);
		}
	}

	/**
	 * Finalize installation (Move files and run hooks).
	 *
	 * @param string $temp_dir  Path to the unzipped module in temp.
	 * @param bool   $overwrite Whether to overwrite existing module.
	 * @return true|WP_Error
	 */
	public function finalize_install($temp_dir, $overwrite = false)
	{
		global $wp_filesystem;

		// Re-validate to get paths (safe check)
		$validation = $this->validate_module($temp_dir);
		if (is_wp_error($validation)) {
			$wp_filesystem->delete($temp_dir, true);
			return $validation;
		}

		$source = $validation['path'];
		$dirname = basename($source);
		$new_headers = $validation['headers'];

		$target_modules_dir = WP_CONTENT_DIR . '/hooma/modules/';
		$can_use_new_path = false;
		if ($wp_filesystem->exists($target_modules_dir)) {
			$can_use_new_path = true;
		} else {
			$hooma_dir = WP_CONTENT_DIR . '/hooma/';
			if (!$wp_filesystem->exists($hooma_dir)) {
				$wp_filesystem->mkdir($hooma_dir);
			}
			if ($wp_filesystem->exists($hooma_dir)) {
				$wp_filesystem->mkdir($target_modules_dir);
				if ($wp_filesystem->exists($target_modules_dir)) {
					$can_use_new_path = true;
				}
			}
		}

		$destination = $can_use_new_path ? ($target_modules_dir . $dirname) : (HOOMA_MODULES_PATH . $dirname);

		// Handle Overwrite
		$was_active = false;
		if ($wp_filesystem->exists($destination)) {
			if ($overwrite) {
				// Check if module is active and deactivate it
				$active_modules = get_option('hooma_active_modules', array());
				if (in_array($dirname, $active_modules)) {
					$was_active = true;
					$key = array_search($dirname, $active_modules);
					unset($active_modules[$key]);
					update_option('hooma_active_modules', array_values($active_modules));
				}

				// Uninstall old version logic could go here if we want to run uninstall hooks first
				// For now, let's just delete the files to allow clean copy
				$wp_filesystem->delete($destination, true);
			} else {
				$wp_filesystem->delete($temp_dir, true);
				return new WP_Error('folder_exists', __('Directory already exists and overwrite was not authorized.', 'hooma'));
			}
		}

		// Move files
		$copy_result = copy_dir($source, $destination);

		// Clean up temp
		$wp_filesystem->delete($temp_dir, true);

		if (is_wp_error($copy_result)) {
			return $copy_result;
		}

		if (!$copy_result) {
			return new WP_Error('copy_failed', __('Failed to move files to the modules directory.', 'hooma'));
		}

		// Lifecycle hook: Install
		$lifecycle_class = $this->resolve_lifecycle_class($new_headers['HOOMA_MODULE_SLUG'], $new_headers['HOOMA_MODULE_NAMESPACE']);

		if ($lifecycle_class) {
			$manifest = array(
				'slug' => $new_headers['HOOMA_MODULE_SLUG'],
				'namespace' => $new_headers['HOOMA_MODULE_NAMESPACE'],
				'version' => $new_headers['Version'],
				'headers' => $new_headers,
			);

			try {
				$lifecycle_class::install($manifest);
			} catch (Exception $e) {
				// Rollback install (optional, but good practice)
				// $wp_filesystem->delete( $destination, true );
				return new WP_Error('install_hook_failed', __('Installation hook error: ', 'hooma') . $e->getMessage());
			}
		}

		// Re-activate if it was active before overwrite
		if ($was_active) {
			$active_modules = get_option('hooma_active_modules', array());
			if (!in_array($dirname, $active_modules)) {
				$active_modules[] = $dirname;
				update_option('hooma_active_modules', array_values($active_modules));
			}
		}

		return true;
	}

	/**
	 * Uninstall a module.
	 *
	 * @param string $module_slug The slug of the module to uninstall.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	public function uninstall($module_slug)
	{
		$module_dir = HOOMA_MODULES_PATH . $module_slug;

		global $wp_filesystem;
		if (!$wp_filesystem) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if (!$wp_filesystem->exists($module_dir)) {
			return new WP_Error('module_not_found', __('Module not found.', 'hooma'));
		}

		// 1. Get info to attempt Lifecycle resolution
		// Since we are uninstalling, we might have issues if dependencies are broken, so be careful.
		$info = $this->get_module_info($module_dir);

		if (!is_wp_error($info)) {
			// Attempt to resolve Lifecycle
			// We need to know the namespace. Code might not be loaded.
			// Execution in sandbox to retrieve identity constants is the safest way to know the real namespace.

			ob_start();
			include_once $info['main_file'];
			ob_end_clean();

			// Note: If the file was already included (e.g. module was active), include_once returns true.
			// Constants should be available.

			if (defined('HOOMA_MODULE_SLUG') && defined('HOOMA_MODULE_NAMESPACE')) {
				if (HOOMA_MODULE_SLUG === $module_slug) {
					$lifecycle_class = $this->resolve_lifecycle_class(HOOMA_MODULE_SLUG, HOOMA_MODULE_NAMESPACE);

					if ($lifecycle_class) {
						try {
							$manifest = array(
								'slug' => HOOMA_MODULE_SLUG,
								'namespace' => HOOMA_MODULE_NAMESPACE,
								'version' => isset($info['headers']['Version']) ? $info['headers']['Version'] : '0.0.0',
								'headers' => $info['headers'],
							);
							$lifecycle_class::uninstall($manifest);
						} catch (Exception $e) {
							error_log('Hooma Uninstall Hook Error: ' . $e->getMessage());
						}
					}
				}
			}
		}

		// 2. Delete Files
		if (!$wp_filesystem->delete($module_dir, true)) {
			return new WP_Error('delete_failed', __('Failed to remove module files.', 'hooma'));
		}

		return true;
	}
	private function validate_module($temp_dir)
	{
		global $wp_filesystem;

		// Regra 1: Validação de Estrutura de Diretório (Single Root)
		$items = $wp_filesystem->dirlist($temp_dir);
		if (!$items) {
			return new WP_Error('empty_zip', __('The package is empty.', 'hooma'));
		}

		$dirs = array();
		$files_at_root = array();

		foreach ($items as $name => $info) {
			if ($name === '__MACOSX' || $name === '.' || $name === '..')
				continue;
			if ($info['type'] === 'd') {
				$dirs[] = $name;
			} elseif ($info['type'] === 'f') {
				$files_at_root[] = $name;
			}
		}

		if (count($dirs) !== 1 || !empty($files_at_root)) {
			return new WP_Error('invalid_structure', __('Invalid structure: The ZIP must contain exactly one root folder.', 'hooma'));
		}

		$module_root_name = $dirs[0];
		$module_path = $temp_dir . $module_root_name;

		// Use helper to get info
		$info = $this->get_module_info($module_path);
		if (is_wp_error($info)) {
			return $info;
		}

		// Validation of specific Hooma requirements (Security Check)
		// Regra 4: Dependência Explícita (O "Require" do Hooma)
		// We read from the main file found by get_module_info
		$content = $wp_filesystem->get_contents($info['main_file']);

		if (!preg_match("/defined\s*\(\s*['\"](HOOMA_PATH|ABSPATH)['\"]\s*\)/", $content)) {
			return new WP_Error(
				'missing_security_check',
				__('Module Rejected: Main file missing mandatory security check. Add defined( \'HOOMA_PATH\' ) || exit; at the beginning.', 'hooma')
			);
		}

		// Explicit Identity Validation (Static Parsing) - DEPRECATED
		// We now rely on the folder name and Loader calculation to determine identity.
		// This ensures the module installed matches the folder structure we want.

		// Calculate expected identity based on directory name (Single Source of Truth)
		$info['headers']['HOOMA_MODULE_SLUG'] = $module_root_name;
		$info['headers']['HOOMA_MODULE_NAMESPACE'] = $this->kebab_to_pascal($module_root_name);

		// Version is usually in headers, but if we want to extract constant too:
		if (preg_match("/define\s*\(\s*['\"]HOOMA_MODULE_VERSION['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $matches_ver)) {
			$info['headers']['HOOMA_MODULE_VERSION'] = $matches_ver[1];
		}

		// Define new_constants for validation below
		$new_constants = $info['headers'];

		// Validate Slug
		if (empty($new_constants['HOOMA_MODULE_SLUG'])) {
			return new WP_Error('missing_slug', __('Internal Error: Slug could not be determined.', 'hooma'));
		}

		// The checks for mismatch are now redundant because we force the values based on the folder name.
		// However, we can keep a simple check if we ever change how we get the root name.
		if ($new_constants['HOOMA_MODULE_SLUG'] !== $module_root_name) {
			// This theoretically never happens now unless logic above changes
			$info['headers']['HOOMA_MODULE_SLUG'] = $module_root_name;
		}

		// Validate Namespace
		if (empty($new_constants['HOOMA_MODULE_NAMESPACE'])) {
			// Also auto-calculated
			$info['headers']['HOOMA_MODULE_NAMESPACE'] = $this->kebab_to_pascal($module_root_name);
		}

		// Validation of Dependencies
		$requirements = $this->check_requirements($info['headers']);
		if (is_wp_error($requirements)) {
			return $requirements;
		}

		// Return fully qualified info including the root path
		return array_merge($info, array('path' => $module_path));
	}

	/**
	 * Extract module details from a directory.
	 * 
	 * @param string $dir Path to the module directory.
	 * @return array|WP_Error ['main_file' => path, 'headers' => array]
	 */
	private function get_module_info($dir)
	{
		global $wp_filesystem;

		$dirname = basename($dir);

		// Strategy to find main file: Priority to $dirname.php, then index.php
		$potential_files = array(
			$dirname . '.php',
			'index.php'
		);

		$main_file = '';
		foreach ($potential_files as $file) {
			if ($wp_filesystem->exists($dir . '/' . $file)) {
				$main_file = $file;
				break;
			}
		}

		if (empty($main_file)) {
			return new WP_Error('missing_main_file', sprintf(__('Invalid Structure: Main file not found (%s.php or index.php).', 'hooma'), $dirname));
		}

		$main_file_path = $dir . '/' . $main_file;

		// Regra 2: Validação do Manifesto (Header Comment)
		$headers = get_file_data($main_file_path, array(
			'Name' => 'Module Name',
			'Version' => 'Version',
			'RequiresHooma' => 'Requires Hooma',
			'RequiresPlugins' => 'Requires Plugins',
			'RequiresModules' => 'Requires Modules',
		));

		if (empty($headers['Name']) || empty($headers['Version'])) {
			// But for consistency let's require it.
			return new WP_Error('invalid_manifest', sprintf(__('Invalid Manifest: Main file (%s) must contain "Plugin Name" and "Version" headers.', 'hooma'), $main_file));
		}

		return array(
			'main_file' => $main_file_path,
			'headers' => $headers
		);
	}

	/**
	 * Check module requirements.
	 *
	 * @param array $headers Module headers.
	 * @return true|WP_Error
	 */
	private function check_requirements($headers)
	{
		// 1. Hooma Version
		if (!empty($headers['RequiresHooma'])) {
			if (version_compare(HOOMA_VERSION, $headers['RequiresHooma'], '<')) {
				return new WP_Error(
					'incompatible_hooma',
					sprintf(__('This module requires Hooma version %s or higher. You are using version %s.', 'hooma'), $headers['RequiresHooma'], HOOMA_VERSION)
				);
			}
		}

		// 2. Plugins
		if (!empty($headers['RequiresPlugins'])) {
			$dependencies = explode(',', $headers['RequiresPlugins']);
			$all_plugins = get_plugins(); // Key is plugin file path (e.g., my-plugin/my-plugin.php)
			$active_plugins = get_option('active_plugins');

			// Map slugs to plugin files for easier lookup
			// Helper to find plugin file by slug (foldername or filename base)
			// A simple approach: iterate all plugins and check if dirname matches slug

			foreach ($dependencies as $dep) {
				$dep = trim($dep);
				if (empty($dep))
					continue;

				// Format: slug or slug:version
				$parts = explode(':', $dep);
				$slug = $parts[0];
				$version = isset($parts[1]) ? $parts[1] : null;

				$found_file = '';
				$found_version = '';
				$is_active = false;

				foreach ($all_plugins as $file => $data) {
					// Check if slug matches dirname
					// Need to handle single file plugins too (dirname is .)
					$plugin_dirname = dirname($file);

					// Simple slug matching: 
					// 1. Exact match with directory name
					// 2. Exact match with filename without extension (for single file plugins)
					if ($plugin_dirname === $slug || basename($file, '.php') === $slug) {
						$found_file = $file;
						$found_version = $data['Version'];
						break;
					}
				}

				if ($found_file) {
					// Check if active
					if (is_multisite() && is_plugin_active_for_network($found_file)) {
						$is_active = true;
					} elseif (in_array($found_file, $active_plugins)) {
						$is_active = true;
					}
				}

				if (!$found_file) {
					return new WP_Error('missing_dependency', sprintf(__('Required plugin "%s" is not installed.', 'hooma'), $slug));
				}

				if (!$is_active) {
					return new WP_Error('inactive_dependency', sprintf(__('Required plugin "%s" is installed but not active.', 'hooma'), $slug));
				}

				if ($version && version_compare($found_version, $version, '<')) {
					return new WP_Error(
						'incompatible_dependency',
						sprintf(__('Plugin "%s" requires version %s or higher. Installed version: %s.', 'hooma'), $slug, $version, $found_version)
					);
				}
			}
		}

		// 3. Hooma Modules
		if (!empty($headers['RequiresModules'])) {
			$modules = explode(',', $headers['RequiresModules']);

			foreach ($modules as $mod) {
				$mod = trim($mod);
				if (empty($mod))
					continue;

				// Format: slug or slug:version
				$parts = explode(':', $mod);
				$slug = $parts[0];
				$version = isset($parts[1]) ? $parts[1] : null;

				$check = $this->check_module_dependency($slug, $version);
				if (is_wp_error($check)) {
					// Improve error message context if needed, or pass through
					return $check;
				}
			}
		}

		return true;
	}

	/**
	 * Check if a specific Hooma module is installed and active.
	 * Matches directory name.
	 *
	 * @param string $slug Module directory name.
	 * @param string $version Min version required.
	 * @return true|WP_Error
	 */
	private function check_module_dependency($slug, $version = null)
	{
		global $wp_filesystem;

		$module_path = HOOMA_MODULES_PATH . $slug;

		if (!$wp_filesystem->exists($module_path)) {
			return new WP_Error('missing_module', sprintf(__('Required module "%s" is not installed.', 'hooma'), $slug));
		}

		// To check version, we need to read the manifest of the installed module
		$info = $this->get_module_info($module_path);
		if (is_wp_error($info)) {
			// Module exists but is invalid (broken manifest/structure)
			return new WP_Error('invalid_module_dependency', sprintf(__('Required module "%s" is installed but seems corrupted.', 'hooma'), $slug));
		}

		$installed_version = isset($info['headers']['Version']) ? $info['headers']['Version'] : '0.0.0';

		if ($version && version_compare($installed_version, $version, '<')) {
			return new WP_Error(
				'incompatible_module',
				sprintf(__('Module "%s" requires to be version %s or higher. Installed version: %s.', 'hooma'), $slug, $version, $installed_version) // We don't have current module name easily available here without passing it down, but the error structure usually is "This module requires...". I will simplify the message.
			);
		}

		return true;
	}

	/**
	 * Convert kebab-case to PascalCase.
	 * 
	 * @param string $string
	 * @return string
	 */
	private function kebab_to_pascal($string)
	{
		return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
	}

	/**
	 * Resolve module lifecycle class.
	 *
	 * @param string $module_slug Module slug.
	 * @param string $namespace   Module base namespace.
	 * @return string|null Class name or null if not found/invalid.
	 */
	private function resolve_lifecycle_class($module_slug, $namespace)
	{
		// HoomaModules\Ads\Lifecycle
		$class = $namespace . '\\Lifecycle';

		if (
			class_exists($class) &&
			in_array(
				'Hooma_Module_Lifecycle_Interface',
				class_implements($class),
				true
			)
		) {
			return $class;
		}

		return null;
	}
}
