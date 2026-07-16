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
        if (isset($_GET['page']) && ($_GET['page'] === 'hooma' || $_GET['page'] === 'hooma-modules')) {
            wp_enqueue_style(
                'hooma-admin-style',
                HOOMA_URL . 'assets/css/admin.css',
                array(),
                HOOMA_VERSION
            );
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
                }
            }

            // Handle Install
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
        }
    }

    /**
     * Render the management page.
     */
    public function render_page()
    {
        // Include list table class if not already
        require_once HOOMA_PATH . 'admin/class-hooma-modules-list-table.php';

        // Use Hooma_UI for structure
        require_once HOOMA_PATH . 'includes/class-hooma-ui.php';

        Hooma_UI::container_start();

        // Confirmation Screen
        if (isset($_GET['hooma_confirm_overwrite'])) {
            $data = get_transient('hooma_overwrite_data_' . get_current_user_id());
            if ($data) {
                ?>
                <div class="hooma-header">
                    <h1 class="wp-heading-inline"><?php _e('Installation Conflict', 'hooma'); ?></h1>
                </div>

                <div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px; border-left: 4px solid #ffba00;">
                    <h2 class="title">
                        <?php printf(__('The module directory "%s" already exists.', 'hooma'), esc_html($data['module_slug'])); ?>
                    </h2>
                    <p><?php _e('This usually means a version of this module is already installed.', 'hooma'); ?></p>
                    <p><strong><?php _e('Do you want to replace the existing version with the uploaded version?', 'hooma'); ?></strong>
                    </p>

                    <ul style="background: #f0f0f1; padding: 15px; border-radius: 4px;">
                        <li><strong><?php _e('Module:', 'hooma'); ?></strong> <?php echo esc_html($data['module_slug']); ?></li>
                        <li><strong><?php _e('Namespace:', 'hooma'); ?></strong>
                            <?php echo esc_html($data['new_headers']['HOOMA_MODULE_NAMESPACE']); ?></li>
                        <li><strong><?php _e('Package Version:', 'hooma'); ?></strong>
                            <?php echo esc_html($data['new_headers']['Version']); ?></li>
                    </ul>

                    <form method="post" action="">
                        <?php wp_nonce_field('hooma_confirm_overwrite'); ?>
                        <input type="hidden" name="hooma_action" value="confirm_overwrite">
                        <div style="margin-top: 20px;">
                            <?php submit_button(__('Overwrite Existing Version', 'hooma'), 'primary', 'overwrite', false); ?>
                            <?php submit_button(__('Cancel', 'hooma'), 'secondary', 'cancel', false); ?>
                        </div>
                    </form>
                </div>
                <?php
                Hooma_UI::container_end();
                return;
            }
        }

        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'modules';
        ?>
        <div class="hooma-header">
            <h1 class="wp-heading-inline">Hooma Core</h1>
            <?php if ($current_tab === 'modules') : ?>
                <a href="#" id="hooma-add-module-btn" class="page-title-action"><?php _e('Add Module', 'hooma'); ?></a>
            <?php endif; ?>
        </div>



        <?php if ($current_tab === 'modules') : ?>
            <!-- Upload Form (Hidden by default) -->
            <div id="hooma-upload-container" class="card" style="display:none; margin-top: 20px; padding: 20px; max-width: 600px;">
                <h2 style="margin-top: 0;"><?php _e('Install New Module', 'hooma'); ?></h2>

                <form method="post" enctype="multipart/form-data" action="">
                    <?php wp_nonce_field('hooma_install_module'); ?>
                    <input type="hidden" name="hooma_action" value="install_module">

                    <label class="wp-upload-box">
                        <input type="file" name="module_zip" accept=".zip" required>
                        <span class="wp-upload-content">
                            <strong><?php _e('Upload ZIP File', 'hooma'); ?></strong>
                            <span class="wp-upload-hint"><?php _e('Drag file here or click to select', 'hooma'); ?></span>
                        </span>
                    </label>

                    <div class="hooma-upload-actions">
                        <button type="button" class="button" id="hooma-cancel-upload"><?php _e('Cancel', 'hooma'); ?></button>
                        <?php submit_button(__('Install Now', 'hooma'), 'primary', 'install', false); ?>
                    </div>
                </form>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var btn = document.getElementById('hooma-add-module-btn');
                    var container = document.getElementById('hooma-upload-container');
                    var cancel = document.getElementById('hooma-cancel-upload');

                    if (btn && container) {
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            container.style.display = container.style.display === 'none' ? 'block' : 'none';
                        });
                    }
                    if (cancel) {
                        cancel.addEventListener('click', function (e) {
                            e.preventDefault();
                            container.style.display = 'none';
                        });
                    }

                    // File Upload Dynamic Text
                    var fileInput = document.querySelector('input[name="module_zip"]');
                    if (fileInput) {
                        fileInput.addEventListener('change', function () {
                            var wrapper = this.closest('.wp-upload-box');
                            var title = wrapper.querySelector('strong');
                            var hint = wrapper.querySelector('.wp-upload-hint');

                            if (this.files && this.files.length > 0) {
                                var fileName = this.files[0].name;
                                if (title) {
                                    title.textContent = '<?php echo esc_js(__('Upload module:', 'hooma')); ?>';
                                }
                                if (hint) {
                                    hint.textContent = fileName;
                                }
                            } else {
                                if (title) {
                                    title.textContent = '<?php echo esc_js(__('Upload ZIP File', 'hooma')); ?>';
                                }
                                if (hint) {
                                    hint.textContent = '<?php echo esc_js(__('Drag file here or click to select', 'hooma')); ?>';
                                }
                            }
                        });
                    }
                });
            </script>
        <?php
        endif;

        settings_errors('hooma_messages');

        // Navigation Tabs (Modulos | Paquetes | Documentación)
        $admin_tabs = array(
            'modules'  => __('Modules', 'hooma'),
            'packages' => __('Packages', 'hooma'),
            'docs'     => __('Documentation', 'hooma'),
        );

        $original_uri = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = remove_query_arg(array('status', 's', 'paged'), $_SERVER['REQUEST_URI']);
        Hooma_UI::tabs($admin_tabs, $current_tab);
        $_SERVER['REQUEST_URI'] = $original_uri;

        // Check if package details is requested
        $package_details = isset($_GET['package_details']) ? sanitize_text_field($_GET['package_details']) : '';
        if ($current_tab === 'packages' && !empty($package_details)) {
            if (Hooma::packages()->exists($package_details)) {
                $package = Hooma::packages()->get($package_details);
                $this->render_package_details_page($package);
                Hooma_UI::footer();
                Hooma_UI::container_end();
                return;
            } else {
                echo '<div class="notice notice-error"><p>' . sprintf(__('Package "%s" not found.', 'hooma'), esc_html($package_details)) . '</p></div>';
            }
        }

        if ($current_tab === 'packages') {
            require_once HOOMA_PATH . 'admin/class-hooma-packages-list-table.php';
            $list_table = new Hooma_Packages_List_Table();
            $list_table->prepare_items();

            echo '<form method="get" style="margin-top:20px;">';
            echo '<input type="hidden" name="page" value="' . esc_attr($_REQUEST['page']) . '" />';
            echo '<input type="hidden" name="tab" value="packages" />';
            $list_table->search_box(__('Search Packages', 'hooma'), 'hooma-search-packages');
            $list_table->display();
            echo '</form>';
        } elseif ($current_tab === 'docs') {
            $this->render_global_docs_page();
        } else {
            $list_table = new Hooma_Modules_List_Table();
            $list_table->prepare_items();

            // Outputs All, Active, Inactive links
            $list_table->views();

            echo '<form method="get" style="margin-top:20px;">';
            echo '<input type="hidden" name="page" value="' . esc_attr($_REQUEST['page']) . '" />';
            echo '<input type="hidden" name="tab" value="modules" />';
            if (isset($_REQUEST['status'])) {
                echo '<input type="hidden" name="status" value="' . esc_attr($_REQUEST['status']) . '" />';
            }
            $list_table->search_box(__('Search Modules', 'hooma'), 'hooma-search-modules');
            $list_table->display();
            echo '</form>';
        }

        Hooma_UI::footer();
        Hooma_UI::container_end();
    }

    /**
     * Render global documentation page.
     */
    public function render_global_docs_page()
    {
        $docs_dir = HOOMA_PATH . 'docs';
        $docs_data = array();
        
        if (is_dir($docs_dir)) {
            // First, scan the root docs folder for any root .md files
            $root_items = scandir($docs_dir);
            if ($root_items !== false) {
                $root_files = array();
                foreach ($root_items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }
                    $item_path = $docs_dir . '/' . $item;
                    if (is_file($item_path) && strtolower(pathinfo($item, PATHINFO_EXTENSION)) === 'md') {
                        $content = file_get_contents($item_path);
                        if ($content !== false) {
                            $root_files[$item] = $content;
                        }
                    }
                }
                if (!empty($root_files)) {
                    $docs_data['General'] = $root_files;
                }
            }

            // Next, scan subdirectories
            $sub_items = scandir($docs_dir);
            if ($sub_items !== false) {
                foreach ($sub_items as $sub_item) {
                    if ($sub_item === '.' || $sub_item === '..') {
                        continue;
                    }
                    $sub_path = $docs_dir . '/' . $sub_item;
                    if (is_dir($sub_path)) {
                        $sub_files_items = scandir($sub_path);
                        if ($sub_files_items !== false) {
                            $sub_files = array();
                            foreach ($sub_files_items as $file_item) {
                                if ($file_item === '.' || $file_item === '..') {
                                    continue;
                                }
                                $file_path = $sub_path . '/' . $file_item;
                                if (is_file($file_path) && strtolower(pathinfo($file_item, PATHINFO_EXTENSION)) === 'md') {
                                    $content = file_get_contents($file_path);
                                    if ($content !== false) {
                                        $sub_files[$file_item] = $content;
                                    }
                                }
                            }
                            if (!empty($sub_files)) {
                                $docs_data[$sub_item] = $sub_files;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($docs_data)) {
            ?>
            <div class="hooma-examples-explorer" style="margin-top: 20px;">
                <!-- Left: List of documentation groups & files -->
                <div class="hooma-examples-list-panel">
                    <?php foreach ($docs_data as $group => $files): ?>
                        <div class="hooma-example-group">
                            <h4 class="hooma-example-group-title" onclick="hoomaToggleDocGroup(this)">
                                <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html(ucfirst($group)); ?>
                            </h4>
                            <ul class="hooma-example-files-list">
                                <?php foreach ($files as $filename => $content): ?>
                                    <li>
                                        <a href="#" class="hooma-doc-file-link" onclick="hoomaSelectGlobalDocFile(event, '<?php echo esc_js($group); ?>', '<?php echo esc_js($filename); ?>')">
                                            <span class="dashicons dashicons-media-text"></span> <?php echo esc_html($filename); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Right: Markdown Viewer -->
                <div class="hooma-code-viewer-panel" style="background:#ffffff;">
                    <div class="hooma-code-viewer-header" id="hooma-global-doc-viewer-header">
                        <span class="description"><?php _e('Select a document to view its content', 'hooma'); ?></span>
                    </div>
                    <!-- CDN Marked markdown library -->
                    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
                    <div class="hooma-markdown-body" id="hooma-global-doc-body" style="padding: 30px; overflow-y: auto; flex-grow: 1; max-height: 600px; box-sizing: border-box;">
                        <span class="description"><?php _e('No document selected.', 'hooma'); ?></span>
                    </div>
                </div>
            </div>

            <script>
                var hoomaGlobalDocs = <?php echo json_encode($docs_data); ?>;
                
                function hoomaSelectGlobalDocFile(e, group, filename) {
                    e.preventDefault();
                    
                    // Deactivate current active links
                    document.querySelectorAll('.hooma-doc-file-link').forEach(function(el) {
                        el.classList.remove('active');
                    });
                    e.currentTarget.classList.add('active');

                    var content = hoomaGlobalDocs[group] ? hoomaGlobalDocs[group][filename] : '';
                    
                    document.getElementById('hooma-global-doc-viewer-header').innerHTML = '<strong>' + group + ' / ' + filename + '</strong>';
                    var docBody = document.getElementById('hooma-global-doc-body');
                    if (window.marked && content) {
                        docBody.innerHTML = marked.parse(content);
                    } else {
                        docBody.textContent = content;
                    }
                }

                function hoomaToggleDocGroup(header) {
                    var group = header.closest('.hooma-example-group');
                    var list = group.querySelector('.hooma-example-files-list');
                    var icon = header.querySelector('.dashicons');
                    if (list.style.display === 'none' || !list.style.display) {
                        list.style.display = 'block';
                        icon.classList.remove('dashicons-arrow-right');
                        icon.classList.add('dashicons-arrow-down');
                    } else {
                        list.style.display = 'none';
                        icon.classList.remove('dashicons-arrow-down');
                        icon.classList.add('dashicons-arrow-right');
                    }
                }
            </script>
            <?php
        } else {
            ?>
            <div class="notice notice-info inline" style="margin-top: 20px;">
                <p><?php _e('No documentation files found in /docs.', 'hooma'); ?></p>
            </div>
            <?php
        }
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
                    $ui_tabs[$slug] = $data['label'];
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

        // 4. Render Tabs
        if (!empty($ui_tabs)) {
            Hooma_UI::tabs($ui_tabs, $current_tab);
        }

        // 5. Determine View File
        $view_file = '';

        if (!empty($ui_tabs)) {
            // Tab-based resolution
            $tab_data = isset($tabs_config['tabs'][$current_tab]) ? $tabs_config['tabs'][$current_tab] : array();

            // Use 'view' if defined, otherwise default to slug
            $view_name = isset($tab_data['view']) ? $tab_data['view'] : $current_tab;

            $view_file = $module_path . '/admin/views/' . $view_name . '.php';
        } else {
            // Fallback: Check for index.php
            $fallback_view = $module_path . '/admin/views/index.php';
            if (file_exists($fallback_view)) {
                $view_file = $fallback_view;
            }
        }

        // 6. Render Content Container & View
        echo '<div class="hooma-tab-content" style="padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-top: none;">';

        if (!empty($view_file) && file_exists($view_file)) {
            include $view_file;
        } else {
            // User Friendly Message
            echo '<div class="notice notice-warning inline"><p>' . __('Content not found.', 'hooma') . '</p></div>';

            // Developer Message
            echo '<div style="margin-top: 20px; padding: 10px; background: #f0f0f1; border-left: 4px solid #cc0000; font-family: monospace;">';
            echo '<strong>' . __('Details', 'hooma') . '</strong><br>';
            if (!empty($view_file)) {
                echo __('The configured view file was not found: ', 'hooma') . '<code>' . esc_html($view_file) . '</code><br>';
            } else {
                echo __('No view defined for this tab.', 'hooma') . '<br>';
            }
            echo __('Make sure to create <code>index.php</code> in the <code>admin/views/</code> folder.', 'hooma');
            echo '</div>';
        }

        echo '</div>'; // End content wrapper

        Hooma_UI::footer();
        Hooma_UI::container_end();
    }

    /**
     * Render the package details view.
     *
     * @param \Hooma\Core\Services\Packages\Package $package
     */
    public function render_package_details_page($package)
    {
        $manifest = $package->get_manifest();
        $compatibility = $package->get_compatibility();
        $readme_content = $package->get_readme();

        $examples = $package->get_examples();
        $examples_data = array();
        foreach ($examples as $example) {
            $files = $package->get_example_files($example);
            $examples_data[$example] = array();
            foreach ($files as $file) {
                $content = $package->get_example_file_content($example, $file);
                $examples_data[$example][$file] = $content;
            }
        }

        $docs = $package->get_docs();
        $docs_data = array();
        foreach ($docs as $doc) {
            $content = $package->get_doc_content($doc);
            $docs_data[$doc] = $content;
        }

        // Back Button
        $back_url = remove_query_arg('package_details');
        ?>
        <a href="<?php echo esc_url($back_url); ?>" class="button button-secondary" style="margin-bottom: 20px;">
            &larr; <?php _e('Back to Packages', 'hooma'); ?>
        </a>

        <!-- Details Dashboard Grid Layout -->
        <div class="hooma-package-grid">
            <!-- Sidebar: Metadata & Compatibility -->
            <div class="hooma-package-sidebar">
                <div class="hooma-sidebar-card">
                    <h2 class="hooma-sidebar-title"><?php echo esc_html($package->get_name()); ?></h2>
                    <div style="margin-top: 10px; margin-bottom: 15px;">
                        <span class="hooma-badge badge-<?php echo esc_attr($package->get_type()->value); ?>">
                            <?php echo esc_html(ucfirst($package->get_type()->value)); ?>
                        </span>
                    </div>

                    <table class="hooma-metadata-table">
                        <tr>
                            <th><?php _e('Version', 'hooma'); ?>:</th>
                            <td><code><?php echo esc_html($package->get_version()); ?></code></td>
                        </tr>
                        <?php if ($manifest->get_author()): ?>
                        <tr>
                            <th><?php _e('Author', 'hooma'); ?>:</th>
                            <td><?php echo esc_html($manifest->get_author()); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($manifest->get_license()): ?>
                        <tr>
                            <th><?php _e('License', 'hooma'); ?>:</th>
                            <td><code><?php echo esc_html($manifest->get_license()); ?></code></td>
                        </tr>
                        <?php endif; ?>
                    </table>

                    <div class="hooma-sidebar-actions">
                        <?php if ($manifest->get_homepage()): ?>
                            <a href="<?php echo esc_url($manifest->get_homepage()); ?>" target="_blank" rel="noopener noreferrer" class="button">
                                <span class="dashicons dashicons-admin-links"></span> <?php _e('Homepage', 'hooma'); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($manifest->get_documentation()): ?>
                            <a href="<?php echo esc_url($manifest->get_documentation()); ?>" target="_blank" rel="noopener noreferrer" class="button">
                                <span class="dashicons dashicons-editor-help"></span> <?php _e('Official Docs', 'hooma'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="hooma-sidebar-card">
                    <h3 class="hooma-sidebar-subtitle"><?php _e('Compatible Services', 'hooma'); ?></h3>
                    <div style="margin-top: 10px;">
                        <?php if (!empty($compatibility)): ?>
                            <?php foreach ($compatibility as $service): ?>
                                <div class="hooma-sidebar-service-item">
                                    <span class="hooma-service-badge badge-<?php echo esc_attr(strtolower($service)); ?>">
                                        <?php echo esc_html(ucfirst($service)); ?>
                                    </span>
                                    <span class="service-label"><?php echo esc_html(ucfirst($service)) . ' Service'; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="description"><?php _e('No compatible services declared.', 'hooma'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content Area: Tabs & Detail Views -->
            <div class="hooma-package-content-area">
                <div class="hooma-detail-tabs-nav">
                    <button class="hooma-detail-tab-btn active" onclick="hoomaSwitchDetailTab(event, 'overview')"><?php _e('Overview', 'hooma'); ?></button>
                    <button class="hooma-detail-tab-btn" onclick="hoomaSwitchDetailTab(event, 'examples')"><?php _e('Examples & Snippets', 'hooma'); ?></button>
                    <button class="hooma-detail-tab-btn" onclick="hoomaSwitchDetailTab(event, 'docs')"><?php _e('Documentation', 'hooma'); ?></button>
                </div>

                <!-- Tab Content: Overview -->
                <div id="hooma-tab-overview" class="hooma-detail-tab-pane active">
                    <?php if (!empty($readme_content)): ?>
                        <!-- CDN Marked markdown library -->
                        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
                        <div class="hooma-markdown-body" id="hooma-readme-viewer">
                            <span class="description"><?php _e('Loading documentation...', 'hooma'); ?></span>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var rawMarkdown = <?php echo json_encode($readme_content); ?>;
                                if (window.marked && rawMarkdown) {
                                    document.getElementById('hooma-readme-viewer').innerHTML = marked.parse(rawMarkdown);
                                }
                            });
                        </script>
                    <?php else: ?>
                        <div class="notice notice-info inline">
                            <p><?php _e('No README.md documentation provided for this package.', 'hooma'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tab Content: Examples -->
                <div id="hooma-tab-examples" class="hooma-detail-tab-pane">
                    <?php if (!empty($examples)): ?>
                        <div class="hooma-examples-explorer">
                            <!-- Left: List of examples & files -->
                            <div class="hooma-examples-list-panel">
                                <?php foreach ($examples as $example): ?>
                                    <div class="hooma-example-group">
                                        <h4 class="hooma-example-group-title" onclick="hoomaToggleDocGroup(this)">
                                            <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html(ucfirst($example)); ?>
                                        </h4>
                                        <ul class="hooma-example-files-list">
                                            <?php foreach ($examples_data[$example] as $filename => $content): ?>
                                                <li>
                                                    <a href="#" class="hooma-example-file-link" onclick="hoomaSelectExampleFile(event, '<?php echo esc_js($example); ?>', '<?php echo esc_js($filename); ?>')">
                                                        <span class="dashicons dashicons-editor-code"></span> <?php echo esc_html($filename); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Right: Code Snippet Viewer -->
                            <div class="hooma-code-viewer-panel">
                                <div class="hooma-code-viewer-header" id="hooma-viewer-header">
                                    <span class="description"><?php _e('Select a file to view its content', 'hooma'); ?></span>
                                </div>
                                <pre class="hooma-code-pre"><code id="hooma-code-body"><?php _e('No file selected.', 'hooma'); ?></code></pre>
                            </div>
                        </div>

                        <script>
                            var hoomaPackageExamples = <?php echo json_encode($examples_data); ?>;
                            
                            function hoomaSelectExampleFile(e, example, filename) {
                                e.preventDefault();
                                
                                // Deactivate current active links
                                document.querySelectorAll('.hooma-example-file-link').forEach(function(el) {
                                    el.classList.remove('active');
                                });
                                e.currentTarget.classList.add('active');

                                var content = hoomaPackageExamples[example] ? hoomaPackageExamples[example][filename] : '';
                                
                                document.getElementById('hooma-viewer-header').innerHTML = '<strong>' + example + ' / ' + filename + '</strong>';
                                var codeBody = document.getElementById('hooma-code-body');
                                codeBody.textContent = content;
                            }
                        </script>
                    <?php else: ?>
                        <div class="notice notice-info inline">
                            <p><?php _e('No usage examples provided for this package.', 'hooma'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tab Content: Docs -->
                <div id="hooma-tab-docs" class="hooma-detail-tab-pane">
                    <?php if (!empty($docs_data)): ?>
                        <div class="hooma-examples-explorer">
                            <!-- Left: List of documentation files -->
                            <div class="hooma-examples-list-panel">
                                <div class="hooma-example-group">
                                    <h4 class="hooma-example-group-title" onclick="hoomaToggleDocGroup(this)">
                                        <span class="dashicons dashicons-arrow-right"></span> <?php _e('Documents', 'hooma'); ?>
                                    </h4>
                                    <ul class="hooma-example-files-list">
                                        <?php foreach ($docs_data as $filename => $content): ?>
                                            <li>
                                                <a href="#" class="hooma-doc-file-link" onclick="hoomaSelectDocFile(event, '<?php echo esc_js($filename); ?>')">
                                                    <span class="dashicons dashicons-media-text"></span> <?php echo esc_html($filename); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>

                            <!-- Right: Markdown Viewer -->
                            <div class="hooma-code-viewer-panel" style="background:#ffffff;">
                                <div class="hooma-code-viewer-header" id="hooma-doc-viewer-header">
                                    <span class="description"><?php _e('Select a document to view its content', 'hooma'); ?></span>
                                </div>
                                <div class="hooma-markdown-body" id="hooma-doc-body" style="padding: 30px; overflow-y: auto; flex-grow: 1; max-height: 600px; box-sizing: border-box;">
                                    <span class="description"><?php _e('No document selected.', 'hooma'); ?></span>
                                </div>
                            </div>
                        </div>

                        <script>
                            var hoomaPackageDocs = <?php echo json_encode($docs_data); ?>;
                            
                            function hoomaSelectDocFile(e, filename) {
                                e.preventDefault();
                                
                                // Deactivate current active links
                                document.querySelectorAll('.hooma-doc-file-link').forEach(function(el) {
                                    el.classList.remove('active');
                                });
                                e.currentTarget.classList.add('active');

                                var content = hoomaPackageDocs[filename] || '';
                                
                                document.getElementById('hooma-doc-viewer-header').innerHTML = '<strong>' + filename + '</strong>';
                                var docBody = document.getElementById('hooma-doc-body');
                                if (window.marked && content) {
                                    docBody.innerHTML = marked.parse(content);
                                } else {
                                    docBody.textContent = content;
                                }
                            }
                        </script>
                    <?php else: ?>
                        <div class="notice notice-info inline">
                            <p><?php _e('No documentation files provided for this package.', 'hooma'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            function hoomaSwitchDetailTab(e, tabId) {
                e.preventDefault();
                // Toggle Tab Buttons
                document.querySelectorAll('.hooma-detail-tab-btn').forEach(function(btn) {
                    btn.classList.remove('active');
                });
                e.currentTarget.classList.add('active');

                // Toggle Tab Panes
                document.querySelectorAll('.hooma-detail-tab-pane').forEach(function(pane) {
                    pane.classList.remove('active');
                });
                document.getElementById('hooma-tab-' + tabId).classList.add('active');
            }

            function hoomaToggleDocGroup(header) {
                var group = header.closest('.hooma-example-group');
                var list = group.querySelector('.hooma-example-files-list');
                var icon = header.querySelector('.dashicons');
                if (list.style.display === 'none' || !list.style.display) {
                    list.style.display = 'block';
                    icon.classList.remove('dashicons-arrow-right');
                    icon.classList.add('dashicons-arrow-down');
                } else {
                    list.style.display = 'none';
                    icon.classList.remove('dashicons-arrow-down');
                    icon.classList.add('dashicons-arrow-right');
                }
            }
        </script>
        <?php
    }
}
