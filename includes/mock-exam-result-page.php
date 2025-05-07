<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_email'])) {
    global $wpdb;
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    // evaluate_mock_exam($email);
    
}

if (isset($_POST['email'])) {
    global $wpdb;
    $mock_results_table = MDNY_MOCK_RESULTS;
    
    $query = $wpdb->prepare(
        "SELECT * FROM $mock_results_table WHERE user_email = %s ORDER BY q_paper_id DESC LIMIT 1",
        $_POST['email']
    );

    $fetched_data = $wpdb->get_row($query, ARRAY_A);
}

// function evaluate_mock_exam($email) {
//     global $wpdb;

//     // Define table names
//     $exam_table_status = MDNY_MOCK_QUESTION_PAPER;
//     $questions_table = MDNY_MOCK_QUESTION;
//     $mock_results_table = MDNY_MOCK_RESULTS;

//     if (empty($email)) {
//         echo '<div class="notice notice-error is-dismissible"><p>Missing user email.</p></div>';
//         return;
//     }

    
//     $query_check_percentage = $wpdb->prepare(
//         "SELECT percentage FROM $mock_results_table WHERE user_email = %s ORDER BY q_paper_id DESC LIMIT 1", 
//         $email
//     );
    
//     $existing_percentage = $wpdb->get_var($query_check_percentage);

   
//     if (!is_null($existing_percentage) && $existing_percentage !== '') {
//         error_log("User $email already has a recorded percentage ($existing_percentage). Skipping recalculation.");
//         return;
//     }

    
//     $query_to_get_paperID = $wpdb->prepare(
//         "SELECT * FROM $mock_results_table WHERE user_email = %s ORDER BY q_paper_id DESC LIMIT 1", 
//         $email
//     );
    
//     $fetched_data = $wpdb->get_row($query_to_get_paperID, ARRAY_A);

//     if (!$fetched_data || empty($fetched_data['q_paper_id'])) {
//         return; 
//     }

//     $q_paper_id = intval($fetched_data['q_paper_id']);

    
//     $query_exam_questions = $wpdb->prepare(
//         "SELECT question_ids FROM $exam_table_status WHERE q_paper_id = %d", 
//         $q_paper_id
//     );
//     $get_exam_questions = $wpdb->get_var($query_exam_questions);


//     $exam_question_ids = maybe_unserialize($get_exam_questions);
    
   
//     $formatted_qids = [];
//     $question_user_map = []; 

//     if (is_array($exam_question_ids)) {
//         foreach ($exam_question_ids as $entry) {
//             if (isset($entry['question_id']) && isset($entry['user_id'])) {
//                 $formatted_qids[] = $entry['question_id'];
//                 $question_user_map[$entry['question_id']] = $entry['user_id'];
//             }
//         }
//     }

 
//     $exam_details = $wpdb->get_results("SELECT qid, correct FROM $questions_table", OBJECT_K);

    
//     $submitted_answers = maybe_unserialize($fetched_data['questions']); 

//     $correct = 0;
    
//     if (!empty($submitted_answers)) {
//         foreach ($submitted_answers as $qid => $user_answer) {
          
//             if (isset($exam_details[$qid]) && $exam_details[$qid]->correct == $user_answer) {
//                 $correct++;
//             }
//         }
//     }

    
//     $total_questions = count($formatted_qids);
//     $percentage = ($total_questions > 0) ? ($correct / $total_questions) * 100 : 0;
//     $update = $wpdb->update(
//         $mock_results_table,
//         ['percentage' => round($percentage, 2)], 
//         ['user_email' => sanitize_email($email), 'q_paper_id' => intval($q_paper_id)], 
//         ['%f'], 
//         ['%s', '%d'] 
//     );

//     if ($update === false) {
//         error_log("Database update failed: " . $wpdb->last_error);
//     } else {
//         error_log("Update successful! Percentage: " . round($percentage, 2) . "%");
//     }
// }






?>

<head>
    <div class="head-logo">
        <?php get_header( );?>
       

    </div>
</head>

<body>
    <div class="mock-exam-result-wrap">
        <div class="form-container">
            <form method="post" >
                <label for="email">Enter your email to see the results</label>
                <input type="email" id="email" name="email" placeholder="your@email.com" required>
                <input type="submit" name="submit_email" value="Show Result">
            </form>
    </div>

        <?php

if (!empty($fetched_data) && isset($fetched_data['percentage'])) {
    global $wpdb;

    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

    if (!empty($email)) {
        $table_name = MDNY_MOCK_RESULTS; 
        $exam_table = MDNY_MOCK_QUESTION_PAPER;

        
        
        // Fetch results only for the given email
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_email = %s ORDER BY q_paper_id DESC",
            $email
        );

        $results = $wpdb->get_results($query, ARRAY_A);

            if (!empty($results)) {
                echo '<table class="results-table" border="1" cellpadding="10" cellspacing="0">';
                echo '<thead>';
                echo '<tr>';
                echo '<th class="table-header">Exam Name</th>';
                echo '<th class="table-header">User Email</th>';
                echo '<th class="table-header">Score (%)</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
            
            
                foreach ($results as $row) {

                    $exam_name_query = $wpdb->prepare(
                        "SELECT question_paper_name FROM $exam_table WHERE q_paper_id = %d", 
                        $row['q_paper_id']
                    );
                    
                    $val = $wpdb->get_row($exam_name_query, ARRAY_A);
                    
                    _console($val);

                    $formatted = rtrim(rtrim(number_format(floatval($row['percentage']), 5), '0'), '.');

                    echo '<tr class="table-row">';
                    echo '<td class="table-data">' . esc_html($val['question_paper_name'] ? $val['question_paper_name'] : 'No Exam Found!' ) . '</td>';
                    echo '<td class="table-data">' . esc_html($row['user_email']) . '</td>';
                    echo '<td class="table-data">' . esc_html($formatted) . '%</td>';
                    echo '</tr>';
                }
            
                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<p class="no-results">No exam results found for this email.</p>';
            }
        } else {
            echo '<p class="invalid-email">Please enter a valid email.</p>';
        }
}

        

        ?>
 </div>
 <?php 
 get_footer();

