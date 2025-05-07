<?php
    add_action('admin_menu',function(){

        add_submenu_page(
            'midnay-mock-test',
            'All Mock Exams',
            'All Mock Exams',
            'manage_options',
            'all-mock-exams',
            'all_mock_exams_callback',
        1
        );

    });



    function all_mock_exams_callback(){

        $screen = get_current_screen();
        _console($screen);
         
        global $wpdb;
        $Mock_exams = new Mock_exams();
        
        $q_paper_id = $_GET['q_paper_id'] ?? null; 
        $action = $_GET['action'] ?? null;
        $show = $_GET['show'] ?? null;
        

        
        
        
        if (isset($_GET['update']) && $_GET['update'] == 1) {
            echo '<div class="notice notice-success is-dismissible"><p>Mock Paper updated successfully!</p></div>';
        }
        if (isset($_GET['error']) && $_GET['error'] == 'database_error') {
            echo '<div class="notice notice-error"><p>Database error! Please try again.</p></div>';
        }

        if (isset($_GET['delete']) && $_GET['delete'] == 1) {
            echo '<div class="notice notice-success is-dismissible"><p>Mock Paper deleted successfully!</p></div>';
        }
        if (isset($_GET['error']) && $_GET['error'] == 'delete_failed') {
            echo '<div class="notice notice-error"><p>Error deleting mock paper. Please try again.</p></div>';
        }
       
      
        
    
        if ( $action == 'edit_mock_exam' && $q_paper_id ) {
            $Mock_exams->display_edit_mock_exam($q_paper_id);
            return;
        }elseif( $action == 'delete_mock_exam' && $q_paper_id ){
            $Mock_exams->delete_mock_exam($q_paper_id);
            return;
        }elseif($show == 'add_mock_questions_from_library'){
            $view_all_mockedit_questions_table = new view_all_mockedit_questions_table();
            $search = isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : '';
          
            echo '<div class="wrap">';
            echo '<h1 class="wp-heading-inline"> All Questions</h1>';
            echo '<a href="'.admin_url('admin.php?page=all-mock-exams&action=edit_mock_exam&q_paper_id='.$q_paper_id).'" class="page-title-action">Edit Exam</a>';
            echo '<form method="GET">';
            echo '<input type="hidden" name="page" value="all-mock-exams"/>';
            echo '<input type="hidden" name="show" value="add_mock_questions_from_library"/>';
            echo '<input type="hidden" name="q_paper_id" value="'.$_GET['q_paper_id'].'"/>';
            $view_all_mockedit_questions_table->prepare_items();
                // Search form
            $view_all_mockedit_questions_table->search_box('Search', 'search_id');
            $view_all_mockedit_questions_table->display();
            echo '</form>';
            echo '</div>';	
            return;	 
        }elseif($action == 'view_mock_exam'){
            $Mock_exams->view_mock_exam($q_paper_id);
            return;
        }elseif($action == 'export_mock_questions'){
            $Mock_exams->export_mock_questions($q_paper_id);
            return;
        }elseif($action == 'remove_all_questions_from_exam'){
            $Mock_exams->remove_all_questions_from_exam($q_paper_id);
            return;
        }
    
        
       
        $mdny_mock_all_exams = new mdny_mock_all_exams();
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">All Exams</h1>';
        echo '<a href="'.admin_url('admin.php?page=create-mock-question-paper').'" class="page-title-action">Add New Exam</a>';
        echo '<form method="GET">';
        echo '<input type="hidden" name="page" value="all-mock-exams"/>';
        $mdny_mock_all_exams->prepare_items();
        $mdny_mock_all_exams->search_box('search', 'search_id');
        $mdny_mock_all_exams->display();
        echo '</form>';
        echo '</div>';
    }
    