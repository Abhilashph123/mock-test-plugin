<?php

add_action( 'init', 'exam_evaluation_submit' );

function exam_evaluation_submit(){

    if (isset($_POST['submit_second_form'])) {

        if (!function_exists('wp_safe_redirect')) {
            require_once ABSPATH . 'wp-includes/pluggable.php';
        }
        global $wpdb; 
        $user_email = sanitize_email($_POST['user_email']);
        $phone_number = sanitize_text_field($_POST['user_phone']);
        $q_paper_id = intval($_POST['q_paper_id']);
        $category = sanitize_text_field($_POST['category']);
        $answers = isset($_POST['answers']) ? $_POST['answers'] : [];
        $table_name = MDNY_MOCK_RESULTS;

        
        $serialized_answers = !empty($answers) ? maybe_serialize($answers) : '';

        
        $insert = $wpdb->insert(
            $table_name,
            [
                'questions'    => $serialized_answers,
                'q_paper_id'   => $q_paper_id,
                'user_email'   => $user_email,
                'phone_number' => $phone_number,
                'percentage'   => '',
                'category'     => $category 
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s'] 
        );

    
        if ($insert === false) {
            _console("Database insert failed: " . $wpdb->last_error);
        } else {
            evaluate_mock_exam($user_email);
            $confirmation_url = site_url('/mock-submission-confirmation'); // custom page to show popup
            wp_safe_redirect($confirmation_url);
            _console("Insert successful! New ID: " . $wpdb->insert_id);
            exit;
        }
    }

}
function evaluate_mock_exam($user_email) {
    global $wpdb;

    // Define table names
    $exam_table_status = MDNY_MOCK_QUESTION_PAPER;
    $questions_table = MDNY_MOCK_QUESTION;
    $mock_results_table = MDNY_MOCK_RESULTS;

    if (empty($user_email)) {
        echo '<div class="notice notice-error is-dismissible"><p>Missing user email.</p></div>';
        return;
    }

    // Check if percentage already exists
    $query_check_percentage = $wpdb->prepare(
        "SELECT percentage FROM $mock_results_table WHERE user_email = %s ORDER BY q_paper_id DESC LIMIT 1", 
        $user_email
    );
    
    $existing_percentage = $wpdb->get_var($query_check_percentage);

    if (!is_null($existing_percentage) && $existing_percentage !== '') {
        error_log("User $user_email already has a recorded percentage ($existing_percentage). Skipping recalculation.");
        return;
    }

    // Get the latest q_paper_id
    $query_to_get_paperID = $wpdb->prepare(
        "SELECT * FROM $mock_results_table WHERE user_email = %s ORDER BY q_paper_id DESC LIMIT 1", 
        $user_email
    );
    
    $fetched_data = $wpdb->get_row($query_to_get_paperID, ARRAY_A);

    if (!$fetched_data || empty($fetched_data['q_paper_id'])) {
        return; 
    }

    $q_paper_id = intval($fetched_data['q_paper_id']);

    // Get question IDs for the exam
    $query_exam_questions = $wpdb->prepare(
        "SELECT question_ids FROM $exam_table_status WHERE q_paper_id = %d", 
        $q_paper_id
    );
    $get_exam_questions = $wpdb->get_var($query_exam_questions);

    $exam_question_ids = maybe_unserialize($get_exam_questions);

    $formatted_qids = [];
    $question_user_map = []; 

    if (is_array($exam_question_ids)) {
        foreach ($exam_question_ids as $entry) {
            if (isset($entry['question_id']) && isset($entry['user_id'])) {
                $formatted_qids[] = $entry['question_id'];
                $question_user_map[$entry['question_id']] = $entry['user_id'];
            }
        }
    }

    // Get correct answers and topic IDs
    $exam_details = $wpdb->get_results("SELECT qid, correct, topic_ids FROM $questions_table", OBJECT_K);

    $submitted_answers = maybe_unserialize($fetched_data['questions']); 

    $correct = 0;
    $topic_arr = [];

    if (!empty($submitted_answers)) {
        foreach ($submitted_answers as $qid => $user_answer) {
            if (isset($exam_details[$qid])) {
                $is_correct = ($exam_details[$qid]->correct == $user_answer);
                if ($is_correct) {
                    $correct++;
                }

                $topic_ids = maybe_unserialize($exam_details[$qid]->topic_ids);
                if ($topic_ids && is_array($topic_ids)) {
                    foreach ($topic_ids as $topic_id) {
                        if (!isset($topic_arr[$topic_id])) {
                            $topic_arr[$topic_id] = [
                                'correct' => 0,
                                'total' => 0
                            ];
                        }
                        $topic_arr[$topic_id]['total']++;
                        if ($is_correct) {
                            $topic_arr[$topic_id]['correct']++;
                        }
                    }
                }
            }
        }
    }

    $total_questions = count($formatted_qids);
    $percentage = ($total_questions > 0) ? ($correct / $total_questions) * 100 : 0;

    // Add topic result as JSON
    $topic_json = json_encode($topic_arr);

    // Update the result in the database
    $update = $wpdb->update(
        $mock_results_table,
        [
            'percentage' => round($percentage, 2),
            'topics' => $topic_json,
            'exam_submission_date' => current_time('mysql') 
        ], 
        ['user_email' => sanitize_email($user_email), 'q_paper_id' => intval($q_paper_id)], 
        ['%f', '%s', '%s'], 
        ['%s', '%d'] 
    );
    

    if ($update === false) {
        error_log("Database update failed: " . $wpdb->last_error);
    } else {
        error_log("Update successful! Percentage: " . round($percentage, 2) . "%");
    }

    $check_percentage = $wpdb->get_var($wpdb->prepare(
        "SELECT percentage FROM $mock_results_table WHERE user_email = %s ORDER BY q_paper_id DESC LIMIT 1", 
        $user_email
    ));

    if (!is_null($check_percentage) && $check_percentage !== '') {
        $subject = "Your Mock Exam Results";
        $message = "
        <p>Dear Student,</p>
        <p>You have completed your mock exam. Here are your results:</p>
        <p><strong>Score:</strong> " . round($percentage, 2) . "%</p>
        <p><strong>Total Questions:</strong> $total_questions</p>
        <p><strong>Correct Answers:</strong> $correct</p>
        <p>Thank you for participating!</p>
        <p>Best Regards,<br>Your Exam Team</p>
        ";
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Exam Team <no-abhilash@midnay.com>'
        ];

        $email_sent = wp_mail($user_email, $subject, $message, $headers);
    }
}




function get_user_data_form($first_form_data) {
    ?>
    <div class="user-dataxYjk">
        <center style="color:#fff; margin-bottom:8px;">Enter your email to save your result.</center>
        <form method="post">
            <input type="text" name="user_email" placeholder="Enter your email*" required>
            <input type="text" name="user_phone" placeholder="Enter your phone*" required>
        
            <!-- Hidden inputs for first form data -->
            <input type="hidden" name="q_paper_id" value="<?php echo esc_attr($first_form_data['q_paper_id']); ?>">
            <input type="hidden" name="category" value="<?php echo esc_attr($first_form_data['category']); ?>">
                
            <?php 
            if (!empty($first_form_data['answers']) && is_array($first_form_data['answers'])) {
                foreach ($first_form_data['answers'] as $question_id => $answer) { 
            ?>
                <input type="hidden" name="answers[<?php echo esc_attr($question_id); ?>]" value="<?php echo esc_attr($answer); ?>">
            <?php 
                } 
            } 
            ?>
        
            <button type="submit" name="submit_second_form">Submit</button>
        </form>
    </div>
        
    <?php
}


function submit_premium_exam($first_form_data){

    global $wpdb; 
    if (!function_exists('wp_safe_redirect')) {
        require_once ABSPATH . 'wp-includes/pluggable.php';
    }
    $user_id = get_current_user_id();
    $user_info = get_userdata($user_id);
    
    $email = $user_info->user_email; 
    $phone = get_user_meta($user_id, 'billing_phone', true);

   

   

      
       
        $q_paper_id = intval($first_form_data['q_paper_id']);
        $category = sanitize_text_field($first_form_data['category']);
        $answers = isset($first_form_data['answers']) ? $first_form_data['answers'] : [];
        $table_name = MDNY_MOCK_RESULTS;

        
        $serialized_answers = !empty($answers) ? maybe_serialize($answers) : '';

        
        $insert = $wpdb->insert(
            $table_name,
            [
                'questions'    => $serialized_answers,
                'q_paper_id'   => $q_paper_id,
                'user_email'   => $email,
                'phone_number' => $phone,
                'percentage'   => '',
                'category'     => $category 
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s'] 
        );

    
        if ($insert === false) {
            _console("Database insert failed: " . $wpdb->last_error);
        } else {
            evaluate_mock_exam_premium($email, $q_paper_id);
            wp_safe_redirect(site_url('/dashboard'));
            _console("Insert successful! New ID: " . $wpdb->insert_id);
            // exit;
        }
    

}


function evaluate_mock_exam_premium($user_email, $q_paper_id) {
    global $wpdb;

    // Define table names
    $exam_table_status = MDNY_MOCK_QUESTION_PAPER;
    $questions_table = MDNY_MOCK_QUESTION;
    $mock_results_table = MDNY_MOCK_RESULTS;

    if (empty($user_email)) {
        echo '<div class="notice notice-error is-dismissible"><p>Missing user email.</p></div>';
        return;
    }



    // Get the latest q_paper_id
    $query_to_get_paperID = $wpdb->prepare(
        "SELECT * FROM {$mock_results_table} WHERE user_email = %s AND q_paper_id = %d", 
        sanitize_email($user_email), 
        intval($q_paper_id)
    );
    
    
    $fetched_data = $wpdb->get_row($query_to_get_paperID, ARRAY_A);

    if (!$fetched_data || empty($fetched_data['q_paper_id'])) {
        return; 
    }

    $q_paper_id = intval($fetched_data['q_paper_id']);

    // Get question IDs for the exam
    $query_exam_questions = $wpdb->prepare(
        "SELECT question_ids FROM $exam_table_status WHERE q_paper_id = %d", 
        $q_paper_id
    );
    $get_exam_questions = $wpdb->get_var($query_exam_questions);

    $exam_question_ids = maybe_unserialize($get_exam_questions);

    $formatted_qids = [];
    $question_user_map = []; 

    if (is_array($exam_question_ids)) {
        foreach ($exam_question_ids as $entry) {
            if (isset($entry['question_id']) && isset($entry['user_id'])) {
                $formatted_qids[] = $entry['question_id'];
                $question_user_map[$entry['question_id']] = $entry['user_id'];
            }
        }
    }

    // Get correct answers for the questions
    $exam_details = $wpdb->get_results("SELECT qid, correct FROM $questions_table", OBJECT_K);

    $submitted_answers = maybe_unserialize($fetched_data['questions']); 

    $correct = 0;
    
    if (!empty($submitted_answers)) {
        foreach ($submitted_answers as $qid => $user_answer) {
            if (isset($exam_details[$qid]) && $exam_details[$qid]->correct == $user_answer) {
                $correct++;
            }
        }
    }

    $total_questions = count($formatted_qids);
    $percentage = ($total_questions > 0) ? ($correct / $total_questions) * 100 : 0;
    
    // Update the result in the database
    $update = $wpdb->update(
        $mock_results_table,
        ['percentage' => round($percentage, 2)], 
        ['user_email' => sanitize_email($user_email), 'q_paper_id' => intval($q_paper_id)], 
        ['%f'], 
        ['%s', '%d'] 
    );

    if ($update === false) {
        error_log("Database update failed: " . $wpdb->last_error);
    } else {
        error_log("Update successful! Percentage: " . round($percentage, 2) . "%");

    }

    $check_percentage = $wpdb->get_var($wpdb->prepare(
        "SELECT percentage FROM $mock_results_table WHERE user_email = %s ORDER BY q_paper_id DESC LIMIT 1", 
        $user_email
    ));

    // error_log($check_percentage);

    if (!is_null($check_percentage) && $check_percentage !== '') {
        // error_log("user found in the table " . $check_percentage);
        $subject = "Your Mock Exam Results";
        $message = "
        <p>Dear Student,</p>
        <p>You have completed your mock exam. Here are your results:</p>
        <p><strong>Score:</strong> " . round($percentage, 2) . "%</p>
        <p><strong>Total Questions:</strong> $total_questions</p>
        <p><strong>Correct Answers:</strong> $correct</p>
        <p>Thank you for participating!</p>
        <p>Best Regards,<br>Your Exam Team</p>
        ";
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Exam Team <no-abhilash@midnay.com>'
        ];

        $email_sent = wp_mail($user_email, $subject, $message, $headers);

    }
}





