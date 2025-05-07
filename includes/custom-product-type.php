<?php

function enqueue_select2_for_exams() {
    if ('product' !== get_post_type()) {
        return;
    }
    wp_enqueue_script('select2');
    wp_enqueue_style('select2-style', WC()->plugin_url() . '/assets/css/select2.css');
}
add_action('admin_enqueue_scripts', 'enqueue_select2_for_exams');


function display_exam_category_products($atts) {
    $atts = shortcode_atts([
        'limit' => 10
    ], $atts, 'exam_category_products');

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => intval($atts['limit']),
        'tax_query'      => [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => 'exams'
            ]
        ]
    ];

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No exam products available.</p>';
    }

    ob_start();
    ?>
    <div class="exam-product-grid">
    <?php
    while ($query->have_posts()) {
        $query->the_post();
        global $product;
        
        $product_name_slug = sanitize_title(get_the_title());
        $custom_permalink = site_url("/products/" . $product_name_slug . "/");
        ?>
        <div class="exam-product-card">
            <a href="<?php echo esc_url($custom_permalink); ?>">
                <?php echo get_the_post_thumbnail(get_the_ID(), 'medium'); ?>
            </a>
            <h2><a href="<?php echo esc_url($custom_permalink); ?>"><?php the_title(); ?></a></h2>
            <p>Price: <?php echo $product->get_price_html(); ?></p>
            <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="button">Buy Now</a>
        </div>
        <?php
    }
    ?>
    </div>
    <?php

    wp_reset_postdata();
    
    return ob_get_clean();
}
add_shortcode('exam_category_products', 'display_exam_category_products');



function add_link_exams_product_tab($tabs) {
    $tabs['link_exams'] = [
        'label'    => __('Link Exams', 'woocommerce'),
        'target'   => 'link_exams_options',
        'class'    => ['show_if_simple', 'show_if_variable', 'show_if_exam-product'],
    ];
    return $tabs;
}
add_filter('woocommerce_product_data_tabs', 'add_link_exams_product_tab');


function link_exams_product_options() {
    global $wpdb, $post;
    $table_name = MDNY_MOCK_QUESTION_PAPER;

    $exams = $wpdb->get_results("SELECT q_paper_id, question_paper_name FROM $table_name WHERE status = 'Premium'");

    
    
    $selected_exams = get_post_meta($post->ID, '_linked_exam_ids', true);
    $selected_exams = is_array($selected_exams) ? $selected_exams : explode(',', $selected_exams);
    ?>
    <div id="link_exams_options" class="panel woocommerce_options_panel">
        <div class="options_group">
            <p class="form-field">
                <label for="_linked_exam_ids"><?php _e('Select Exams', 'woocommerce'); ?></label>
                <select id="_linked_exam_ids" name="_linked_exam_ids[]" class="wc-enhanced-select" multiple style="width: 100%;">
                    <?php foreach ($exams as $exam) : ?>
                        <option value="<?php echo esc_attr($exam->q_paper_id); ?>" 
                            <?php echo in_array($exam->q_paper_id, $selected_exams) ? 'selected' : ''; ?>>
                            <?php echo esc_html($exam->question_paper_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="description"><?php _e('Select exams to link with this product.', 'woocommerce'); ?></span>
            </p>
        </div>
    </div>
    <?php
}
add_action('woocommerce_product_data_panels', 'link_exams_product_options');



function save_link_exams_product_meta($post_id) {
    if (isset($_POST['_linked_exam_ids'])) {
        $exam_ids = array_map('sanitize_text_field', $_POST['_linked_exam_ids']);
        update_post_meta($post_id, '_linked_exam_ids', implode(',', $exam_ids));
    } else {
        delete_post_meta($post_id, '_linked_exam_ids');
    }
}
add_action('woocommerce_process_product_meta', 'save_link_exams_product_meta');


function show_link_exams_tab_js() {
    if ('product' !== get_post_type()) return;
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.product_data_tabs .link_exams_tab').addClass('show_if_simple show_if_variable show_if_exam-product');
            $('.options_group.show_if_link_exams').addClass('show_if_simple show_if_variable show_if_exam-product');
        });
    </script>
    <?php
}
add_action('admin_footer', 'show_link_exams_tab_js');
function initialize_select2_for_exams() {
    if ('product' !== get_post_type()) {
        return;
    }
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#_linked_exam_ids').select2();
        });
    </script>
    <?php
}
add_action('admin_footer', 'initialize_select2_for_exams');
