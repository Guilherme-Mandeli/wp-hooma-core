<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Hooma_Modules_List_Table extends WP_List_Table
{

    public $counts = array();

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $data = Hooma::modules()->get_modules();
        $active_modules = Hooma::modules()->get_active_modules();
        
        $status_filter = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : 'all';
        $search_query = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        // Arrays to hold counts
        $this->counts = array(
            'all' => 0,
            'active' => 0,
            'inactive' => 0
        );

        // Process data for display
        $final_data = array();
        foreach ($data as $id => $module) {
            $is_active = in_array($id, $active_modules);
            $module_status = $is_active ? 'active' : 'inactive';
            $module['status'] = $module_status;
            
            // Count for views
            $this->counts['all']++;
            $this->counts[$module_status]++;

            // Apply status filter
            if ($status_filter !== 'all' && $module_status !== $status_filter) {
                continue;
            }

            // Apply search filter (by name)
            if (!empty($search_query)) {
                if (stripos($module['name'], $search_query) === false && stripos($id, $search_query) === false) {
                    continue;
                }
            }

            // Check for menu_title if active
            if ($is_active) {
                $module_path = Hooma::modules()->get_module_path($id);
                $config_file = $module_path . '/config/navigation.php';
                if (file_exists($config_file)) {
                    $config = include $config_file;
                    if (isset($config['menu_title']) && !empty($config['menu_title'])) {
                        $module['menu_title'] = $config['menu_title'];
                    }
                }
            }

            $final_data[] = $module;
        }

        $this->items = $final_data;
    }

    public function get_views() {
        $status = isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : 'all';
        $views = array();
        
        $base_url = admin_url( 'admin.php?page=' . (isset($_REQUEST['page']) ? sanitize_text_field( $_REQUEST['page'] ) : 'hooma-modules') );
        
        $all_active = $status === 'all' ? 'current' : '';
        $active_active = $status === 'active' ? 'current' : '';
        $inactive_active = $status === 'inactive' ? 'current' : '';
        
        $views['all'] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', esc_url( remove_query_arg( 'status', $base_url ) ), $all_active, __( 'All', 'hooma' ), $this->counts['all'] );
        $views['active'] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', esc_url( add_query_arg( 'status', 'active', $base_url ) ), $active_active, __( 'Active', 'hooma' ), $this->counts['active'] );
        $views['inactive'] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', esc_url( add_query_arg( 'status', 'inactive', $base_url ) ), $inactive_active, __( 'Inactive', 'hooma' ), $this->counts['inactive'] );
        
        return $views;
    }

    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Module', 'hooma'),
            'description' => __('Description', 'hooma'),
            'status' => __('Status', 'hooma'),
        );
        return $columns;
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="module[]" value="%s" />',
            $item['id']
        );
    }

    public function column_name($item)
    {
        $actions = array();

        // Build toggle URL
        $action = ($item['status'] === 'active') ? 'deactivate' : 'activate';
        $url = add_query_arg(
            array(
                'page' => $_REQUEST['page'],
                'action' => $action,
                'module' => $item['id'],
                '_wpnonce' => wp_create_nonce('hooma_manage_module'),
            ),
            admin_url('admin.php')
        );

        $label = ($item['status'] === 'active') ? __('Deactivate', 'hooma') : __('Activate', 'hooma');

        // Add "Abrir" link if active and has menu_title
        if ($item['status'] === 'active' && isset($item['menu_title'])) {
            $open_url = admin_url('admin.php?page=' . $item['id']);
            $actions['open'] = sprintf('<a href="%s">%s</a>', $open_url, __('Open', 'hooma'));
        }

        $actions[$action] = sprintf('<a href="%s">%s</a>', $url, $label);

        // Add Delete action if inactive
        if ($item['status'] !== 'active') {
            $delete_url = add_query_arg(
                array(
                    'page' => $_REQUEST['page'],
                    'action' => 'delete_module',
                    'module' => $item['id'],
                    '_wpnonce' => wp_create_nonce('hooma_delete_module'),
                ),
                admin_url('admin.php')
            );
            $actions['delete'] = sprintf(
                '<a href="%s" class="delete" onclick="return confirm(\'%s\');">%s</a>',
                $delete_url,
                esc_js(__('Are you sure you want to permanently delete this module?', 'hooma')),
                __('Delete', 'hooma')
            );
        }

        return sprintf('%1$s %2$s', '<strong>' . $item['name'] . '</strong>', $this->row_actions($actions));
    }

    public function column_description($item)
    {
        return $item['description'];
    }



    public function column_status($item)
    {
        if ($item['status'] === 'active') {
            return '<span class="dashicons dashicons-yes wporg-ratings-5"></span> ' . __('Active', 'hooma');
        }
        return '<span class="dashicons dashicons-no-alt"></span> ' . __('Inactive', 'hooma');
    }

    /**
     * Override display_tablenav to hide top nav if empty.
     *
     * @param string $which
     */
    protected function display_tablenav($which)
    {
        if ('top' === $which && !$this->has_items()) {
            return;
        }
        parent::display_tablenav($which);
    }
}
