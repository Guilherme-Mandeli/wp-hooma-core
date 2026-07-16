<?php

if (!defined('ABSPATH')) {
    exit;
}

class Hooma_UI
{

    /**
     * Render standard header.
     *
     * @param string $title Page title.
     * @param string $subtitle Optional subtitle.
     */
    public static function header($title, $subtitle = '')
    {
        ?>
        <div class="hooma-header">
            <h1><?php echo esc_html($title); ?></h1>
            <?php if ($subtitle): ?>
                <p class="description"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render tabs navigation.
     *
     * @param array $tabs Associative array of slug => label.
     * @param string $current Current tab slug.
     */
    public static function tabs($tabs, $current)
    {
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $slug => $label) {
            $class = ($current === $slug) ? 'nav-tab-active' : '';
            // Assuming query param 'tab' handling is done by the module calling this
            // Construct a simple URL for same page with tab param
            $url = add_query_arg('tab', $slug);
            echo '<a href="' . esc_url($url) . '" class="nav-tab ' . esc_attr($class) . '">' . esc_html($label) . '</a>';
        }
        echo '</h2><br>';
    }

    /**
     * Start a standard container.
     */
    public static function container_start()
    {
        echo '<div class="wrap hooma-container">';
    }

    /**
     * End a standard container.
     */
    public static function container_end()
    {
        echo '</div>';
    }

    /**
     * Render footer.
     */
    public static function footer()
    {
        ?>
        <div class="hooma-footer">
            <hr>
            <p class="description text-right">Hooma Core v<?php echo esc_html(HOOMA_VERSION); ?></p>
        </div>
        <?php
    }
}
