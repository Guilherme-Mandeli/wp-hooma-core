<?php

if (!defined('ABSPATH')) {
    exit;
}

class Hooma_Admin
{

    /**
     * Run the admin.
     */
    public function run()
    {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'handle_actions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Enqueue admin assets.
     */
    public function enqueue_admin_assets($hook)
    {
        if (isset($_GET['page'])) {
            $page = sanitize_text_field($_GET['page']);
            $active_modules = Hooma::modules()->get_active_modules();
            if ($page === 'hooma' || $page === 'hooma-modules' || in_array($page, $active_modules)) {
                wp_enqueue_style(
                    'hooma-admin-style',
                    HOOMA_URL . 'assets/css/admin.css',
                    array(),
                    HOOMA_VERSION
                );
            }
        }
    }

    /**
     * Register the admin menu.
     */
    public function register_menu()
    {
        add_menu_page(
            'Hooma Core',
            'Hooma',
            'manage_options',
            'hooma',
            array($this, 'render_page'),
            HOOMA_URL . 'assets/images/favicon-hooma-20x20.svg',
            2
        );

        add_submenu_page(
            'hooma',
            __('Modules', 'hooma'),
            __('Modules', 'hooma'),
            'manage_options',
            'hooma-modules',
            array($this, 'render_page')
        );

        // Remove redundant first submenu
        remove_submenu_page('hooma', 'hooma');

        // Dynamically register module menus
        $this->register_module_menus();
    }

    /**
     * Handle actions (activate/deactivate/install).
     */
    public function handle_actions()
    {
        if (isset($_REQUEST['page']) && ($_REQUEST['page'] === 'hooma' || $_REQUEST['page'] === 'hooma-modules')) {

            // Handle Messages
            if (isset($_GET['hooma_message'])) {
                switch ($_GET['hooma_message']) {
                    case 'installed':
                        add_settings_error('hooma_messages', 'hooma_install_success', __('Module installed successfully.', 'hooma'), 'success');
                        break;
                    case 'updated':
                        add_settings_error('hooma_messages', 'hooma_install_success', __('Module updated successfully.', 'hooma'), 'success');
                        break;
                    case 'deleted':
                        add_settings_error('hooma_messages', 'hooma_message', __('Module deleted successfully.', 'hooma'), 'success');
                        break;
                    case 'activated':
                        add_settings_error('hooma_messages', 'hooma_message', __('Module activated successfully.', 'hooma'), 'success');
                        break;
                    case 'deactivated':
                        add_settings_error('hooma_messages', 'hooma_message', __('Module deactivated successfully.', 'hooma'), 'success');
                        break;
                    case 'package_installed':
                        add_settings_error('hooma_messages', 'hooma_install_success', __('Package installed successfully.', 'hooma'), 'success');
                        break;
                    case 'package_updated':
                        add_settings_error('hooma_messages', 'hooma_install_success', __('Package updated successfully.', 'hooma'), 'success');
                        break;
                    case 'package_deleted':
                        add_settings_error('hooma_messages', 'hooma_message', __('Package deleted successfully.', 'hooma'), 'success');
                        break;
                }
            }

            // Handle Install Module
            if (isset($_POST['hooma_action']) && $_POST['hooma_action'] === 'install_module') {
                check_admin_referer('hooma_install_module');

                if (!current_user_can('upload_files')) {
                    wp_die(__('You do not have permission to do this.', 'hooma'));
                }

                require_once HOOMA_PATH . 'includes/class-hooma-installer.php';
                $installer = new Hooma_Installer();
                $result = $installer->install($_FILES['module_zip']);

                if (is_wp_error($result)) {
                    if ($result->get_error_code() === 'folder_exists') {
                        // Handle Confirmation Flow
                        $error_data = $result->get_error_data();
                        $error_data['install_type'] = 'module';
                        set_transient('hooma_overwrite_data_' . get_current_user_id(), $error_data, 600); // 10 minutes

                        // Redirect to show confirmation
                        wp_safe_redirect(add_query_arg('hooma_confirm_overwrite', '1', wp_get_referer()));
                        exit;
                    }
                    add_settings_error('hooma_messages', 'hooma_install_error', $result->get_error_message(), 'error');
                } else {
                    // Redirect on success
                    wp_safe_redirect(admin_url('admin.php?page=hooma-modules&hooma_message=installed'));
                    exit;
                }
            }

            // Handle Install Package
            if (isset($_POST['hooma_action']) && $_POST['hooma_action'] === 'install_package') {
                check_admin_referer('hooma_install_package');

                if (!current_user_can('upload_files')) {
                    wp_die(__('You do not have permission to do this.', 'hooma'));
                }

                require_once HOOMA_PATH . 'includes/class-hooma-installer.php';
                $installer = new Hooma_Installer();
                $result = $installer->install_package($_FILES['package_zip']);

                if (is_wp_error($result)) {
                    if ($result->get_error_code() === 'folder_exists') {
                        // Handle Confirmation Flow
                        $error_data = $result->get_error_data();
                        $error_data['install_type'] = 'package';
                        set_transient('hooma_overwrite_data_' . get_current_user_id(), $error_data, 600); // 10 minutes

                        // Redirect to show confirmation
                        wp_safe_redirect(add_query_arg('hooma_confirm_overwrite', '1', wp_get_referer()));
                        exit;
                    }
                    add_settings_error('hooma_messages', 'hooma_install_error', $result->get_error_message(), 'error');
                } else {
                    // Redirect on success
                    wp_safe_redirect(admin_url('admin.php?page=hooma-modules&tab=packages&hooma_message=package_installed'));
                    exit;
                }
            }

            // Handle Confirm Overwrite
            if (isset($_POST['hooma_action']) && $_POST['hooma_action'] === 'confirm_overwrite') {
                check_admin_referer('hooma_confirm_overwrite');

                if (!current_user_can('upload_files')) {
                    wp_die(__('You do not have permission to do this.', 'hooma'));
                }

                $data = get_transient('hooma_overwrite_data_' . get_current_user_id());

                if (!$data) {
                    add_settings_error('hooma_messages', 'hooma_overwrite_expired', __('The confirmation session has expired. Please try again.', 'hooma'), 'error');
                } elseif (isset($_POST['cancel'])) {
                    // Cleanup
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    WP_Filesystem();
                    global $wp_filesystem;
                    $wp_filesystem->delete($data['temp_dir'], true);
                    delete_transient('hooma_overwrite_data_' . get_current_user_id());

                    add_settings_error('hooma_messages', 'hooma_overwrite_cancelled', __('Installation cancelled.', 'hooma'), 'info');
                } else {
                    // Proceed
                    require_once HOOMA_PATH . 'includes/class-hooma-installer.php';
                    $installer = new Hooma_Installer();

                    if (isset($data['install_type']) && $data['install_type'] === 'package') {
                        // Call finalize_package_install with overwrite = true
                        $result = $installer->finalize_package_install($data['temp_dir'], true);
                        delete_transient('hooma_overwrite_data_' . get_current_user_id());

                        if (is_wp_error($result)) {
                            add_settings_error('hooma_messages', 'hooma_install_error', $result->get_error_message(), 'error');
                        } else {
                            // Redirect on success
                            wp_safe_redirect(admin_url('admin.php?page=hooma-modules&tab=packages&hooma_message=package_updated'));
                            exit;
                        }
                    } else {
                        // Call finalize_install with overwrite = true
                        $result = $installer->finalize_install($data['temp_dir'], true);
                        delete_transient('hooma_overwrite_data_' . get_current_user_id());

                        if (is_wp_error($result)) {
                            add_settings_error('hooma_messages', 'hooma_install_error', $result->get_error_message(), 'error');
                        } else {
                            // Redirect on success
                            wp_safe_redirect(admin_url('admin.php?page=hooma-modules&hooma_message=updated'));
                            exit;
                        }
                    }
                }
            }

            if (isset($_REQUEST['action']) && ($_REQUEST['action'] === 'activate' || $_REQUEST['action'] === 'deactivate')) {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'hooma_manage_module')) {
                    wp_die(__('Invalid action.', 'hooma'));
                }

                if (!current_user_can('manage_options')) {
                    wp_die(__('You do not have permission to do this.', 'hooma'));
                }

                $module = sanitize_text_field($_REQUEST['module']);
                if ($_REQUEST['action'] === 'activate') {
                    $result = Hooma::modules()->activate($module);
                    if (!is_wp_error($result)) {
                        wp_safe_redirect(admin_url('admin.php?page=hooma-modules&hooma_message=activated'));
                        exit;
                    } else {
                        add_settings_error('hooma_messages', 'hooma_message', $result->get_error_message(), 'error');
                    }
                } else {
                    $result = Hooma::modules()->deactivate($module);
                    if (!is_wp_error($result)) {
                        wp_safe_redirect(admin_url('admin.php?page=hooma-modules&hooma_message=deactivated'));
                        exit;
                    } else {
                        add_settings_error('hooma_messages', 'hooma_message', $result->get_error_message(), 'error');
                    }
                }
            }

            // Delete Action
            if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'delete_module') {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'hooma_delete_module')) {
                    wp_die(__('Invalid action.', 'hooma'));
                }

                if (!current_user_can('manage_options')) {
                    wp_die(__('You do not have permission to do this.', 'hooma'));
                }

                $module_slug = sanitize_text_field($_REQUEST['module']);

                // Check if active
                $active_modules = get_option('hooma_active_modules', array());
                if (in_array($module_slug, $active_modules)) {
                    add_settings_error(
                        'hooma_messages',
                        'hooma_message',
                        __('Cannot delete an active module. Deactivate it first.', 'hooma'),
                        'error'
                    );
                } else {
                    // Instantiate Installer
                    require_once HOOMA_PATH . 'includes/class-hooma-installer.php';
                    $installer = new Hooma_Installer();
                    $result = $installer->uninstall($module_slug);

                    if (is_wp_error($result)) {
                        add_settings_error(
                            'hooma_messages',
                            'hooma_message',
                            __('Error deleting module: ', 'hooma') . $result->get_error_message(),
                            'error'
                        );
                    } else {
                        // Redirect on success
                        wp_safe_redirect(admin_url('admin.php?page=hooma-modules&hooma_message=deleted'));
                        exit;
                    }
                }
            }

            // Delete Package Action
            if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'delete_package') {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'hooma_delete_package')) {
                    wp_die(__('Invalid action.', 'hooma'));
                }

                if (!current_user_can('manage_options')) {
                    wp_die(__('You do not have permission to do this.', 'hooma'));
                }

                $package_slug = sanitize_text_field($_REQUEST['package']);

                // Instantiate Installer
                require_once HOOMA_PATH . 'includes/class-hooma-installer.php';
                $installer = new Hooma_Installer();
                $result = $installer->uninstall_package($package_slug);

                if (is_wp_error($result)) {
                    add_settings_error(
                        'hooma_messages',
                        'hooma_message',
                        __('Error deleting package: ', 'hooma') . $result->get_error_message(),
                        'error'
                    );
                } else {
                    // Redirect on success
                    wp_safe_redirect(admin_url('admin.php?page=hooma-modules&tab=packages&hooma_message=package_deleted'));
                    exit;
                }
            }
        }
    }

    /**
     * Render the management page.
     */
    public function render_page()
    {
        // Use Hooma_UI for structure
        require_once HOOMA_PATH . 'includes/class-hooma-ui.php';

        // 1. Gather Modules
        $modules_raw = Hooma::modules()->get_modules();
        $active_modules = Hooma::modules()->get_active_modules();
        $modules = array();
        foreach ($modules_raw as $id => $module) {
            $is_active = in_array($id, $active_modules);
            $module_path = Hooma::modules()->get_module_path($id);
            
            // Get menu_title from config/navigation.php if active
            $menu_title = '';
            if ($is_active && file_exists($module_path . '/config/navigation.php')) {
                $config = include $module_path . '/config/navigation.php';
                if (isset($config['menu_title']) && !empty($config['menu_title'])) {
                    $menu_title = $config['menu_title'];
                }
            }

            // Nonces for actions
            $activate_nonce = wp_create_nonce('hooma_manage_module');
            $delete_nonce = wp_create_nonce('hooma_delete_module');

            $readme_content = '';
            $readme_files = array('README.md', 'readme.md', 'README.txt', 'readme.txt');
            foreach ($readme_files as $readme_file) {
                $readme_path = $module_path . '/' . $readme_file;
                if (file_exists($readme_path)) {
                    $readme_content = file_get_contents($readme_path);
                    if ($readme_content !== false) {
                        break;
                    }
                }
            }

            $modules[$id] = array(
                'id' => $id,
                'name' => isset($module['name']) ? $module['name'] : $id,
                'description' => isset($module['description']) ? $module['description'] : '',
                'version' => isset($module['version']) ? $module['version'] : '0.0.0',
                'status' => $is_active ? 'active' : 'inactive',
                'menu_title' => $menu_title,
                'readme' => $readme_content,
                'activate_url' => admin_url('admin.php?page=hooma-modules&hooma_action=activate&action=' . ($is_active ? 'deactivate' : 'activate') . '&module=' . $id . '&_wpnonce=' . $activate_nonce),
                'delete_url' => admin_url('admin.php?page=hooma-modules&action=delete_module&module=' . $id . '&_wpnonce=' . $delete_nonce),
                'open_url' => $is_active && $menu_title ? admin_url('admin.php?page=' . $id) : ''
            );
        }

        // 2. Gather Packages
        $packages_raw = Hooma::packages()->all();
        $packages = array();
        foreach ($packages_raw as $name => $package) {
            $manifest = $package->get_manifest();
            
            // Gather examples
            $examples = $package->get_examples();
            $examples_data = array();
            foreach ($examples as $example) {
                $files = $package->get_example_files($example);
                $examples_data[$example] = array();
                foreach ($files as $file) {
                    $examples_data[$example][$file] = $package->get_example_file_content($example, $file);
                }
            }

            // Gather docs
            $p_docs = $package->get_docs();
            $p_docs_data = array();
            foreach ($p_docs as $doc) {
                $p_docs_data[$doc] = $package->get_doc_content($doc);
            }

            $delete_nonce = wp_create_nonce('hooma_delete_package');

            $packages[$name] = array(
                'name' => $package->get_name(),
                'version' => $package->get_version(),
                'type' => $package->get_type()->value,
                'description' => $package->get_description(),
                'author' => $manifest->get_author(),
                'license' => $manifest->get_license(),
                'homepage' => $manifest->get_homepage(),
                'documentation' => $manifest->get_documentation(),
                'compatibility' => $package->get_compatibility(),
                'readme' => $package->get_readme(),
                'examples' => $examples_data,
                'docs' => $p_docs_data,
                'delete_url' => admin_url('admin.php?page=hooma-modules&tab=packages&action=delete_package&package=' . $name . '&_wpnonce=' . $delete_nonce)
            );
        }

        // 3. Gather Global Documentation
        $docs_dir = HOOMA_PATH . 'docs';
        $global_docs = array();
        if (is_dir($docs_dir)) {
            // Root docs
            $root_items = scandir($docs_dir);
            if ($root_items !== false) {
                $root_files = array();
                foreach ($root_items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $item_path = $docs_dir . '/' . $item;
                    if (is_file($item_path) && strtolower(pathinfo($item, PATHINFO_EXTENSION)) === 'md') {
                        $content = file_get_contents($item_path);
                        if ($content !== false) {
                            $root_files[$item] = $content;
                        }
                    }
                }
                if (!empty($root_files)) {
                    $global_docs['General'] = $root_files;
                }
            }
            // Subdirectories
            $sub_items = scandir($docs_dir);
            if ($sub_items !== false) {
                foreach ($sub_items as $sub_item) {
                    if ($sub_item === '.' || $sub_item === '..') continue;
                    $sub_path = $docs_dir . '/' . $sub_item;
                    if (is_dir($sub_path)) {
                        $sub_files_items = scandir($sub_path);
                        if ($sub_files_items !== false) {
                            $sub_files = array();
                            foreach ($sub_files_items as $file_item) {
                                if ($file_item === '.' || $file_item === '..') continue;
                                $file_path = $sub_path . '/' . $file_item;
                                if (is_file($file_path) && strtolower(pathinfo($file_item, PATHINFO_EXTENSION)) === 'md') {
                                    $content = file_get_contents($file_path);
                                    if ($content !== false) {
                                        $sub_files[$file_item] = $content;
                                    }
                                }
                            }
                            if (!empty($sub_files)) {
                                $global_docs[ucfirst($sub_item)] = $sub_files;
                            }
                        }
                    }
                }
            }
        }

        Hooma_UI::container_start();
        
        // Output title header
        ?>
        <div class="hooma-header">
            <h1 class="wp-heading-inline">Hooma Core</h1>
        </div>
        <?php

        settings_errors('hooma_messages');

        // Check Conflict / Overwrite status
        $overwrite_data = get_transient('hooma_overwrite_data_' . get_current_user_id());
        $has_conflict = isset($_GET['hooma_confirm_overwrite']) && $overwrite_data;

        // Enequeue marked.js
        echo '<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>';

        ?>
        <div class="hooma-explorer-layout">
            <!-- Sidebar -->
            <div class="hooma-explorer-sidebar">
                <div class="hooma-explorer-search-wrapper">
                    <span class="dashicons dashicons-search"></span>
                    <input type="text" id="hooma-ecosystem-search" placeholder="<?php esc_attr_e('Search ecosystem...', 'hooma'); ?>" oninput="hoomaFilterEcosystem(this.value)">
                </div>

                <div class="hooma-sidebar-nav">
                    <!-- Section: General -->
                    <div class="hooma-nav-section">
                        <ul class="hooma-nav-list">
                            <li>
                                <a href="#" class="hooma-nav-item active" id="nav-overview" onclick="hoomaShowOverview(event)">
                                    <img src="<?php echo plugin_dir_url(__FILE__) . '../assets/images/favicon-hooma-20x20.svg'; ?>" alt="" style="filter: brightness(0) invert(.27);">
                                    <span><?php _e('Dashboard', 'hooma'); ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Section: Modules -->
                    <div class="hooma-nav-section">
                        <h4 class="hooma-nav-section-title">
                            <span><?php _e('Modules', 'hooma'); ?></span>
                            <?php if (current_user_can('upload_files')) : ?>
                            <a href="#" class="hooma-nav-add-btn" title="<?php esc_attr_e('Add Module', 'hooma'); ?>" onclick="hoomaShowAddModule(event)">
                                <span class="dashicons dashicons-plus"></span>
                            </a>
                            <?php endif; ?>
                        </h4>
                        <ul class="hooma-nav-list" id="sidebar-list-modules">
                            <?php foreach ($modules as $id => $mod) : ?>
                                <li>
                                    <a href="#" class="hooma-nav-item" id="nav-mod-<?php echo esc_attr($id); ?>" onclick="hoomaSelectModule(event, '<?php echo esc_js($id); ?>')">
                                        <span class="hooma-status-dot <?php echo esc_attr($mod['status']); ?>"></span>
                                        <span><?php echo esc_html($mod['name']); ?></span>
                                        <span class="hooma-nav-item-version">v<?php echo esc_html($mod['version']); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Section: Packages -->
                    <div class="hooma-nav-section">
                        <h4 class="hooma-nav-section-title">
                            <span><?php _e('Packages', 'hooma'); ?></span>
                            <?php if (current_user_can('upload_files')) : ?>
                            <a href="#" class="hooma-nav-add-btn" title="<?php esc_attr_e('Add Package', 'hooma'); ?>" onclick="hoomaShowAddPackage(event)">
                                <span class="dashicons dashicons-plus"></span>
                            </a>
                            <?php endif; ?>
                        </h4>
                        <ul class="hooma-nav-list" id="sidebar-list-packages">
                            <?php foreach ($packages as $name => $pkg) : ?>
                                <li>
                                    <a href="#" class="hooma-nav-item" id="nav-pkg-<?php echo esc_attr($name); ?>" onclick="hoomaSelectPackage(event, '<?php echo esc_js($name); ?>')">
                                        <span class="dashicons <?php 
                                            switch($pkg['type']) {
                                                case 'javascript': echo 'dashicons-editor-code'; break;
                                                case 'php': echo 'dashicons-media-code'; break;
                                                case 'binary': echo 'dashicons-admin-settings'; break;
                                                case 'asset': echo 'dashicons-art'; break;
                                                case 'template': echo 'dashicons-email'; break;
                                                case 'schema': echo 'dashicons-media-spreadsheet'; break;
                                                default: echo 'dashicons-box';
                                            }
                                        ?>"></span>
                                        <span><?php echo esc_html($pkg['name']); ?></span>
                                        <span class="hooma-nav-item-version">v<?php echo esc_html($pkg['version']); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Section: Documentation -->
                    <div class="hooma-nav-section">
                        <h4 class="hooma-nav-section-title">
                            <span><?php _e('Documentation', 'hooma'); ?></span>
                        </h4>
                        <ul class="hooma-nav-list" id="sidebar-list-docs">
                            <?php foreach ($global_docs as $group => $files) : ?>
                                <li class="hooma-sidebar-doc-group">
                                    <div class="hooma-sidebar-doc-group-title" onclick="hoomaToggleDocGroup(this)">
                                        <span class="dashicons dashicons-arrow-right"></span>
                                        <span><?php echo esc_html($group); ?></span>
                                    </div>
                                    <ul class="hooma-nav-list" style="padding-left:12px; display:none;">
                                        <?php foreach ($files as $filename => $content) : ?>
                                            <li>
                                                <a href="#" class="hooma-nav-item" id="nav-doc-<?php echo esc_attr(sanitize_title($group . '-' . $filename)); ?>" onclick="hoomaSelectDoc(event, '<?php echo esc_js($group); ?>', '<?php echo esc_js($filename); ?>')">
                                                    <span class="dashicons dashicons-media-text"></span>
                                                    <span><?php echo esc_html(str_replace('.md', '', $filename)); ?></span>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Canvas -->
            <div class="hooma-explorer-canvas">
                <!-- Overview View -->
                <div class="hooma-canvas-view active" id="view-overview">
                    <div class="hooma-overview-card">
                        <h2><?php _e('Welcome to Hooma Workspace', 'hooma'); ?></h2>
                        <p class="description"><?php _e('Manage business modules, infrastructure packages, and browse developer documentation.', 'hooma'); ?></p>
                        
                        <div class="hooma-stats-grid">
                            <div class="hooma-stat-box">
                                <div class="stat-val"><?php echo count($modules); ?></div>
                                <div class="stat-lbl"><?php _e('Total Modules', 'hooma'); ?></div>
                            </div>
                            <div class="hooma-stat-box">
                                <div class="stat-val"><?php echo count($packages); ?></div>
                                <div class="stat-lbl"><?php _e('Total Packages', 'hooma'); ?></div>
                            </div>
                            <div class="hooma-stat-box">
                                <div class="stat-val">
                                    <?php 
                                        $doc_count = 0;
                                        foreach ($global_docs as $files) {
                                            $doc_count += count($files);
                                        }
                                        echo $doc_count;
                                    ?>
                                </div>
                                <div class="stat-lbl"><?php _e('Documentation Articles', 'hooma'); ?></div>
                            </div>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                        <div class="hooma-overview-card" style="margin-bottom:0;">
                            <h3><?php _e('Quick Actions', 'hooma'); ?></h3>
                            <div style="display:flex; flex-direction:column; gap:10px; margin-top:15px;">
                                <?php if (current_user_can('upload_files')) : ?>
                                    <a href="#" class="button button-primary" onclick="hoomaShowAddModule(event)" style="text-align:center; padding: 6px 12px;"><?php _e('Install New Module', 'hooma'); ?></a>
                                    <a href="#" class="button button-secondary" onclick="hoomaShowAddPackage(event)" style="text-align:center; padding: 6px 12px;"><?php _e('Install New Package', 'hooma'); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="hooma-overview-card" style="margin-bottom:0;">
                            <h3><?php _e('System Status', 'hooma'); ?></h3>
                            <table class="wp-list-table widefat fixed striped" style="border:none; box-shadow:none; margin-top:10px;">
                                <tbody>
                                    <tr>
                                        <td><strong><?php _e('Hooma Core Version', 'hooma'); ?></strong></td>
                                        <td><code>v<?php echo esc_html(HOOMA_VERSION); ?></code></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e('PHP Version', 'hooma'); ?></strong></td>
                                        <td><code><?php echo esc_html(PHP_VERSION); ?></code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add Module View -->
                <div class="hooma-canvas-view" id="view-add-module">
                    <div class="hooma-overview-card">
                        <h2 style="margin-top:0;"><?php _e('Install New Module', 'hooma'); ?></h2>
                        <form method="post" enctype="multipart/form-data" action="">
                            <?php wp_nonce_field('hooma_install_module'); ?>
                            <input type="hidden" name="hooma_action" value="install_module">
                            
                            <label class="wp-upload-box" style="margin-bottom:20px; display:block; border: 2px dashed #8c8f94; border-radius:4px; padding:40px; text-align:center; cursor:pointer; background:#f6f7f7;">
                                <input type="file" name="module_zip" accept=".zip" required onchange="hoomaHandleFileText(this, 'mod')" style="display:none;">
                                <span class="wp-upload-content">
                                    <strong id="upload-title-mod" style="display:block; font-size:14px; color:#1d2327;"><?php _e('Upload ZIP File', 'hooma'); ?></strong>
                                    <span class="wp-upload-hint" id="upload-hint-mod" style="display:block; font-size:12px; color:#757575; margin-top:8px;"><?php _e('Drag file here or click to select', 'hooma'); ?></span>
                                </span>
                            </label>

                            <div class="hooma-upload-actions" style="display:flex; gap:10px; align-items:center;">
                                <button type="button" class="button" onclick="hoomaShowOverview(event)"><?php _e('Cancel', 'hooma'); ?></button>
                                <?php submit_button(__('Install Now', 'hooma'), 'primary', 'install', false); ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Add Package View -->
                <div class="hooma-canvas-view" id="view-add-package">
                    <div class="hooma-overview-card">
                        <h2 style="margin-top:0;"><?php _e('Install New Package', 'hooma'); ?></h2>
                        
                        <div class="hooma-alert alert-warning" style="margin-bottom:20px;">
                            <span class="dashicons dashicons-warning"></span>
                            <div>
                                <strong><?php _e('Important', 'hooma'); ?>:</strong> <?php _e('The ZIP package must contain a single root folder with the package slug, containing a valid "manifest.json" file at its root. The manifest.json must contain at least: "name", "version", and "type".', 'hooma'); ?>
                            </div>
                        </div>

                        <form method="post" enctype="multipart/form-data" action="">
                            <?php wp_nonce_field('hooma_install_package'); ?>
                            <input type="hidden" name="hooma_action" value="install_package">
                            
                            <label class="wp-upload-box" style="margin-bottom:20px; display:block; border: 2px dashed #8c8f94; border-radius:4px; padding:40px; text-align:center; cursor:pointer; background:#f6f7f7;">
                                <input type="file" name="package_zip" accept=".zip" required onchange="hoomaHandleFileText(this, 'pkg')" style="display:none;">
                                <span class="wp-upload-content">
                                    <strong id="upload-title-pkg" style="display:block; font-size:14px; color:#1d2327;"><?php _e('Upload ZIP File', 'hooma'); ?></strong>
                                    <span class="wp-upload-hint" id="upload-hint-pkg" style="display:block; font-size:12px; color:#757575; margin-top:8px;"><?php _e('Drag file here or click to select', 'hooma'); ?></span>
                                </span>
                            </label>

                            <div class="hooma-upload-actions" style="display:flex; gap:10px; align-items:center;">
                                <button type="button" class="button" onclick="hoomaShowOverview(event)"><?php _e('Cancel', 'hooma'); ?></button>
                                <?php submit_button(__('Install Now', 'hooma'), 'primary', 'install', false); ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Conflict / Overwrite view -->
                <?php if ($has_conflict) : ?>
                    <div class="hooma-canvas-view active" id="view-conflict">
                        <div class="hooma-overview-card" style="border-left: 4px solid #f0b849;">
                            <h2 style="margin-top:0;"><?php _e('Installation Conflict', 'hooma'); ?></h2>
                            <p><?php 
                                if (isset($overwrite_data['install_type']) && $overwrite_data['install_type'] === 'package') {
                                    printf(__('The package directory "%s" already exists.', 'hooma'), esc_html($overwrite_data['package_slug']));
                                } else {
                                    printf(__('The module directory "%s" already exists.', 'hooma'), esc_html($overwrite_data['module_slug']));
                                }
                            ?></p>
                            <p><strong><?php _e('Do you want to replace the existing version with the uploaded version?', 'hooma'); ?></strong></p>

                            <ul style="background: #f6f7f7; padding: 15px; border-radius: 4px; border:1px solid #e0e0e0; list-style:none;">
                                <?php if (isset($overwrite_data['install_type']) && $overwrite_data['install_type'] === 'package') : ?>
                                    <li><strong><?php _e('Package', 'hooma'); ?>:</strong> <?php echo esc_html($overwrite_data['package_slug']); ?></li>
                                    <li><strong><?php _e('Type', 'hooma'); ?>:</strong> <?php echo esc_html($overwrite_data['new_manifest']['type']); ?></li>
                                    <li><strong><?php _e('Version', 'hooma'); ?>:</strong> <?php echo esc_html($overwrite_data['new_manifest']['version']); ?></li>
                                <?php else : ?>
                                    <li><strong><?php _e('Module', 'hooma'); ?>:</strong> <?php echo esc_html($overwrite_data['module_slug']); ?></li>
                                    <li><strong><?php _e('Namespace', 'hooma'); ?>:</strong> <?php echo esc_html($overwrite_data['new_headers']['HOOMA_MODULE_NAMESPACE']); ?></li>
                                    <li><strong><?php _e('Version', 'hooma'); ?>:</strong> <?php echo esc_html($overwrite_data['new_headers']['Version']); ?></li>
                                <?php endif; ?>
                            </ul>

                            <form method="post" action="">
                                <?php wp_nonce_field('hooma_confirm_overwrite'); ?>
                                <input type="hidden" name="hooma_action" value="confirm_overwrite">
                                <div style="margin-top: 20px; display:flex; gap:10px;">
                                    <?php submit_button(__('Overwrite Existing Version', 'hooma'), 'primary', 'overwrite', false); ?>
                                    <?php submit_button(__('Cancel', 'hooma'), 'secondary', 'cancel', false); ?>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Module Detail View -->
                <?php include HOOMA_PATH . 'admin/views/view-module-details.php'; ?>

                <!-- Package Detail View -->
                <?php include HOOMA_PATH . 'admin/views/view-package-details.php'; ?>

                <!-- Doc View -->
                <div class="hooma-canvas-view" id="view-doc">
                    <div class="hooma-detail-view-header">
                        <h2 class="hooma-detail-title" id="doc-title">Document</h2>
                    </div>
                    <div class="hooma-markdown-body" id="doc-body" style="padding: 10px 0;"></div>
                </div>
            </div>
        </div>

        <script>
            var hoomaEcosystem = <?php echo json_encode(array(
                'modules' => $modules,
                'packages' => $packages,
                'docs' => $global_docs
            )); ?>;

            function hoomaHandleFileText(input, type) {
                var wrapper = input.closest('.wp-upload-box');
                var title = document.getElementById('upload-title-' + type);
                var hint = document.getElementById('upload-hint-' + type);
                if (input.files && input.files.length > 0) {
                    var fileName = input.files[0].name;
                    if (title) title.textContent = '<?php echo esc_js(__('File selected:', 'hooma')); ?>';
                    if (hint) hint.textContent = fileName;
                } else {
                    if (title) title.textContent = '<?php echo esc_js(__('Upload ZIP File', 'hooma')); ?>';
                    if (hint) hint.textContent = '<?php echo esc_js(__('Drag file here or click to select', 'hooma')); ?>';
                }
            }

            var hoomaCurrentView = 'overview';
            var hoomaCurrentSelectedId = null;

            function hoomaShowView(viewId) {
                document.querySelectorAll('.hooma-canvas-view').forEach(function(el) {
                    el.classList.remove('active');
                });
                var targetView = document.getElementById(viewId);
                if (targetView) {
                    targetView.classList.add('active');
                }
            }

            function hoomaDeactivateNav() {
                document.querySelectorAll('.hooma-nav-item').forEach(function(el) {
                    el.classList.remove('active');
                });
            }

            // Restore navigation for docs list
            function hoDeactivateNavDoc() {
                hoomaDeactivateNav();
            }

            function hoomaShowOverview(e) {
                if (e) e.preventDefault();
                hoomaDeactivateNav();
                var btn = document.getElementById('nav-overview');
                if (btn) btn.classList.add('active');
                hoomaShowView('view-overview');
                hoomaCurrentView = 'overview';
                hoomaCurrentSelectedId = null;
                hoomaUpdateUrl('overview');
            }

            function hoomaShowAddModule(e) {
                if (e) e.preventDefault();
                hoomaDeactivateNav();
                hoomaShowView('view-add-module');
                hoomaCurrentView = 'add_module';
                hoomaCurrentSelectedId = null;
                hoomaUpdateUrl('add_module');
            }

            // Fix add package view showing
            function hoomaShowAddPackage(e) {
                if (e) e.preventDefault();
                hoDeactivateNavDoc();
                hoomaShowView('view-add-package');
                hoomaCurrentView = 'add_package';
                hoomaCurrentSelectedId = null;
                hoomaUpdateUrl('add_package');
            }

            function hoomaSelectModule(e, id) {
                if (e) e.preventDefault();
                var mod = hoomaEcosystem.modules[id];
                if (!mod) return;

                hoomaDeactivateNav();
                var navLink = document.getElementById('nav-mod-' + id);
                if (navLink) navLink.classList.add('active');

                // Fill detail placeholders
                document.getElementById('mod-detail-name').textContent = mod.name;
                document.getElementById('mod-detail-slug').innerHTML = '<code>' + mod.id + '</code>';
                document.getElementById('mod-detail-version').textContent = mod.version;
                document.getElementById('mod-detail-description').textContent = mod.description;

                var readmeSeparator = document.getElementById('mod-detail-readme-separator');
                var readmeViewer = document.getElementById('mod-detail-readme');
                if (mod.readme) {
                    readmeViewer.innerHTML = window.marked ? marked.parse(mod.readme) : mod.readme;
                    readmeViewer.style.display = 'block';
                    if (readmeSeparator) {
                        readmeSeparator.style.display = 'block';
                    }
                } else {
                    readmeViewer.innerHTML = '';
                    readmeViewer.style.display = 'none';
                    if (readmeSeparator) {
                        readmeSeparator.style.display = 'none';
                    }
                }

                // Status pill
                var statusPill = document.getElementById('mod-detail-status-pill');
                statusPill.textContent = mod.status === 'active' ? '<?php echo esc_js(__('Active', 'hooma')); ?>' : '<?php echo esc_js(__('Inactive', 'hooma')); ?>';
                statusPill.className = 'hooma-badge-pill ' + (mod.status === 'active' ? 'pill-active' : 'pill-inactive');

                // Actions
                var toggleBtn = document.getElementById('mod-detail-toggle-btn');
                toggleBtn.href = mod.activate_url;
                toggleBtn.textContent = mod.status === 'active' ? '<?php echo esc_js(__('Deactivate', 'hooma')); ?>' : '<?php echo esc_js(__('Activate', 'hooma')); ?>';
                toggleBtn.className = 'button ' + (mod.status === 'active' ? 'button-secondary' : 'button-primary');

                var openBtn = document.getElementById('mod-detail-open-btn');
                if (mod.open_url) {
                    openBtn.href = mod.open_url;
                    openBtn.style.display = 'block';
                } else {
                    openBtn.style.display = 'none';
                }

                var deleteBtn = document.getElementById('mod-detail-delete-btn');
                if (deleteBtn) {
                    if (mod.status !== 'active') {
                        deleteBtn.href = mod.delete_url;
                        deleteBtn.style.display = 'block';
                        deleteBtn.onclick = function(event) {
                            return confirm('<?php echo esc_js(__('Are you sure you want to permanently delete this module?', 'hooma')); ?>');
                        };
                    } else {
                        deleteBtn.style.display = 'none';
                    }
                }

                hoomaShowView('view-module-details');
                hoomaCurrentView = 'module';
                hoomaCurrentSelectedId = id;
                hoomaUpdateUrl('modules', id);
            }

            function hoomaSelectPackage(e, name) {
                if (e) e.preventDefault();
                var pkg = hoomaEcosystem.packages[name];
                if (!pkg) return;

                hoomaDeactivateNav();
                var navLink = document.getElementById('nav-pkg-' + name);
                if (navLink) navLink.classList.add('active');

                // Fill detail placeholders
                document.getElementById('pkg-detail-name').textContent = pkg.name;
                document.getElementById('pkg-detail-version').innerHTML = '<code>' + pkg.version + '</code>';
                
                // Type pill
                var typePill = document.getElementById('pkg-detail-type-pill');
                typePill.textContent = pkg.type.toUpperCase();
                typePill.className = 'hooma-badge-pill pill-active';

                // Author
                var authorRow = document.getElementById('row-pkg-author');
                if (pkg.author) {
                    document.getElementById('pkg-detail-author').textContent = pkg.author;
                    authorRow.style.display = 'table-row';
                } else {
                    authorRow.style.display = 'none';
                }

                // License
                var licenseRow = document.getElementById('row-pkg-license');
                if (pkg.license) {
                    document.getElementById('pkg-detail-license').innerHTML = '<code>' + pkg.license + '</code>';
                    licenseRow.style.display = 'table-row';
                } else {
                    licenseRow.style.display = 'none';
                }

                // Links
                var homepageBtn = document.getElementById('pkg-detail-homepage-btn');
                if (pkg.homepage) {
                    homepageBtn.href = pkg.homepage;
                    homepageBtn.style.display = 'inline-flex';
                } else {
                    homepageBtn.style.display = 'none';
                }

                var docsBtn = document.getElementById('pkg-detail-docs-btn');
                if (pkg.documentation) {
                    docsBtn.href = pkg.documentation;
                    docsBtn.style.display = 'inline-flex';
                } else {
                    docsBtn.style.display = 'none';
                }

                // Compatibility
                var compatCard = document.getElementById('card-pkg-compatibility');
                var compatList = document.getElementById('pkg-detail-compatibility-list');
                compatList.innerHTML = '';
                if (pkg.compatibility && pkg.compatibility.length > 0) {
                    pkg.compatibility.forEach(function(service) {
                        var badge = document.createElement('span');
                        badge.className = 'hooma-service-badge badge-' + service.toLowerCase();
                        badge.textContent = service.charAt(0).toUpperCase() + service.slice(1);
                        compatList.appendChild(badge);
                    });
                    compatCard.style.display = 'block';
                } else {
                    compatCard.style.display = 'none';
                }

                // Delete Action
                var deleteBtn = document.getElementById('pkg-detail-delete-btn');
                deleteBtn.href = pkg.delete_url;
                deleteBtn.onclick = function(event) {
                    return confirm('<?php echo esc_js(__('Are you sure you want to permanently delete this package?', 'hooma')); ?>');
                };

                // Readme
                var readmeBtn = document.getElementById('btn-pkg-tab-readme');
                var readmeViewer = document.getElementById('pkg-readme-viewer');
                if (pkg.readme) {
                    readmeViewer.innerHTML = window.marked ? marked.parse(pkg.readme) : pkg.readme;
                    readmeBtn.style.display = 'block';
                } else {
                    readmeViewer.innerHTML = '<p class="description"><?php echo esc_js(__('No README.md documentation provided for this package.', 'hooma')); ?></p>';
                    readmeBtn.style.display = 'none';
                }

                // Examples Tab
                var examplesBtn = document.getElementById('btn-pkg-tab-examples');
                var examplesList = document.getElementById('pkg-examples-list');
                var examplesHeader = document.getElementById('pkg-examples-header');
                var examplesBody = document.getElementById('pkg-examples-body');
                examplesBody.textContent = '';
                examplesHeader.innerHTML = '<span class="description"><?php echo esc_js(__('Select a file to view its content', 'hooma')); ?></span>';
                
                if (pkg.examples && Object.keys(pkg.examples).length > 0) {
                    examplesList.innerHTML = '';
                    for (var groupName in pkg.examples) {
                        var groupDiv = document.createElement('div');
                        groupDiv.className = 'hooma-example-group';
                        groupDiv.innerHTML = '<h4 class="hooma-example-group-title" onclick="hoomaToggleDocGroup(this)"><span class="dashicons dashicons-arrow-right"></span> ' + groupName + '</h4>';
                        var ul = document.createElement('ul');
                        ul.className = 'hooma-example-files-list';
                        for (var filename in pkg.examples[groupName]) {
                            var li = document.createElement('li');
                            li.innerHTML = '<a href="#" class="hooma-example-file-link" onclick="hoomaSelectExampleFile(event, \'' + name + '\', \'' + groupName + '\', \'' + filename + '\')"><span class="dashicons dashicons-editor-code"></span> ' + filename + '</a>';
                            ul.appendChild(li);
                        }
                        groupDiv.appendChild(ul);
                        examplesList.appendChild(groupDiv);
                    }
                    examplesBtn.style.display = 'block';
                } else {
                    examplesBtn.style.display = 'none';
                }

                // Documentation Tab
                var docsTabBtn = document.getElementById('btn-pkg-tab-docs');
                var docsList = document.getElementById('pkg-docs-list');
                var docsHeader = document.getElementById('pkg-docs-header');
                var docsBody = document.getElementById('pkg-docs-body');
                docsBody.innerHTML = '';
                docsHeader.innerHTML = '<span class="description"><?php echo esc_js(__('Select a document to view its content', 'hooma')); ?></span>';

                if (pkg.docs && Object.keys(pkg.docs).length > 0) {
                    docsList.innerHTML = '';
                    var docGroup = document.createElement('div');
                    docGroup.className = 'hooma-example-group';
                    docGroup.innerHTML = '<h4 class="hooma-example-group-title" onclick="hoomaToggleDocGroup(this)"><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_js(__('Documents', 'hooma')); ?></h4>';
                    var ulDocs = document.createElement('ul');
                    ulDocs.className = 'hooma-example-files-list';
                    for (var docFilename in pkg.docs) {
                        var liDoc = document.createElement('li');
                        liDoc.innerHTML = '<a href="#" class="hooma-doc-file-link" onclick="hoomaSelectPkgDocFile(event, \'' + name + '\', \'' + docFilename + '\')"><span class="dashicons dashicons-media-text"></span> ' + docFilename + '</a>';
                        ulDocs.appendChild(liDoc);
                    }
                    docGroup.appendChild(ulDocs);
                    docsList.appendChild(docGroup);
                    docsTabBtn.style.display = 'block';
                } else {
                    docsTabBtn.style.display = 'none';
                }

                // Default active tab in detail
                document.querySelectorAll('.hooma-detail-tab-btn').forEach(function(btn) {
                    btn.classList.remove('active');
                });
                document.querySelectorAll('.hooma-detail-tab-pane').forEach(function(pane) {
                    pane.classList.remove('active');
                });
                
                var defaultBtn = document.getElementById('btn-pkg-tab-readme');
                var defaultPane = document.getElementById('pkg-pane-readme');
                if (defaultBtn && defaultBtn.style.display !== 'none') {
                    defaultBtn.classList.add('active');
                    defaultPane.classList.add('active');
                } else if (document.getElementById('btn-pkg-tab-examples').style.display !== 'none') {
                    document.getElementById('btn-pkg-tab-examples').classList.add('active');
                    document.getElementById('pkg-pane-examples').classList.add('active');
                } else if (document.getElementById('btn-pkg-tab-docs').style.display !== 'none') {
                    document.getElementById('btn-pkg-tab-docs').classList.add('active');
                    document.getElementById('pkg-pane-docs').classList.add('active');
                }

                hoomaShowView('view-package-details');
                hoomaCurrentView = 'package';
                hoomaCurrentSelectedId = name;
                hoomaUpdateUrl('packages', name);
            }

            function hoomaSelectExampleFile(e, pkgName, group, filename) {
                e.preventDefault();
                document.querySelectorAll('.hooma-example-file-link').forEach(function(el) {
                    el.classList.remove('active');
                });
                e.currentTarget.classList.add('active');

                var pkg = hoomaEcosystem.packages[pkgName];
                var content = pkg.examples[group][filename];
                document.getElementById('pkg-examples-header').innerHTML = '<strong>' + group + ' / ' + filename + '</strong>';
                document.getElementById('pkg-examples-body').textContent = content;
            }

            function hoomaSelectPkgDocFile(e, pkgName, docFilename) {
                e.preventDefault();
                document.querySelectorAll('.hooma-doc-file-link').forEach(function(el) {
                    el.classList.remove('active');
                });
                e.currentTarget.classList.add('active');

                var pkg = hoomaEcosystem.packages[pkgName];
                var content = pkg.docs[docFilename];
                document.getElementById('pkg-docs-header').innerHTML = '<strong>' + docFilename + '</strong>';
                var docsBody = document.getElementById('pkg-docs-body');
                docsBody.innerHTML = window.marked ? marked.parse(content) : content;
            }

            function hoomaSelectDoc(e, group, filename) {
                if (e) e.preventDefault();
                var docs = hoomaEcosystem.docs[group];
                if (!docs) return;
                var content = docs[filename];
                if (!content) return;

                hoDeactivateNavDoc();
                
                var slug = group + '-' + filename;
                var sanitizedSlug = slug.replace(/[^a-z0-9]/gi, '-').toLowerCase();
                var navLink = document.getElementById('nav-doc-' + sanitizedSlug);
                if (navLink) {
                    navLink.classList.add('active');
                    var parentUl = navLink.closest('.hooma-nav-list');
                    if (parentUl && window.getComputedStyle(parentUl).display === 'none') {
                        var groupHeader = parentUl.previousElementSibling;
                        if (groupHeader && groupHeader.classList.contains('hooma-sidebar-doc-group-title')) {
                            hoomaToggleDocGroup(groupHeader);
                        }
                    }
                }

                document.getElementById('doc-title').textContent = filename.replace('.md', '');
                document.getElementById('doc-body').innerHTML = window.marked ? marked.parse(content) : content;

                hoomaShowView('view-doc');
                hoomaCurrentView = 'doc';
                hoomaCurrentSelectedId = filename;
                hoomaUpdateUrl('docs', filename, group);
            }

            function hoomaToggleDocGroup(header) {
                var ul = header.nextElementSibling;
                var arrow = header.querySelector('.dashicons');
                if (window.getComputedStyle(ul).display === 'none') {
                    ul.style.display = 'block';
                    if (arrow) {
                        arrow.className = 'dashicons dashicons-arrow-down';
                    }
                } else {
                    ul.style.display = 'none';
                    if (arrow) {
                        arrow.className = 'dashicons dashicons-arrow-right';
                    }
                }
            }

            function hoomaSwitchPkgTab(e, tabName) {
                var paneId = 'pkg-pane-' + tabName;
                document.querySelectorAll('.hooma-detail-tab-btn').forEach(function(btn) {
                    btn.classList.remove('active');
                });

                document.querySelectorAll('.hooma-detail-tab-pane').forEach(function(pane) {
                    pane.classList.remove('active');
                });
                e.currentTarget.classList.add('active');
                var pane = document.getElementById(paneId);
                if (pane) pane.classList.add('active');
            }

            function hoomaFilterEcosystem(val) {
                val = val.toLowerCase().trim();
                document.querySelectorAll('#sidebar-list-modules li, #sidebar-list-packages li, .hooma-sidebar-doc-group').forEach(function(el) {
                    var text = el.innerText.toLowerCase();
                    if (val === '' || text.includes(val)) {
                        el.style.display = 'block';
                    } else {
                        el.style.display = 'none';
                    }
                });
            }

            function hoomaUpdateUrl(tab, select, group) {
                var url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                if (select) {
                    url.searchParams.set('select', select);
                } else {
                    url.searchParams.delete('select');
                }
                if (group) {
                    url.searchParams.set('group', group);
                } else {
                    url.searchParams.delete('group');
                }
                url.searchParams.delete('action');
                history.replaceState(null, '', url.toString());
            }

            // Restore state from URL
            document.addEventListener('DOMContentLoaded', function() {
                var params = new URLSearchParams(window.location.search);
                var tab = params.get('tab');
                var select = params.get('select');
                var action = params.get('action');

                // If conflict view is active, stay on conflict view
                var conflictView = document.getElementById('view-conflict');
                if (conflictView) {
                    hoomaShowView('view-conflict');
                    return;
                }

                if (action === 'add_module') {
                    hoomaShowAddModule();
                } else if (action === 'add_package') {
                    hoomaShowAddPackage();
                } else if (tab === 'modules' && select) {
                    hoomaSelectModule(null, select);
                } else if (tab === 'packages' && select) {
                    hoomaSelectPackage(null, select);
                } else if (tab === 'docs' && select) {
                    var grp = params.get('group') || 'General';
                    hoomaSelectDoc(null, grp, select);
                } else {
                    hoomaShowOverview();
                }
            });
        </script>
        <?php
        Hooma_UI::footer();
        Hooma_UI::container_end();
    }

    /**
     * Scan active modules and register their menus.
     */
    public function register_module_menus()
    {
        $active_modules = Hooma::modules()->get_active_modules();

        foreach ($active_modules as $module_slug) {
            $module_path = Hooma::modules()->get_module_path($module_slug);
            $config_file = $module_path . '/config/navigation.php';

            if (file_exists($config_file)) {
                $config = include $config_file;

                // If config has menu_title, it needs a menu item
                if (isset($config['menu_title']) && !empty($config['menu_title'])) {
                    add_submenu_page(
                        'hooma',
                        $config['menu_title'],
                        $config['menu_title'],
                        'manage_options',
                        $module_slug,
                        array($this, 'render_module_page')
                    );
                }
            }
        }
    }

    /**
     * Render a generic module page.
     */
    public function render_module_page()
    {
        $current_page_slug = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

        if (empty($current_page_slug)) {
            return;
        }

        // Identify module by page slug (assuming slug matches module dir per Hooma convention)
        $module_slug = $current_page_slug;
        $module_path = Hooma::modules()->get_module_path($module_slug);
        $config_file = $module_path . '/config/navigation.php';

        // 1. Check Configuration
        if (!file_exists($config_file)) {
            echo '<div class="notice notice-error"><p>Configuration not found for module: ' . esc_html($module_slug) . '</p></div>';
            return;
        }

        $tabs_config = include $config_file;

        // 2. Prepare Tabs
        $ui_tabs = array();
        if (isset($tabs_config['tabs']) && is_array($tabs_config['tabs'])) {
            foreach ($tabs_config['tabs'] as $slug => $data) {
                if (is_array($data) && isset($data['label'])) {
                    $ui_tabs[$slug] = $data;
                }
            }
        }

        // Current Tab
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
        if (empty($current_tab) || !array_key_exists($current_tab, $ui_tabs)) {
            $current_tab = array_key_first($ui_tabs);
        }

        Hooma_UI::container_start();

        // 3. Render Header
        Hooma_UI::header(
            isset($tabs_config['menu_title']) ? $tabs_config['menu_title'] : $module_slug,
            ''
        );

        // 4. Render Split Layout Wrapper
        if (!empty($ui_tabs)) :
        ?>
        <div class="hooma-explorer-layout">
            <!-- Sidebar Navigation -->
            <div class="hooma-explorer-sidebar">
                <div class="hooma-sidebar-nav">
                    <div class="hooma-nav-section">
                        <h4 class="hooma-nav-section-title">
                            <span><?php _e('Navigation', 'hooma'); ?></span>
                        </h4>
                        <ul class="hooma-nav-list">
                            <?php foreach ($ui_tabs as $tab_slug => $tab_conf) : 
                                $is_active = ($current_tab === $tab_slug);
                                $tab_icon = isset($tab_conf['icon']) ? $tab_conf['icon'] : 'dashicons-admin-generic';
                                $item_url = add_query_arg(array('tab' => $tab_slug));
                            ?>
                                <li>
                                    <a href="<?php echo esc_url($item_url); ?>" class="hooma-nav-item <?php echo $is_active ? 'active' : ''; ?>">
                                        <span class="dashicons <?php echo esc_attr($tab_icon); ?>"></span>
                                        <span><?php echo esc_html($tab_conf['label']); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Canvas Content Panel -->
            <div class="hooma-explorer-canvas" style="display: block;">
        <?php
        endif;

        // 5. Render Content (Dispatch to active tab)
        $tab_data = isset($ui_tabs[$current_tab]) ? $ui_tabs[$current_tab] : array();
        
        if (isset($tab_data['callback']) && is_callable($tab_data['callback'])) {
            call_user_func($tab_data['callback']);
        } else {
            // Determine view file name
            $view_name = isset($tab_data['view']) ? $tab_data['view'] : $current_tab;
            
            // Try standard admin/views/ first
            $view_file = $module_path . '/admin/views/' . $view_name . '.php';
            if (!file_exists($view_file)) {
                // Fallback to views/
                $view_file = $module_path . '/views/' . $view_name . '.php';
            }
            if (!file_exists($view_file)) {
                // Fallback to fallback admin/views/index.php
                $fallback_view = $module_path . '/admin/views/index.php';
                if (file_exists($fallback_view)) {
                    $view_file = $fallback_view;
                }
            }

            if (file_exists($view_file)) {
                include $view_file;
            } else {
                echo '<div class="notice notice-warning inline"><p>' . __('Content not found.', 'hooma') . '</p></div>';
                echo '<div style="margin-top: 20px; padding: 10px; background: #f0f0f1; border-left: 4px solid #cc0000; font-family: monospace;">';
                echo '<strong>' . __('Details', 'hooma') . '</strong><br>';
                echo __('The configured view file was not found: ', 'hooma') . '<code>' . esc_html($view_file) . '</code><br>';
                echo '</div>';
            }
        }

        // Close split layout wrapper
        if (!empty($ui_tabs)) :
        ?>
            </div>
        </div>
        <?php
        endif;

        Hooma_UI::footer();
        Hooma_UI::container_end();
    }
}
