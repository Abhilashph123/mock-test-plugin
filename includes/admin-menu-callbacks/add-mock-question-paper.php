<?php

add_action('admin_menu',function(){

        add_submenu_page(
            'midnay-mock-test',
            'Add Mock Questions',
            'Add Mock Questions',
            'manage_options',
            'add-mock-questions',
            'add_mock_questions_callback',
           1
        );
	
});





function add_mock_questions_callback(){

    if (isset($_GET['success']) && $_GET['success'] == 'question_added') {
        echo '<div class="notice notice-success is-dismissible"><p>Question added successfully!</p></div>';
    }
    
    if (isset($_GET['error'])) {
        if ($_GET['error'] == 'invalid_answer') {
            echo '<div class="notice notice-error"><p>Invalid correct answer selected.</p></div>';
        } elseif ($_GET['error'] == 'db_error') {
            echo '<div class="notice notice-error"><p>Database error occurred.</p></div>';
        }
    }
    




    $Mdny_Mock_Add_Question = new Mdny_Mock_Add_Question();
    
        if (isset($_GET['edit'])) {

            $Mdny_Mock_Add_Question->edit_mock_question_form(intval($_GET['edit']));
            wp_cache_delete( 'admin_question_data', 'all_questions_data' );
        } elseif (isset($_GET['delete'])) {

           
            $Mdny_Mock_Add_Question->delete_question_callback(intval($_GET['delete']));
            wp_cache_delete( 'admin_question_data', 'all_questions_data' );
        } elseif(isset($_GET['add_mock_topics'])){

            $Mdny_Mock_Add_Question->add_mock_topics(intval($_GET['add_mock_topics']));
            
        }elseif(isset($_GET['question_id'])) {

            if (isset($_POST['question_id'])) {
                $Mdny_Mock_Add_Question->handlemock_add_topics_to_question();
                wp_cache_delete( 'admin_question_data', 'all_questions_data' );
            } else {
                $Mdny_Mock_Add_Question->display_topics_for_question(intval($_GET['question_id']));
            }
       
        } elseif (isset($_POST['save_mock_question'])) {
            $Mdny_Mock_Add_Question->save_mock_question(); 
        }else {

            $Mdny_Mock_Add_Question->add_new_mock_question_form();
            wp_cache_delete( 'admin_question_data', 'all_questions_data' );
        
        }
         
}



