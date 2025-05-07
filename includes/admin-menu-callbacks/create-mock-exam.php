<?php

add_action('admin_menu',function(){

    add_submenu_page(
        'midnay-mock-test',
        'Create Mock Exam ',
        'Create Mock Exam ',
        'manage_options',
        'create-mock-question-paper',
        'create_mock_question_paper_callback',
       1
    );

});





function create_mock_question_paper_callback(){

    if (isset($_GET['success']) && $_GET['success'] == 'mock_paper_saved') {
        echo '<div class="notice notice-success is-dismissible"><p>Mock Paper Created successfully!</p></div>';
    }
    
    
    global $wpdb;
    $table_name = MDNY_MOCK_QUESTION_PAPER;
    $createMockpaper = new createMockpaper();
    $createMockpaper->display_add_mock_form();
    
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mock_paper_save'])){
        $createMockpaper->submit_add_mock_form();
       
    }
}
