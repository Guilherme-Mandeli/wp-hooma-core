<?php

if (!defined('ABSPATH')) {
    exit;
}

class Hooma_Autoloader
{

    /**
     * Register the autoloader.
     */
    public function register()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Autoloads classes from modules.
     *
     * Maps HoomaModules\{Module}\{Class} to hooma-modules/{module}/includes/{Class}.php
     *
     * @param string $class_name The class name to load.
     */
    public function autoload($class_name)
    {
        // Optimización de rendimiento: retornar inmediatamente si la clase no pertenece al namespace de Hooma
        if (strpos($class_name, 'Hooma') !== 0) {
            return;
        }

        // Split into parts
        $parts = explode('\\', $class_name);

        // Support standard 'Hooma\Core' prefix for Core classes & services
        if ($parts[0] === 'Hooma' && isset($parts[1]) && $parts[1] === 'Core') {
            array_shift($parts); // Remove 'Hooma'
            array_shift($parts); // Remove 'Core'
            if (empty($parts)) {
                return;
            }
            if ($parts[0] === 'Services') {
                array_shift($parts); // Remove 'Services'
                $path = HOOMA_PATH . 'services/' . implode('/', $parts) . '.php';
            } else {
                $path = HOOMA_PATH . 'includes/' . implode('/', $parts) . '.php';
            }
            $path = wp_normalize_path($path);
            if (file_exists($path)) {
                require_once $path;
            }
            return;
        }

        // The first part is the module namespace (Convention: CamelCase -> kebab-case)
        $module_namespace_root = array_shift($parts);

        // Support standard 'HoomaModules' prefix
        if ($module_namespace_root === 'HoomaModules') {
            if (empty($parts)) {
                return; // Nothing after prefix
            }
            $module_namespace_root = array_shift($parts);
        }

        $module_slug = $this->camel_to_kebab($module_namespace_root);

        // Check if this module exists in our modules directory
        $module_base_path = untrailingslashit(HOOMA_MODULES_PATH) . '/' . $module_slug;

        // Optimization: checking is_dir for every class is expensive if we have many unrelated classes.
        // However, standard WP autoload chain will handle known classes first.
        // We could cache this check or use a list of active modules if available.
        // For now, reliance on filesystem is acceptable for simplicity.
        if (!is_dir($module_base_path)) {
            // Not a Hooma module, or at least not one we can find.
            return;
        }

        // The rest is the class path
        $class_file = array_pop($parts); // The class name itself
        $namespace_path = implode('/', $parts); // Sub-namespaces if any

        $path_parts = array(
            $module_base_path,
            'includes'
        );

        if (!empty($namespace_path)) {
            $path_parts[] = $namespace_path;
        }

        $path_parts[] = $class_file . '.php';

        $path = implode('/', $path_parts); // WP uses forward slashes internally mostly, but let's be safe
        $path = wp_normalize_path($path); // Convert backslashes to forward slashes for consistency

        // Debug logging
        // error_log( "Hooma Autoloader: Class [$class_name] -> Path [$path]" );

        if (file_exists($path)) {
            require_once $path;
        } else {
            // Log missing file to help debugging
            error_log("Hooma Autoloader CRITICAL: File not found for class [$class_name]. Expected at: [$path]");
        }
    }

    /**
     * Convert CamelCase to kebab-case.
     *
     * @param string $string
     * @return string
     */
    private function camel_to_kebab($string)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $string));
    }
}
