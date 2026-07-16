<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Hooma_Packages_List_Table extends WP_List_Table
{
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $data = Hooma::packages()->all();
        $search_query = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        $final_data = array();
        foreach ($data as $name => $package) {
            // Apply search filter (by name, description, or keywords)
            if (!empty($search_query)) {
                $manifest = $package->get_manifest();
                $match = false;
                if (stripos($package->get_name(), $search_query) !== false) {
                    $match = true;
                } elseif (stripos($package->get_description(), $search_query) !== false) {
                    $match = true;
                } else {
                    $keywords = $manifest->get_keywords();
                    if (is_array($keywords)) {
                        foreach ($keywords as $keyword) {
                            if (stripos($keyword, $search_query) !== false) {
                                $match = true;
                                break;
                            }
                        }
                    }
                }
                if (!$match) {
                    continue;
                }
            }

            $final_data[] = array(
                'id'            => $package->get_uuid(),
                'name'          => $package->get_name(),
                'version'       => $package->get_version(),
                'type'          => $package->get_type()->value,
                'description'   => $package->get_description(),
                'author'        => $package->get_manifest()->get_author(),
                'license'       => $package->get_manifest()->get_license(),
                'homepage'      => $package->get_manifest()->get_homepage(),
                'compatibility' => $package->get_compatibility(),
            );
        }

        $this->items = $final_data;
    }

    public function get_columns()
    {
        return array(
            'name'          => __('Package', 'hooma'),
            'description'   => __('Description', 'hooma'),
            'version'       => __('Version', 'hooma'),
            'type'          => __('Type', 'hooma'),
            'compatibility' => __('Compatibility', 'hooma'),
            'author'        => __('Author/License', 'hooma'),
        );
    }

    public function column_name($item)
    {
        $actions = array();
        
        $details_url = add_query_arg(
            array(
                'tab'             => 'packages',
                'package_details' => $item['name']
            ),
            admin_url('admin.php?page=hooma-modules')
        );

        $actions['view_docs'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url($details_url),
            __('View Docs', 'hooma')
        );

        if (!empty($item['homepage'])) {
            $actions['homepage'] = sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                esc_url($item['homepage']),
                __('Homepage', 'hooma')
            );
        }

        return sprintf(
            '<a href="%1$s" style="font-weight:600; text-decoration:none; color:#2271b1;">%2$s</a> %3$s',
            esc_url($details_url),
            esc_html($item['name']),
            $this->row_actions($actions)
        );
    }

    public function column_description($item)
    {
        return esc_html($item['description']);
    }

    public function column_version($item)
    {
        return esc_html($item['version']);
    }

    public function column_type($item)
    {
        return sprintf(
            '<span class="hooma-badge badge-%s">%s</span>',
            esc_attr($item['type']),
            esc_html(ucfirst($item['type']))
        );
    }

    public function column_compatibility($item)
    {
        if (empty($item['compatibility']) || !is_array($item['compatibility'])) {
            return '—';
        }
        $badges = array();
        foreach ($item['compatibility'] as $service) {
            $badges[] = sprintf(
                '<span class="hooma-service-badge badge-%s">%s</span>',
                esc_attr(strtolower($service)),
                esc_html(ucfirst($service))
            );
        }
        return implode(' ', $badges);
    }

    public function column_author($item)
    {
        $author = !empty($item['author']) ? $item['author'] : '—';
        $license = !empty($item['license']) ? ' (' . $item['license'] . ')' : '';
        return esc_html($author . $license);
    }

    protected function display_tablenav($which)
    {
        if ('top' === $which && (!$this->has_items() || (!$this->has_bulk_actions() && empty($this->_pagination_args)))) {
            return;
        }
        parent::display_tablenav($which);
    }
}
