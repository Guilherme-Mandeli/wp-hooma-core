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

        // Custom Header with Action Button
        ?>
        <div class="hooma-header">
            <h1 class="wp-heading-inline">Hooma Core</h1>
            <a href="#" id="hooma-add-module-btn" class="page-title-action"><?php _e('Add Module', 'hooma'); ?></a>
            <hr>
        </div>

        <!-- Upload Form (Hidden by default) -->
        <div id="hooma-upload-container" class="card" style="display:none; margin-top: 20px; padding: 20px; max-width: 600px;">
            <h2 style="margin-top: 0;"><?php _e('Install New Module', 'hooma'); ?></h2>

            <style>
                .wp-upload-box {
                    display: block;
                    border: 1px dashed #c3c4c7;
                    border-radius: 4px;
                    background: #f6f7f7;
                    padding: 24px;
                    text-align: center;
                    cursor: pointer;
                    transition: all 0.15s ease-in-out;
                }

                .wp-upload-box:hover {
                    border-color: #2271b1;
                    background: #f0f6fc;
                }

                .wp-upload-box input[type="file"] {
                    display: none;
                }

                .wp-upload-content {
                    color: #2c3338;
                    font-size: 14px;
                }

                .wp-upload-content strong {
                    display: block;
                    color: #2271b1;
                    font-size: 14px;
                    margin-bottom: 4px;
                }

                .wp-upload-hint {
                    font-size: 12px;
                    color: #646970;
                }

                .wp-upload-box:focus-within {
                    outline: 2px solid #2271b1;
                    outline-offset: 2px;
                }

                .hooma-upload-actions {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-top: 15px;
                }

                .wp-list-table .dashicons-yes {
                    color: #4ab866;
                }

                .wp-list-table .dashicons-no-alt {
                    color: #6C757D;
                }
            </style>

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

        settings_errors('hooma_messages');

        $list_table = new Hooma_Modules_List_Table();
        $list_table->prepare_items();

        // Outputs All, Active, Inactive links
        $list_table->views();

        echo '<form method="get" style="margin-top:20px;">';
        echo '<input type="hidden" name="page" value="' . esc_attr($_REQUEST['page']) . '" />';
        if (isset($_REQUEST['status'])) {
            echo '<input type="hidden" name="status" value="' . esc_attr($_REQUEST['status']) . '" />';
        }
        $list_table->search_box(__('Search Modules', 'hooma'), 'hooma-search-modules');
        $list_table->display();
        echo '</form>';

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
}
