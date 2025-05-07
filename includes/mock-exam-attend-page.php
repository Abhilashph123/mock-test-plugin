<?php?>
<head>
    <div class="head-logo">
        <?php wp_head();?>

    </div>
</head>

<body>
    <div class="header-section">
        <div class="vallath-desk">
            <div class="desk-header si-container">
                <div class="desk-logo logo">

                    <?php
							if ( function_exists( 'the_custom_logo' ) ) {
								the_custom_logo();
							}
						?>

                </div>


            </div>

        </div>
    </div>

<body>

    <?php
        

      
        global $wpdb;
        $EXAM_table_name = MDNY_MOCK_QUESTION_PAPER;
        $QUESTION_table_name = MDNY_MOCK_QUESTION;
            if (isset($_POST['submit_answer'])) {
                $first_form_data = $_POST; 
               
                if( isset($_POST['status']) && $_POST['status'] == 'Premium'){
                    submit_premium_exam($first_form_data);
                   
                }else{
                    get_user_data_form($first_form_data); 
                    
                }
                exit;
                
               
            }else {
               

                 if (isset($_POST['q_paper_id'])) {
                    $q_paper_id = intval($_POST['q_paper_id']);

                    $exam = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM $EXAM_table_name WHERE q_paper_id = %d",
                            $q_paper_id
                        )
                    );
                    $max_hours = $exam->max_hours;

                    echo '<div class="exam-details-wrap">';
                    echo '<p style="padding: 10px;">Exam name : <span class="exam-name-span">'. $exam->question_paper_name.'</span></p>';
                    echo '<p style="padding: 10px;">Maximum Hours : <span class="exam-name-span">'. $exam->max_hours.' hrs</span></p>';
                    echo '</div>';

?>
                <script type="text/javascript">
                var maxHours = "<?php echo $max_hours; ?>"; 
                var now = new Date().getTime(); 
                var endTime = now + (maxHours * 60 * 60 * 1000); 

                // console.log("Max Hours: " + maxHours);
                // console.log("End Time: " + new Date(endTime).toLocaleString());

                function initializeCountdown(endTime) {
                    var countdownInterval = setInterval(function() {
                        var now = new Date().getTime();
                        var remainingTime = endTime - now;

                        var hours = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        var minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));
                        var seconds = Math.floor((remainingTime % (1000 * 60)) / 1000);

                        document.getElementById("time-remaining-d").innerHTML = hours + "h " + minutes + "m " + seconds + "s ";
                        document.getElementById("time-remaining").innerHTML = hours + "h " + minutes + "m " + seconds + "s ";

                        if (remainingTime <= 0) {
                            clearInterval(countdownInterval);
                            document.getElementById("time-remaining-d").innerHTML = "Time's up! Submitting...";
                            document.getElementById("time-remaining").innerHTML = "Time's up! Submitting...";

                            // Auto-submit logic
                            document.getElementById("mock-submit-answers-btn").click();
                            document.getElementById("mock-confirm-yes").click();
                        }
                    }, 1000);
                }

                initializeCountdown(endTime);
            </script>

            <div class='si-container-exam-page'>
            <div class="exam-page">

            <div id="customAlertModal" class="modal" style="display:none;">
    <div class="modal-content">
    <div id="countdown-timer">
                <p>Time Remaining: <span id="time-remaining"></span></p>
            </div>
        <div id="mock-summary-review">

        </div>
        <p>Once you submit the answers you cannot attend this exam again. Are you sure you want to submit your answers?</p>
        <button id="mock-confirm-yes">Yes</button>
        <button id="mock-confirm-no">No</button>
    </div>
</div>
            <?php


if ($exam) {
    $question_ids = maybe_unserialize($exam->question_ids);

    if (is_array($question_ids) && !empty($question_ids)) {
        // Extract only the question_id from each entry
        $formatted_qids = array_map(function($entry) {
            return isset($entry['question_id']) ? intval($entry['question_id']) : null;
        }, $question_ids);

        // Remove null values to avoid SQL errors
        $formatted_qids = array_filter($formatted_qids);

        if (!empty($formatted_qids)) {
            $placeholders = implode(',', array_fill(0, count($formatted_qids), '%d'));
            $query = "
                SELECT qid, question, options
                FROM $QUESTION_table_name
                WHERE qid IN ($placeholders)
                ORDER BY FIELD(qid, " . implode(',', $formatted_qids) . ")
            ";

            $prepared_query = $wpdb->prepare($query, ...$formatted_qids);
            $questions = $wpdb->get_results($prepared_query);

            $count_q = 1;
            if ($questions) {
                echo '<form id="exam-form" class="exam-form" method="post">';
                echo '<input type="hidden" name="q_paper_id" value="' . esc_attr($q_paper_id) . '">';
                echo '<input type="hidden" name="category" value="' . esc_attr($exam->category) . '">';
                // echo '<input type="hidden" name="status" value="' . esc_attr($_POST['status'] ? $_POST['status'] : '') . '">';

                foreach ($questions as $index => $question) {
                    $filtered_question = stripslashes($question->question);
                    $filtered_question = apply_filters('the_content', $filtered_question);

                    echo '<div class="mock-question mock-question-' . esc_attr($question->qid) . '" style="display: ' . ($index === 0 ? 'block' : 'none') . ';">';
                    echo '<div class="mock-question-text">';
                    echo '<p><strong>Question ' . $count_q . ': </strong><br>' . $filtered_question . '</p>';
                    echo '</div>';

                    $options = maybe_unserialize($question->options);
                    if (is_array($options) && !empty($options)) {
                        echo '<ul class="option-grid">';
                        foreach ($options as $key => $option) {
                            echo '<li>';
                            echo '<label>';
                            echo '<input type="radio" name="answers[' . esc_attr($question->qid) . ']" value="' . esc_attr($key) . '"> ';
                            echo esc_html($option);
                            echo '</label>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No options found for this question.</p>';
                    }

                    echo '</div>';
                    $count_q++;
                }

                echo '<button id="mock-submit-answers-btn" name="submit_answer">Submit</button>';
                echo '</form>';

                // Navigation Buttons
                echo '<div id="exam-navigation">';
                echo '<button class="exam-nav-btn" id="mock-previous-question-butn">Previous</button>';
                echo '<button class="exam-nav-btn" id="mock-next-question-butn">Next</button>';
                echo '<button class="exam-nav-btn" id="mock-clear-question-btn">Clear</button>';
                echo '<button class="exam-nav-btn" id="mock-save-next-question-btn" data-index="0">Save & Next</button>';
                echo '<button class="exam-nav-btn" id="mock-save-and-mark-question-btn" data-index="0">Save & Mark For Review</button>';
                echo '<button class="exam-nav-btn" id="mock-show-summary-btn" data-index="0">Show summary</button>';
                echo '</div>';

                // Summary Popup
                echo '<div id="mock-summary-popup" class="mock-popup-wrap" style="display: none;">
                        <center><h2>Summary</h2></center> <hr>
                        <p id="mock-summary-content"></p>
                        <button class="exam-nav-btn" id="mock-close-summary-btn">Close Summary</button>
                    </div>';
            } else {
                echo '<p>No questions found for this exam.</p>';
            }
        } else {
            echo '<p>No valid question IDs found for this exam.</p>';
        }
    } else {
        echo '<p>No question IDs found for this exam.</p>';
    }
} else {
    echo '<p>No exam found for ID: ' . esc_html($q_paper_id) . '</p>';
}

            } else {
                echo "No Exam found";
            }
        }
        ?>

        </div>

        <div class="right-container">
            <div id="countdown-timer">
                <p>Time Remaining: <span id="time-remaining-d"></span></p>
            </div>



            <div class="attending-instruction-show">
                <div class="checkbox-wrapper-input-right">
                    <div class="instruct-holder">
                        <p class="instruction-val-save-and-next" id="instruction-val-save-and-next">99</p>
                        <label for="">Answered and Saved Questions</label>
                    </div>
                    <div class="instruct-holder">
                        <p class="instruction-val-save-and-mark" id="instruction-val-save-and-mark">99</p>
                        <label for="">Saved and Marked For Review (will be considered for evaluation)</label>
                    </div>
                    <div class="instruct-holder">
                        <p class="instruction-val-not-visited">99</p>
                        <label for="">Not Visited</label>
                    </div>
                    <div class="instruct-holder">
                        <p class="instruction-val-cviewing" id="instruction-val-cviewing">99</p>
                        <label for="">Not Answered Question</label>
                    </div>
                </div>
            </div>
            <div class="all-exam-question-nav">
            <?php
            $question_count = 1;
 
            foreach ($questions as $question) {
                echo '<div class="checkbox-wrapper-input">';
                echo '<a href="#" class="mock-question-link" data-question-id="' . esc_attr($question->qid) . '">' . $question_count . '</a>';
                echo '</div>';
                $question_count++;
            }
        
            ?>
            </div>
        </div>
    </div>
    <?php
get_footer();
?>