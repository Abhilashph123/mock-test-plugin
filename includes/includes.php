<?php
/**
 * @files included
 */
include MDNY_MOCKTEST_PATH.'includes/define-tablename-constants.php';
include MDNY_MOCKTEST_PATH.'includes/mdny-mocktest-activation.php';
include MDNY_MOCKTEST_PATH.'includes/admin-menus.php';
include MDNY_MOCKTEST_PATH.'includes/table-queries.php';
include MDNY_MOCKTEST_PATH.'includes/add-questions-func.php';
include MDNY_MOCKTEST_PATH.'includes/create-exam-functions.php';
include MDNY_MOCKTEST_PATH.'includes/admin-menu-callbacks/add-mock-question-paper.php';
include MDNY_MOCKTEST_PATH.'includes/admin-menu-callbacks/mock-questions.php';
include MDNY_MOCKTEST_PATH.'includes/admin-menu-callbacks/create-mock-exam.php';
include MDNY_MOCKTEST_PATH.'includes/questions-actions.php';
include MDNY_MOCKTEST_PATH.'includes/wp-enhanced-list-tables/wp-enhanced-list-tables.php';
include MDNY_MOCKTEST_PATH.'includes/all-mock-exams-functions.php';
include MDNY_MOCKTEST_PATH.'includes/mock-exam-shortcode.php';
include MDNY_MOCKTEST_PATH.'includes/admin-menu-callbacks/all-mock-exams.php';
include MDNY_MOCKTEST_PATH.'includes/handle-exam-submit.php';
include MDNY_MOCKTEST_PATH.'includes/admin-menu-callbacks/mock-exam-results.php';
include MDNY_MOCKTEST_PATH.'includes/mock-results.php';
include MDNY_MOCKTEST_PATH.'includes/admin-menu-callbacks/mock-topics.php';
include MDNY_MOCKTEST_PATH.'includes/custom-product-type.php';
include MDNY_MOCKTEST_PATH.'includes/exam-product-custom-endpoint.php';
include MDNY_MOCKTEST_PATH.'includes/dashboard-menus.php';
// include MDNY_MOCKTEST_PATH.'includes/premium-exam.php';


/**
 * @Admin side css and js enqueue funtion
 */

function enqueue_mock_plugin_admin_style() {
   
    wp_enqueue_style( 'custom-plugin-style',MDNY_MOCKTEST_URL .  'css/create-mock-paper.css' );
    wp_enqueue_style( 'custom-admin-plugin-style',MDNY_MOCKTEST_URL .  'css/mock-admin.css' );
    wp_enqueue_script('exam-view-script.js', MDNY_MOCKTEST_URL. 'js/exam-view-script.js', array('jquery'), '1.0.0');
}
add_action( 'admin_enqueue_scripts', 'enqueue_mock_plugin_admin_style' );

/**
 * @frontend css and js enqueue function
 */
function enqueue_mock_frontend_styles() {
    wp_enqueue_script('script-frontend', MDNY_MOCKTEST_URL. 'js/attend-exam.js', array('jquery'), '1.0.0');
    wp_enqueue_style( 'custom-frontend-plugin-style',MDNY_MOCKTEST_URL .  'css/mock-frontend.css' );
    
}
add_action( 'wp_enqueue_scripts', 'enqueue_mock_frontend_styles' );

/**
 * @mock test frontend
 * 
 * @creating a new custom endpoint for exam attending.
 */
add_action('init', function() {
    add_rewrite_endpoint('mock-exam-attend-page', EP_ROOT | EP_PAGES);
    flush_rewrite_rules();
});

add_action('template_include', function($file) {
    global $wp_query;
     if (isset($wp_query->query_vars['mock-exam-attend-page'])) {
        return  MDNY_MOCKTEST_PATH.'includes/mock-exam-attend-page.php';
    }
    return $file;
});

/**
 * @mock test frontend
 * 
 * @creating a new custom endpoint for exam result.
 */

add_action('init', function() {
    add_rewrite_endpoint('mock-exam-result-page', EP_ROOT | EP_PAGES);
    flush_rewrite_rules();
});


add_action('template_include', function($file) {
    global $wp_query;
    if (isset($wp_query->query_vars['mock-exam-result-page'])) {
        return  MDNY_MOCKTEST_PATH.'includes/mock-exam-result-page.php';
    }
    return $file;
});


add_action('init', function() {
    add_rewrite_endpoint('show-premium-exam-result-details', EP_ROOT | EP_PAGES);
    flush_rewrite_rules();
});

add_action('template_include', function($file) {
    global $wp_query;
    if (isset($wp_query->query_vars['show-premium-exam-result-details'])) {
        return  MDNY_MOCKTEST_PATH.'includes/show-premium-exam-result-details.php';
    }
    return $file;
});


/**
 * @custom exam enpoints
 * 
 * @functions to create custom endpoints for each created mock exam.
 */


add_action('mdny_new_exam_created', 'mdny_add_exam_rewrite_rule_from_id');

function mdny_add_exam_rewrite_rule_from_id($q_paper_id) {
    global $wpdb;
    $table = MDNY_MOCK_QUESTION_PAPER;


    $exam_name = $wpdb->get_var($wpdb->prepare(
        "SELECT question_paper_name FROM $table WHERE q_paper_id = %d",
        $q_paper_id
    ));

    if ($exam_name) {
        $slug = sanitize_title($exam_name);

        
        $slugs = get_option('mdny_pending_exam_slugs', []);
        $slugs[$slug] = true;
        update_option('mdny_pending_exam_slugs', $slugs);
        update_option('mdny_needs_flush', true);
    }
}


add_action('init', function () {
    global $wpdb;
    $table = MDNY_MOCK_QUESTION_PAPER;

    
    add_filter('query_vars', function ($vars) {
        $vars[] = 'exam_slug';
        return $vars;
    });

    
    $exams = $wpdb->get_results("SELECT DISTINCT question_paper_name FROM $table");
    foreach ($exams as $exam) {
        $slug = sanitize_title($exam->question_paper_name);
        add_rewrite_rule("^{$slug}-mock-test/?$", 'index.php?exam_slug=' . $slug, 'top');
    }

   
    if (get_option('mdny_needs_flush')) {
        flush_rewrite_rules();
        delete_option('mdny_needs_flush');
        delete_option('mdny_pending_exam_slugs');
    }
});




add_filter('template_include', function ($template) {
    if (get_query_var('exam_slug')) {
        return MDNY_MOCKTEST_PATH . 'includes/mock-exam-single-page.php';
    }
    return $template;
});


add_shortcode('mdny_mock_submission_popup', 'mdny_render_mock_confirmation_popup');

function mdny_render_mock_confirmation_popup() {
    ob_start();
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            alert('Your mock test has been submitted successfully!');
            setTimeout(() => {
                window.location.href = "<?php echo esc_url(site_url()); ?>";
            }, 3000);
        });
    </script>
    <div style="text-align:center;">
    <p><span style="font-weight:bold;">Congratulations!</span>

You've successfully completed your mock test.
Your results will be shared with you shortly via the email.</p>
    <div class="mdny-loader"></div>
</div>
    <?php
    return ob_get_clean();
}

function _console($val){
	 ob_start();
    ?>
    <script>
        console.log(<?php echo json_encode($val); ?>);
    </script>
    <?php
    $script = ob_get_clean();
    echo $script;
}


