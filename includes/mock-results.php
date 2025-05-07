<?php

class mdny_mock__exam_results extends WP_List_Table {
	function get_exam_data() {
        global $wpdb;
    
        $results_table = MDNY_MOCK_RESULTS; 
        $exam_table = MDNY_MOCK_QUESTION_PAPER; 
    
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $exam_name = isset($_REQUEST['exam_name']) ? sanitize_text_field($_REQUEST['exam_name']) : '';
        $category = isset($_REQUEST['category']) ? sanitize_text_field($_REQUEST['category']) : '';
    
       
        $select = "SELECT r.result_id, r.user_email as email, r.category as category, 
                          r.q_paper_id as paper_id, r.percentage as percentage, 
                          r.phone_number as number, r.exam_submission_date as attended_date, e.question_paper_name as exam_name 
                   FROM $results_table r
                   LEFT JOIN $exam_table e ON r.q_paper_id = e.q_paper_id";
    
        
        $where = " WHERE 1=1"; 
    
        if (!empty($search)) {
            $where .= $wpdb->prepare(" AND e.question_paper_name LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }
    
        if (!empty($exam_name)) {
            $where .= $wpdb->prepare(" AND r.q_paper_id LIKE %s", '%' . $wpdb->esc_like($exam_name) . '%');
        } elseif ($exam_name === null) {
            wp_safe_redirect(admin_url('admin.php?page=mock-test-results'));
            exit;
        }
    
        if (!empty($category)) {
            $where .= $wpdb->prepare(" AND r.category LIKE %s", '%' . $wpdb->esc_like($category) . '%');
        } elseif ($category === null) {
            wp_safe_redirect(admin_url('admin.php?page=mock-test-results'));
            exit;
        }
    
        // ORDER BY clause
        $orderby = ' ORDER BY r.result_id DESC';
        if (isset($_REQUEST['orderby']) && !empty($_REQUEST['orderby'])) {
            $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'ASC';
            $orderby = " ORDER BY " . esc_sql($_REQUEST['orderby']) . " " . esc_sql($order);
        }
    
        // Final SQL query
        $sql = $select . $where . $orderby;
    
        // Execute the query
        $exams = $wpdb->get_results($sql);
        $data = json_decode(json_encode($exams), true);
    
        return $data;
    }
    
    
	
  function get_columns(){
        $columns = array(
		// 'cb'                => '<input type="checkbox" class="wlt-deleted"/>',
        'email'            => 'User Email', 
        'category'         => 'Category',
        'paper_id'         => 'Exam Name',       
		'percentage'       => 'Percentage',
        'number'           => 'Phone Number',
        'attended_date'    => 'Attended Date',
        'metabox'          => 'Action'
        
        );
        return $columns;
    }
    function column_default( $item, $column_name ) {
        global $wpdb;
            switch( $column_name ) { 
                // case 'cb':
                // return $this->column_cb($item);
                case 'metabox':
                    return $this->all_exam_action_buttons($item);
                case 'percentage' :  
                    return $this->percentage_val($item);        
                case 'paper_id':
                    return $this->exam_name($item);      
                case 'email':
                case 'category':
                case 'number':
                    case 'attended_date':
                return $item[ $column_name ];
                default:
                return print_r( $item, true ) ; 
            }
    }

    public function column_title(  $item ) {
        
        $edit_link = admin_url( 'admin.php?page=view-mock-questions&edit=' .  $item['qid']);
        $output    = '';
        // Title.
        $output .= '<strong><a href="' . esc_url( $edit_link ) . '" class="row-title">' . esc_html( $item['title']   ) . '</a></strong>';

        //Get actions.
        $actions = array(
            'edit'   => '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'View Question', 'MDNYA' ) . '</a>',
           
        );
    
        $row_actions = array();
    
        foreach ( $actions as $action => $link ) {
            $row_actions[] = '<span class="' . esc_attr( $action ) . '">' . $link . '</span>';
        }
    
        $output .= '<div class="row-actions">' . implode( ' | ', $row_actions ) . '</div>';
        return $output;
    }
	
	
    function prepare_items() {
        $columns = $this->get_columns();
        //   $this->process_bulk_action();
        $data = $this->get_exam_data();
        usort( $data, array( &$this, 'sort_data' ) );
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args(
        array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
            ));
        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
        $this->items = $data;
    }
	
	
    function column_cb($item) {
        $search = isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : '';
            return sprintf(
                '<input type="checkbox" name="id[]" class="wlt-deleted"/>',
                $item['result_id']
            );
    }


    function percentage_val($item){
        global $wpdb;

        $percentage = $item['percentage'];
        if($percentage){
            $integerValue = round($percentage) . "%";
            return $integerValue;
        }else{
            return "Null";
        }

    }


    function extra_tablenav($which) {
        global $wpdb;
        $table_name = MDNY_MOCK_QUESTION_PAPER;
        $exams = $wpdb->get_results("SELECT DISTINCT q_paper_id FROM $table_name ORDER BY q_paper_id DESC");
        $category = $wpdb->get_results("SELECT DISTINCT category FROM $table_name ORDER BY category DESC");
        if ($which == 'top') {
            $selected_type = isset($_REQUEST['exam_name']) ? $_REQUEST['exam_name'] : '';
            if ($exams) {
                echo '<div class="alignleft actions custom">';
                echo '<select name="exam_name">';
                echo '<option value="">All Exams</option>';
                foreach ($exams as $exam) {
                    $query = $wpdb->prepare(
                        "SELECT question_paper_name FROM $table_name WHERE q_paper_id = %d", 
                        $exam->q_paper_id  
                    );
                    $data = $wpdb->get_var($query);
                    $selected = ($data == $selected_type) ? 'selected="selected"' : '';
                    echo '<option value="' . esc_attr( $exam->q_paper_id ) . '" ' . $selected . '>' . esc_html($data) . '</option>';
                }
                echo '</select>';
                 echo '<button class="all-exams-filter button" name="filter_action">Filter</button>';
                echo '</div>';
            } else {
                echo '<div class="alignleft actions">';
                echo '<p>No Paper Types Available.</p>';
                echo '</div>';
            }

            if ($category) {
                echo '<div class="alignleft actions custom">';
                echo '<select name="category">';
                echo '<option value="">All Categories</option>';

                foreach ($category as $cat) {
                    $selected = ($cat->category == $selected_type) ? 'selected="selected"' : '';
                    echo '<option value="' . esc_attr( $cat->category ) . '" ' . $selected . '>' . esc_html($cat->category) . '</option>';
                }

                echo '</select>';
                 echo '<button class="all-exams-filter button" name="filter_action">Filter</button>';
                echo '</div>';
            } else {
                echo '<div class="alignleft actions">';
                echo '<p>No Paper Types Available.</p>';
                echo '</div>';
            }
        }
    }


    function exam_name($item) {
        global $wpdb;
        $table_name = MDNY_MOCK_QUESTION_PAPER;
        // Prepare and execute the query
        $query = $wpdb->prepare(
            "SELECT question_paper_name FROM $table_name WHERE q_paper_id = %d", 
            $item['paper_id']
        );

        $data = $wpdb->get_var($query);
        return $data; // Returns the question_paper_name directly
    }



    function all_exam_action_buttons($item) {
        global $wpdb;

        $result_id = $item['result_id'];
        $result_table = MDNY_MOCK_RESULTS;
        $exam_result_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $result_table WHERE result_id = %d",
                $result_id
            )
        );
        if ($wpdb->get_var("SHOW TABLES LIKE '{$result_table}'") !== null) {
            if ($exam_result_exists > 0) {
                return '<a class="all-exams-actions button" href="' . admin_url('admin.php?page=mock-test-results&show=exam_report&result_id=' . $result_id) . '">' . esc_html__('View Report', 'MDNYA') . '</a>';
            } else {
                return "User results was not saved.";
            }
        }
    }

}


class Exam_result_actions{

    public function view_student_mock_result($result_id) {
        global $wpdb;
        $questions_table = MDNY_MOCK_QUESTION;
        $result_table = MDNY_MOCK_RESULTS;
        $single_name = MDNY_MOCK_QUESTION_PAPER;
        $topics_table = MDNY_MOCK_TOPICS;
        // Fetch result data
        $query = $wpdb->get_row($wpdb->prepare("SELECT * FROM $result_table WHERE result_id = %d", $result_id));
        if (!$query) {
            echo "No result found.";
            return;
        }
        
        $q_paper_id = $query->q_paper_id;
        
        // Fetch the exam details
        $exam_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM $single_name WHERE q_paper_id = %d", $q_paper_id)); 
        $question_counts = maybe_unserialize($exam_details->question_ids);
        
        if (!empty($question_counts)) {
            $question_counts = count($question_counts);
        } else {
            $question_counts = "N/A";
        }
        
        // Fetch the questions and user email
        $query = $wpdb->get_row($wpdb->prepare("SELECT questions, user_email, topics FROM $result_table WHERE result_id = %d AND q_paper_id = %d", $result_id, $q_paper_id));
        
        if (!$query || empty($query->questions)) {
            echo "No result data found.";
            return;
        }
        
        $data_array = maybe_unserialize($query->questions);
        
        if (!is_array($data_array)) {
            echo "Invalid data format.";
            return;
        }
        
        // Retrieve topic-wise results stored in the 'topics' column
        $topic_results = $query->topics; // Assuming the 'topics' column stores topic results in JSON
        $topic_results = json_decode($topic_results);
        // _console();
        // Map question IDs and user answers
        $question_ids = array_keys($data_array);
        $selected_answers = array_values($data_array);
        
        if (empty($question_ids)) {
            return;
        }
        
        $mapped_ids = array_map('intval', $question_ids);
        $question_ids_string = implode(',', $mapped_ids);
        $questions = $wpdb->get_results("SELECT * FROM $questions_table WHERE qid IN ($question_ids_string)");
        
        if (!$questions) {
            echo "No questions found.";
            return;
        }
        
        $total_score_1_count = 0;
        ?>
        <div class="wrap">
            <div class="exam-details-page">
                <div class="exam-details-wrapper">
                    <table id="myTable-mock" class="exam-details-table">
                        <thead>
                            <tr>
                                <th>Question ID</th>
                                <th>Question</th>
                                <th>Given Answer</th>
                                <th>Correct Answer</th>
                                <th>Topic Name</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($questions as $question): ?>
                                <tr>
                                    <td><?php echo esc_html($question->qid); ?></td>
        
                                    <?php $filtered_question = stripcslashes($question->question); ?>
                                    <td><?php echo apply_filters('the_content', $filtered_question); ?></td>
                                    <td>
                                        <?php 
                                        $given_answer = isset($data_array[$question->qid]) ? $data_array[$question->qid] : null;
                                        $options = maybe_unserialize($question->options);
        
                                        echo isset($options[$given_answer]) ? esc_html($options[$given_answer]) : "N/A";
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $correct_answer = isset($question->correct) ? $question->correct : null;
                                        echo isset($options[$correct_answer]) ? esc_html($options[$correct_answer]) : "N/A";
                                        ?>
                                    </td>
                                    <td>
                                    <?php 
                                    $topics = isset($question->topic_ids) ? $question->topic_ids : null;
                                    $topic_ids = maybe_unserialize($topics);
                                    
                                    if (!empty($topic_ids)) {
                                        
                                        $topic_ids = is_array($topic_ids) ? $topic_ids : explode(',', $topic_ids);
                                        
                                        
                                        $sanitized_ids = array_map('absint', $topic_ids);
                                        $ids_string = implode(',', $sanitized_ids);
                                    
                                        
                                        $topic_names = $wpdb->get_col(
                                            "SELECT topic_name FROM $topics_table WHERE topic_id IN ($ids_string)"
                                        );
                                        
                                        echo !empty($topic_names) ? esc_html(implode(', ', $topic_names)) : 'N/A';
                                    } else {
                                        echo 'N/A';
                                    }
                                    
                                    ?>
                                </td>
                                    <td>
                                        <?php 
                                        $score = ($given_answer === $correct_answer) ? 1 : 0;
                                        echo esc_html($score);
                                        $total_score_1_count += $score;
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                     <!-- Display Topic Results -->
                     <?php if (!empty($topic_results)) : ?>
                        <div class="topic-results">
                            <h3>Topic-wise Results</h3>
                            <table class="topic-results-table">
                                <thead>
                                    <tr>
                                        <th style="text-align:center;">Topic ID</th>
                                        <th style="text-align:center;">Topic Name</th>
                                        <th style="text-align:center;">Score</th>
                                        <th style="text-align:center;">Correct Answers</th>
                                        <th style="text-align:center;">Total Questions</th>
                                        <th style="text-align:center;">Total Topic Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topic_results as $topic_id => $topic_data) : ?>
                                        <tr>
                                            <td style="text-align:center;"><?php echo esc_html($topic_id); ?></td>
                                            <td style="text-align:center;">
                                                <?php 
                                                
                                                $topic_name = $wpdb->get_var(
                                                    $wpdb->prepare(
                                                        "SELECT topic_name FROM $topics_table WHERE topic_id = %d",
                                                        $topic_id
                                                    )
                                                );
                                                echo $topic_name ? esc_html($topic_name) : 'Topic ' . esc_html($topic_id);
                                                ?>
                                            </td>
                                            <td style="text-align:center;"><?php echo esc_html($topic_data->correct); ?></td>
                                            <td style="text-align:center;"><?php echo esc_html($topic_data->correct); ?></td>
                                            <td style="text-align:center;"><?php echo esc_html($topic_data->total); ?></td>
                                            <td style="text-align:center;">
                                                <?php 
                                                $percentage = ($topic_data->correct / $topic_data->total) * 100;
                                                echo esc_html(round($percentage)) . '%'; 
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
        
                    <div class="exam-details-overview">
                        <p>Total Questions in this Exam: <span class="total-count"><?php echo esc_html($question_counts); ?></span></p>
                        <p>Total Correct Answers: <span class="total-count"><?php echo esc_html($total_score_1_count); ?></span></p>
        
                        <?php
                        echo '<center class="display-name-center">
                              <p>Exam Name: <span>' . esc_html($exam_details->question_paper_name) . '</span></p>
                              <p>User email: <span>' . esc_html($query->user_email) . '</span></p>
                              </center>';
                        ?>
                    </div>
                    
                    
                    <?php if (is_admin()) { ?>
                        <a class="button back-to-all-reports" href="<?php echo esc_url(admin_url('admin.php?page=mock-test-results')); ?>">Back to all Reports</a> 
                    <?php } else { ?> 
                        <a class="button back-to-premium-exams" href="<?php echo esc_url(home_url('/dashboard/premium-exam')); ?>">Back To Exams</a>                 
                    <?php } ?>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function(){
                $('#myTable-mock tbody tr').each(function(){
                    var givenAnswer = $(this).find('td:eq(2)').text().trim();
                    var correctAnswer = $(this).find('td:eq(3)').text().trim();
                    
                    if (givenAnswer === correctAnswer) {
                        $(this).css('background-color', '#04cd041f'); // Green for correct
                    } else {
                        $(this).css('background-color', '#e9000063'); // Red for incorrect
                    }
                });
            });
        </script>
        <?php
    }
    
    
    

}