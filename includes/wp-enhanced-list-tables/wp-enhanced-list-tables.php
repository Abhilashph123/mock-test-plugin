<?php
/**
 * Module Name: WP Enhanced List Tables
 * Module Description: Improves the UI of WordPress admin list tables
 * Version: 1.0.0
 * Author: Neeraj
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if current screen should be enhanced
function wpelt_should_enhance_screen() {
    $screen = get_current_screen();
    // _console($screen);
    // Default allowed screens
    $allowed_screens = array('mock-test_page_view-mock-questions', 'mock-test_page_all-mock-exams', 'mock-test_page_mock-test-results');

    // Let users filter which screens to enhance
    $allowed_screens = apply_filters('wpelt_allowed_screens', $allowed_screens);

    return $screen && in_array($screen->id, $allowed_screens);
}

// Enqueue admin styles
function wpelt_enqueue_admin_styles() {
    if (is_admin() && wpelt_should_enhance_screen()) {
        wp_enqueue_style(
            'wp-enhanced-list-tables',
            plugin_dir_url(__FILE__) . 'assets/css/enhanced-list-table.css',
            array(),
            '1.0.0'
        );
    }
}
// add_action('admin_enqueue_scripts', 'wpelt_enqueue_admin_styles');

// Add custom classes to list table rows
function wpelt_list_table_classes($classes, $item, $index) {
    if (!wpelt_should_enhance_screen()) {
        return $classes;
    }

    if (isset($item->post_status)) {
        $classes[] = 'status-' . $item->post_status;
    }

    if ($index % 2 == 0) {
        $classes[] = 'alternate';
    }

    return $classes;
}
// add_filter('post_row_actions', 'wpelt_list_table_classes', 10, 3);

// Add custom classes to list table columns
function wpelt_manage_columns_classes($columns) {
    if (!wpelt_should_enhance_screen()) {
        return $columns;
    }

    foreach ($columns as $column_key => $column_display) {
        add_filter("manage_{$column_key}_column", function($column_content, $post_id) use ($column_key) {
            return sprintf('<div class="column-%s">%s</div>', esc_attr($column_key), $column_content);
        }, 10, 2);
    }
    return $columns;
}
add_filter('manage_posts_columns', 'wpelt_manage_columns_classes');
add_filter('manage_pages_columns', 'wpelt_manage_columns_classes');

function wpelt_admin_footer() {
    if (!wpelt_should_enhance_screen()) {
        return;
    }

    // Preload the styles for faster loading
    echo '<link rel="preload" href="' . plugin_dir_url(__FILE__) . 'assets/css/enhanced-list-table.css" as="style" onload="this.rel=\'stylesheet\'" />';
    echo '<noscript><link rel="stylesheet" href="' . plugin_dir_url(__FILE__) . 'assets/css/enhanced-list-table.css" /></noscript>';

    // Inline critical CSS to prevent FOUC
    echo '<style>
        .wp-list-table { display: none; }
        .enhanced-list-table-wrapper { display: block; }
    </style>';

    echo '<script>
        jQuery(document).ready(function($) {
            $(".wp-list-table").wrap("<div class=\"enhanced-list-table-wrapper\"></div>");
            $(".wp-list-table").show(); // Show the table after styles are applied
        });
    </script>';
}
add_action('admin_footer', 'wpelt_admin_footer');
