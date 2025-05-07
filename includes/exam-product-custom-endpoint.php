<?php

function custom_exam_product_rewrite_rules() {
    add_rewrite_rule('products/([^/]+)/?$', 'index.php?exam_product_name=$matches[1]', 'top');
}
add_action('init', 'custom_exam_product_rewrite_rules');


function add_exam_product_query_var($vars) {
    $vars[] = 'exam_product_name';
    return $vars;
}
add_filter('query_vars', 'add_exam_product_query_var');


function load_exam_product_template() {
    global $wpdb;
    $table_name = MDNY_MOCK_QUESTION_PAPER;
    if (get_query_var('exam_product_name')) {
        $product_name = get_query_var('exam_product_name');

        // Get product by name
        $product = get_page_by_path($product_name, OBJECT, 'product');
        if (!$product) {
            wp_redirect(home_url()); // Redirect if not found
            exit;
        }

        // Get linked exams (stored as comma-separated IDs)
        $linked_exam_ids = get_post_meta($product->ID, '_linked_exam_ids', true);
        $linked_exam_ids = !empty($linked_exam_ids) ? explode(',', $linked_exam_ids) : [];

        // Fetch exam details from the `MDNY_MOCK_QUESTION_PAPER` table
        $exam_details = [];
        if (!empty($linked_exam_ids)) {
            $placeholders = implode(',', array_fill(0, count($linked_exam_ids), '%d'));
            $query = $wpdb->prepare(
                "SELECT q_paper_id, question_paper_name, max_hours, question_ids FROM $table_name WHERE q_paper_id IN ($placeholders)", 
                ...$linked_exam_ids
            );
            $exams = $wpdb->get_results($query);
            _console( $exams);
            
            foreach ($exams as $exam) {
                $questions_ar = $exam->question_ids;
                _console($questions_ar);
                $question_ids = maybe_unserialize($exam->question_ids);
                $question_count = is_array($question_ids) ? count($question_ids) : 0;
                
                // $question_ids = count($question_ids);
                $exam_details[] = [
                    'name' => $exam->question_paper_name,
                    'max_hours' => $exam->max_hours,
                    'question_ids' =>  $question_count 
                ];
            }
        }

        // Display product details & linked exams
        get_header();
        ?>
        <div class="exam-product-container">
            <h2><?php echo esc_html(get_the_title($product->ID)); ?></h2> 
            <p><strong>Price : </strong>₹  <?php echo get_post_meta($product->ID, '_price', true); ?></p>

            <?php if (!empty($exam_details)) : ?>
                <h2>Available Exams</h2>
                <div class="exam-grid">
                    <?php foreach ($exam_details as $exam) : ?>
                        <div class="exam-card">
                            <strong><?php echo esc_html($exam['name']); ?></strong>  <br>
                            <span>Max Hours: <?php echo esc_html($exam['max_hours']); ?></span> <br>
                            <span>Total Questions: <?php echo esc_html($exam['question_ids']); ?></span> <br>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p>No linked exams found.</p>
            <?php endif; ?>
        </div>
        <?php
        get_footer();
        exit;
    }
}
add_action('template_redirect', 'load_exam_product_template');


