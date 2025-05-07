<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}



if (isset($_POST['import_csv_to_exam']) && !empty($_FILES['csv_file']['tmp_name'])) {
    $q_paper_id = $_POST['q_paper_id'];
    $user_id = $_POST['current_user'];
    $csv_file = $_FILES['csv_file'];

    $Mock_exams = new Mock_exams();
   
    $Mock_exams->import_mock_questions($q_paper_id, $csv_file, $user_id);
}



add_action('admin_init', 'handle_mock_question_removal');
function handle_mock_question_removal() {
    global $wpdb;
    $table_name = MDNY_MOCK_QUESTION_PAPER;

    if (isset($_GET['remove_question_from_mock']) && isset($_GET['question_id']) && isset($_GET['q_paper_id'])) {
        $question_id_to_delete = intval($_GET['question_id']);
        $q_paper_id = intval($_GET['q_paper_id']);

        $mock_exams_data = $wpdb->get_row(
            $wpdb->prepare("SELECT question_ids FROM $table_name WHERE q_paper_id = %d", $q_paper_id),
            ARRAY_A
        );

        $question_ids = maybe_unserialize($mock_exams_data['question_ids']);

        if (!empty($question_ids) && is_array($question_ids)) {
            foreach ($question_ids as $key => $entry) {
                if (isset($entry['question_id']) && $entry['question_id'] == $question_id_to_delete) {
                    unset($question_ids[$key]);
                    break;
                }
            }

            $question_ids = array_values($question_ids);

            $delete_result = $wpdb->update(
                $table_name,
                array('question_ids' => maybe_serialize($question_ids)),
                array('q_paper_id' => $q_paper_id),
                array('%s'),
                array('%d')
            );

            $redirect_url = admin_url('admin.php?page=all-mock-exams&action=edit_mock_exam&q_paper_id=' . $q_paper_id);

            // Optionally set a transient or $_GET flag to show a success message on redirected page
            wp_safe_redirect($redirect_url);
            exit;
        }
    }


 
}


class mdny_mock_all_exams extends WP_List_Table {
    
	function get_exam_data() {
        global $wpdb;
    
        $table_name = MDNY_MOCK_QUESTION_PAPER;
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        $paper_type = isset($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : '';
        $exam_type = isset($_REQUEST['type']) ? sanitize_text_field($_REQUEST['exam_type']) : '';
        $category = isset($_REQUEST['category']) ? sanitize_text_field( $_REQUEST['category'] ) : '';
        $selected_author = isset($_REQUEST['author']) ? $_REQUEST['author'] : '';
       
    
        $select = "SELECT q_paper_id, question_paper_name as title, max_hours as hours, paper_type as type, category as category, author as user_id, status as exam_type";
        $where = '';
    
        if (!empty($search)) {
            $where = $wpdb->prepare(" WHERE question_paper_name LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }
    
        if(!empty($paper_type)){
            $where = $wpdb->prepare(" WHERE paper_type LIKE %s", '%' . $wpdb->esc_like($paper_type) . '%');
        }elseif($paper_type === null){
            wp_safe_redirect( admin_url('admin.php?page=all-mock-exams') );
        }

        if(!empty($exam_type)){
            $where = $wpdb->prepare(" WHERE status LIKE %s", '%' . $wpdb->esc_like($exam_type) . '%');
        }elseif($exam_type === null){
            wp_safe_redirect( admin_url('admin.php?page=all-mock-exams') );
        }

        if(!empty($category)){
            $where = $wpdb->prepare(" WHERE category LIKE %s", '%' . $wpdb->esc_like($category) . '%');
        }elseif($category === null){
            wp_safe_redirect( admin_url('admin.php?page=all-mock-exams') );
        }


        if(!empty($selected_author)){
            $where = $wpdb->prepare(" WHERE author LIKE %s", '%' . $wpdb->esc_like($selected_author) . '%');
        }elseif($selected_author === null){
            wp_safe_redirect( admin_url('admin.php?page=all-mock-exams') );
        }


        $orderby = ' ORDER BY q_paper_id DESC';
        $order = '';
    
        if (isset($_REQUEST['orderby']) && !empty($_REQUEST['orderby'])) {
            if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
                $order = $_REQUEST['order'];
            }
            $orderby = " ORDER BY " . $_REQUEST['orderby'] . " " . $order;
        }
    
        $sql = $select . " FROM " . $table_name . $where . $orderby;
    
        $exams = $wpdb->get_results($sql);
        $data = json_decode(json_encode($exams), true);
    
        return $data;
    }
    
	
  function get_columns(){
        $columns = array(
		// 'cb'                => '<input type="checkbox" class="wlt-deleted"/>',
       'title'             => 'Title', 
       'category'          => 'Category',
       'type'              => 'Paper Type',   
       'exam_type'         => 'Exam Type',   
	   'hours'             => 'Maximum Hours',
       'user_id'           => 'Author',
        //  'metabox'           => 'Action'
        
        );
        return $columns;
  }
    function column_default( $item, $column_name ) {
        global $wpdb;
            switch( $column_name ) { 
                case 'cb':
                // return $this->column_cb($item);
                case 'metabox':
                    return $this->all_exam_action_buttons($item);
                   case 'user_id' :
                    return $this->display_author_name($item); 	   
                case 'title':
                case 'category':
                case 'exam_type':
                case 'hours':
                case 'type' :    
                case 'user_id':
                return $item[ $column_name ];
                default:
                return print_r( $item, true ) ; 
            }
    }

	public function column_title(  $item ) {	
        $edit_link = admin_url( 'admin.php?page=all-mock-exams&action=edit_mock_exam&q_paper_id=' .  $item['q_paper_id']  );
	    // $add_question = admin_url( 'admin.php?page=all-mock-exams&action=add_to_exam&q_paper_id=' .  $item['q_paper_id']  );
	    $trash_link = admin_url( 'admin.php?page=all-mock-exams&action=delete_mock_exam&q_paper_id=' .  $item['q_paper_id']  );
	  
        
           
	
        $output    = '';
        // Title.
        $output .= '<strong><a href="' . esc_url( $edit_link ) . '" class="row-title">' . esc_html( $item['title']   ) . '</a></strong>';

        // Get actions.
        $actions = array(
            'edit'   => '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit', 'MDNYA' ) . '</a>',
			// 'add_questions'   => '<a href="' . esc_url( $add_question ) . '">' . esc_html__( 'Add questions excluded from series', 'MDNYA' ) . '</a>',
			'trash'   => '<a href="' . esc_url( $trash_link ) . '">' . esc_html__( 'Trash', 'MDNYA' ) . '</a>',
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
            $item['q_paper_id']
        );
}


function display_author_name($item){
    global $wpdb;
    $user_name = $item['user_id'];

    $user = get_user_by( 'id', $user_name );
// _console($user);
    return !empty($user->data->display_name) ? $user->data->display_name : "";


}


function extra_tablenav($which) {
    global $wpdb;

    $table_name = MDNY_MOCK_QUESTION_PAPER;
    $types = $wpdb->get_results("SELECT DISTINCT paper_type FROM $table_name ORDER BY paper_type DESC");
    $exam_types = $wpdb->get_results("SELECT DISTINCT status FROM $table_name ORDER BY status DESC");
    $authors = $wpdb->get_results("SELECT DISTINCT author FROM $table_name ORDER BY author DESC");
    $categories = $wpdb->get_results("SELECT DISTINCT category FROM $table_name ORDER BY category DESC");


    if ($which == 'top') {

        $selected_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
        $selected_date_created = isset($_REQUEST['date_created']) ? $_REQUEST['date_created'] : '';
        $selected_category = isset($_REQUEST['category']) ? $_REQUEST['category'] : '';
        // $selected_course = isset($_REQUEST['course']) ? $_REQUEST['course'] : '';

        // Start Time Filter
        if ($types) {
            echo '<div class="alignleft actions custom">';
            echo '<select name="type">';
            echo '<option value="">Paper Type</option>';

            foreach ($types as $type) {
                $selected = ($type->paper_type == $selected_type) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($type->paper_type) . '" ' . $selected . '>' . esc_html($type->paper_type) . '</option>';
            }

            echo '</select>';
            //  echo '<button class="all-exams-filter button" name="filter_action">Filter</button>';
            echo '<button class="all-exams-filter button" name="filter_action">Filter</button>';
            echo '</div>';
        } else {
            echo '<div class="alignleft actions">';
            echo '<p>No Paper Types Available.</p>';
            echo '</div>';
        }


        if ($exam_types) {
            echo '<div class="alignleft actions custom">';
            echo '<select name="exam_type">';
            echo '<option value="">Exam Type</option>';

            foreach ($exam_types as $exam_type) {
                $selected = ($exam_type->status == $selected_type) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($exam_type->status) . '" ' . $selected . '>' . esc_html($exam_type->status) . '</option>';
            }

            echo '</select>';
             echo '<button class="all-exams-filter button" name="filter_action">Filter</button>';
            echo '</div>';
        } else {
            echo '<div class="alignleft actions">';
            echo '<p>No Paper Types Available.</p>';
            echo '</div>';
        }

        // Category Filter
        if ($categories) {
            echo '<div class="alignleft actions custom">';
            echo '<select name="category">';
            echo '<option value="">Category</option>';

            foreach ($categories as $category) {
                $selected_category = ($category->category == $selected_category) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($category->category) . '" ' . $selected_category . '>' . esc_html($category->category) . '</option>';
            }

            echo '</select>';
            echo '<button class="all-exams-filter button" name="filter_action">Filter</button>';
            echo '</div>';
        } else {
            echo '<div class="alignleft actions">';
            echo '<p>No Categories available.</p>';
            echo '</div>';
        }

        $selected_author = isset($_REQUEST['author']) ? $_REQUEST['author'] : '';

        if ($authors) {
            echo '<div class="alignleft actions custom">';
            echo '<select name="author">';  
            echo '<option value="">All Authors</option>';

            foreach ($authors as $author) {
               
                $user_info = get_userdata($author->author);
                
                
                $first_name = ($user_info) ? $user_info->first_name : 'Unknown';

                $selected = ($author->author == $selected_author) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($author->author) . '" ' . $selected . '>' . esc_html($first_name) . '</option>';
            }

            echo '</select>';
            echo '<button class="all-exams-filter button" name="filter_action">Filter</button>';
            echo '</div>';
        }
    }
    
}







function all_exam_action_buttons($item) {
    global $wpdb;

    $q_paper_id = $item['q_paper_id'];
    $result_table = MDNY_RESULTS;
    $exam_table_name = MDNY_EXAM_SINGLE . $q_paper_id; 
    // Check if the result table exists and has data for the specific q_paper_id
    $exam_result_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $result_table WHERE q_paper_id = %d",
            $q_paper_id
        )
    );
    if ($wpdb->get_var("SHOW TABLES LIKE '{$exam_table_name}'") !== null) {
        if ($exam_result_exists > 0) {
            return '<a class="all-exams-actions" href="' . admin_url('admin.php?page=reports_menu&show=details&q_paper_id=' . $q_paper_id) . '">' . esc_html__('View Report', 'MDNYA') . '</a>';
        } else {
            return '<a class="all-exams-actions" href="' . admin_url('admin.php?page=all-mock-exams&action=evaluate_exam&q_paper_id=' . $q_paper_id . '&course_id=' . $item['course_id']) . '">' . esc_html__('Evaluate', 'MDNYA') . '</a>';
        }
    }
}



	
}


if (isset($_POST['generate_mockpaper_pdf'])) {
    ob_end_clean();
    $q_paper_id = $_GET['q_paper_id'];
     $single_name = MDNY_MOCK_QUESTION_PAPER;
    $results2 = $wpdb->get_results("SELECT question_paper_name FROM $single_name WHERE q_paper_id = $q_paper_id");
    foreach($results2 as $name){
       $question_paper_name = $name->question_paper_name;

       
    }
    require_once MDNY_MOCKTEST_PATH . '/vendor/autoload.php'; 

    ob_start();
    $Mock_exams = new Mock_exams();
    $Mock_exams->build_exam_mockquestions_pdf($q_paper_id);
    $htmlContent = ob_get_clean();

    
    class PDFWithWatermark extends TCPDF {
        public function Header() {
           
            $this->SetFont('Helvetica', '', 50); 
            $this->SetTextColor(200, 200, 200); 
            $this->SetAlpha(0.2); 
            
            
            $this->StartTransform();
            $this->Rotate(25, $this->getPageWidth()/2, $this->getPageHeight() / 2);
            $this->Text(35, 140, 'VALLATH MOCK TEST'); 
            $this->StopTransform();

            $this->SetAlpha(1); 
        }

        public function Footer() {
            // You can optionally add a footer here if needed
        }
    }

    // Create an instance of the custom PDF class
    $pdf = new PDFWithWatermark();
   
    // Add the first page
    $pdf->AddPage();

    // Add the main content
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Black text for table content
    $pdf->writeHTML($htmlContent, true, true, true, false, '');

    // Output the PDF
    $pdf->Output( $question_paper_name .'.pdf', 'D'); 

    exit;
}

class Mock_exams{


    public function remove_all_questions_from_exam($q_paper_id){
        
        global $wpdb;
        $exam_table = MDNY_MOCK_QUESTION_PAPER;
    
      
        $result = $wpdb->query("UPDATE {$exam_table} SET question_ids = ''");
    
        if ($result !== false) {
            echo '<script>window.location.href="' . admin_url('admin.php?page=all-mock-exams&action=edit_mock_exam&q_paper_id='. $q_paper_id)  . '";</script>';
           
            exit;
        } else {
          
            echo '<div class="notice notice-error"><p>Failed to clear Question IDs.</p></div>';
        }
    }

    public function build_exam_mockquestions_pdf($q_paper_id){
      
        global $wpdb;
        $EXAM_table_name = MDNY_MOCK_QUESTION_PAPER;
        $QUESTION_table_name = MDNY_MOCK_QUESTION;
    
        
        $exam = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $EXAM_table_name WHERE q_paper_id = %d",
                $q_paper_id
            )
        );
    
        echo "<h1>Exam :- (".strtoupper($exam->question_paper_name).")</h1>";
        if ($exam) {
            $question_ids = maybe_unserialize($exam->question_ids);
    
        if (is_array($question_ids) && !empty($question_ids)) {
            // Extract only the question_id from each entry
            $formatted_qids = array_map(function($entry) {
                return isset($entry['question_id']) ? intval($entry['question_id']) : null;
            }, $question_ids);
    
            // Remove null values to avoid SQL errors
            $formatted_qids = array_filter($formatted_qids);
        }
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
                
                if ($questions) {
                    echo '<input type="hidden" name="q_paper_id" value="' . esc_attr($q_paper_id) . '">';
                    
                    foreach ($questions as $index => $question) {
                        $filtered_question = stripcslashes($question->question);
                        $filtered_question = apply_filters('the_content', $filtered_question);
                        
                        echo '<div class="question" id="question-' . $index . '">';
                        echo '<div class="question-text">';
                        echo '<p><strong>Question ' . ($index + 1) . ': </strong>' . $filtered_question . '</p>';
                        echo '</div>';
                        $options = maybe_unserialize($question->options);
    
                        if (is_array($options) && !empty($options)) {
                            echo '<ul class="option-grid">';
                            foreach ($options as $key => $option) {
                                echo '<li>';
                                echo '<label>';
                                echo '<input type="radio" name="answers[' . $question->qid . ']" value="' . esc_attr($key) . '"> ';
                                echo esc_html($option);
                                echo '</label>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p>No options found for this question.</p>';
                        }
    
                        echo '</div>';
                    }
                    
                } else {
                    echo '<p>No questions found for this exam.</p>';
                }
            } else {
                echo '<p>No question IDs found for this exam.</p>';
            }
        } else {
            echo '<p>No exam found for ID: ' . esc_html($q_paper_id) . '</p>';
        }
    }



    public function display_edit_mock_exam($q_paper_id){
        global $wpdb;
        $table_name = MDNY_MOCK_QUESTION_PAPER;
        $mock_exams_data  = get_mock_question_paper_data_id($q_paper_id);
        _console($mock_exams_data);

      

        if (isset($_POST['mock_paper_update'])) {
            $Mock_exams = new Mock_exams();
            $question_paper_name  = $_POST['paper_name']; 
            $paper_type  = $_POST['paper_type']; 
            $max_hours  = $_POST['max_hours']; 
            $category_name  = $_POST['category_name'];
    
            $Mock_exams->update_mock_exam($q_paper_id, $question_paper_name, $paper_type, $max_hours, $category_name);
            return; 
        }
        
      
        ?>
        <div class="wrap">
            <h1>Add New Mock Paper</h1>
                <div class="main-wrap-mock">
                    <div class="import-question common-cont-wrap">
                        <label for="">Import Questions (CSV)</label>
                        <form method="post" enctype="multipart/form-data">
                            <input type="file" name="csv_file" accept=".csv" required>
                            <input type="hidden" name="q_paper_id" value="<?php echo $q_paper_id?>"> 
                            <input type="hidden" name="current_user" value="<?php echo  get_current_user_id()?>"> 
                            <button type="submit" name="import_csv_to_exam">Import CSV</button>
                        </form>
                    </div>
               

                    <form method="post">
                        <div class="paper-name common-cont-wrap">
                            <label> Edit Paper Name</label>
                            <input type="text" name="paper_name" value="<?php echo $mock_exams_data['question_paper_name'] ?>">
                        </div> <br>
                        <div class="max-hours common-cont-wrap">
                            <label> Add Max Hours For Exam</label>
                            <input type="text" placeholder="eg:- 1 or 2" name="max_hours" value="<?php echo $mock_exams_data['max_hours'] ?>">
                        </div> <br>
                        <div class="paper-type common-cont-wrap">
                            <label>Paper Type</label>
                            <select id="mock-paper-type" name="paper_type">
                                <option value="Easy" <?php echo ($mock_exams_data['paper_type'] ?? '') == 'Easy' ? 'selected' : ''; ?>>Easy</option>
                                <option value="Medium" <?php echo ($mock_exams_data['paper_type'] ?? '') == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="Hard" <?php echo ($mock_exams_data['paper_type'] ?? '') == 'Hard' ? 'selected' : ''; ?>>Hard</option>
                            </select>
                        </div> <br>

                        <div class="paper-type common-cont-wrap">
                            <label>Paper Type</label>
                            <select id="mock-paper-type" name="status">
                                <option value="Ordinary" <?php echo ($mock_exams_data['status'] ?? '') == 'Ordinary' ? 'selected' : ''; ?>>Ordinary</option>
                                <option value="Premium" <?php echo ($mock_exams_data['status'] ?? '') == 'Premium' ? 'selected' : ''; ?>>Premium</option>
                                
                            </select>
                        </div> <br>


                        <div class="category-name common-cont-wrap">
                            <label>Category Name</label>
                            <input type="text" name="category_name" value="<?php echo $mock_exams_data['category'] ?>">
                        </div> <br>
                        <div class="mock-paper-submit common-cont-wrap">
                            <input type="submit" name="mock_paper_update" value="Update Paper" class="paper-submit-button">
                            
                        </div>
                    </form>
                </div>
                <div class="mock-edit-questions-table">   
                    <?php $this->display_mock_edit_questions($q_paper_id) ?>
                </div>
    </div>
    <?php


    }


    public function display_mock_edit_questions($q_paper_id) {
        
        global $wpdb;


        

        $mock_exams_data  = get_mock_question_paper_data_id($q_paper_id);
        $unserialized_questions = maybe_unserialize($mock_exams_data['question_ids']);
        $formatted_qids = [];
        $question_user_map = [];
    
        if ($mock_exams_data && !empty($unserialized_questions)) {
            if (is_array($unserialized_questions)) {
                foreach ($unserialized_questions as $entry) {
                    if (isset($entry['question_id']) && isset($entry['user_id'])) {
                        $formatted_qids[] = $entry['question_id'];
                        $question_user_map[$entry['question_id']] = $entry['user_id'];
                    }
                }
            }
        }
    
        if (isset($_POST['save_table_order'])) {
           
            if (isset($_POST['question_ids']) && is_array($_POST['question_ids'])) {
                $new_order = $_POST['question_ids'];
                
    
                $updated_question_ids = [];
                foreach ($new_order as $qid) {
                    if (isset($question_user_map[$qid])) {
                        $updated_question_ids[] = [
                            'question_id' => intval($qid),
                            'user_id' => intval($question_user_map[$qid]),
                        ];
                    }
                }
    
                if (!empty($updated_question_ids)) {
                    $serialized_question_ids = maybe_serialize($updated_question_ids);
                    
    
                    $table_name = MDNY_MOCK_QUESTION_PAPER;
                    $result = $wpdb->update(
                        $table_name,
                        ['question_ids' => $serialized_question_ids],
                        ['q_paper_id' => intval($q_paper_id)],
                        ['%s'],
                        ['%d']
                    );
    
                    if ($result !== false) {
                        
                       
                    } else {
                        
                    }
                }
            }
        }


        $mock_exams_data  = get_mock_question_paper_data_id($q_paper_id);
        $unserialized_questions = maybe_unserialize($mock_exams_data['question_ids']);
        $formatted_qids = [];
        $question_user_map = [];
    
        if ($mock_exams_data && !empty($unserialized_questions)) {
            if (is_array($unserialized_questions)) {
                foreach ($unserialized_questions as $entry) {
                    if (isset($entry['question_id']) && isset($entry['user_id'])) {
                        $formatted_qids[] = $entry['question_id'];
                        $question_user_map[$entry['question_id']] = $entry['user_id'];
                    }
                }
            }
        }
        if (!empty($formatted_qids)) {
            $questions_table = MDNY_MOCK_QUESTION;
            $topics_table = MDNY_MOCK_TOPICS;
    
            $placeholders = implode(',', array_fill(0, count($formatted_qids), '%d'));
            $qid_order = implode(',', array_map('intval', $formatted_qids));
    
            $query = "
                SELECT q.*, t.topic_name 
                FROM $questions_table AS q
                LEFT JOIN $topics_table AS t
                ON FIND_IN_SET(t.topic_id, q.topic_ids) > 0
                WHERE q.qid IN ($placeholders)
                ORDER BY FIELD(q.qid, $qid_order)";
    
            $questions = $wpdb->get_results($wpdb->prepare($query, $formatted_qids));
        } else {
            $questions = [];
        }
    
        echo '<div class="question-table-header">';
        echo '<h3>Questions added in this exam <span id="t-q-count"></span></h3>';
        echo '<a class="remove-questions-button" href="' . admin_url("admin.php?page=all-mock-exams&action=remove_all_questions_from_exam&q_paper_id=" . $q_paper_id) . '" onclick="return confirm(\'Are you sure you want to remove all questions from this exam?\');">Remove All Questions</a>';
        echo '</div>';
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="save_table_order" value="1" />';
        echo '<div class="loader-container" id="loader">';
        echo '<div class="loader"></div></div>';
        echo '<div class="table-wrap-assess">';
        echo '<div class="scroll-table">';
        echo '<table id="courses_table">';
        echo '<thead><tr>';
        echo '<th>Question ID</th>';
        echo '<th>Question</th>';
        echo '<th>Topic Name</th>';
        echo '<th>Added by</th>';
        echo '<th>Actions</th>';
        echo '</tr></thead>';
        echo '<tbody>';
    
        $count = 0;
        if ($questions) {
            foreach ($questions as $question) {
                $topic_ids = maybe_unserialize($question->topic_ids);
                if (!is_array($topic_ids)) {
                    $topic_ids = explode(',', $topic_ids);
                }
    
                $topic_names = [];
                if (!empty($topic_ids)) {
                    $topic_placeholders = implode(',', array_fill(0, count($topic_ids), '%d'));
                    $topic_query = "SELECT topic_name FROM $topics_table WHERE topic_id IN ($topic_placeholders)";
                    $results = $wpdb->get_col($wpdb->prepare($topic_query, $topic_ids));
                    $topic_names = $results ? $results : [];
                }
    
                echo '<tr data-qid="' . esc_attr($question->qid) . '">';
                echo '<td><input type="hidden" name="question_ids[]" value="' . esc_attr($question->qid) . '" />' . esc_html($question->qid) . '</td>';
                echo '<td class="truncate-single-line">' . esc_html($question->question) . '</td>';
                echo '<td>' . esc_html(implode(', ', $topic_names)) . '</td>';
    
                $user_id_to_display = isset($question_user_map[$question->qid]) ? $question_user_map[$question->qid] : 'N/A';
                echo '<td>' . esc_html($this->get_username($user_id_to_display)) . '</td>';
    
                echo '<td>
                <a class="remove-mock-q-btn" href="' . admin_url('admin.php?page=all-mock-exams&action=edit_mock_exam&remove_question_from_mock=1&question_id=' . $question->qid . '&q_paper_id=' . $q_paper_id) . '">Remove Question</a></td>';
                echo '</tr>';
    
                $count++;
            }
        } else {
            echo '<tr><td colspan="5">No questions found in this exam.</td></tr>';
        }
    
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '<input type="submit" class="saveorder-submit-button" id="showTable-js" name="save_table_order" value="Save Table Order" />';
        echo '<a class="paper-add-questions-button" href="'. admin_url( "admin.php?page=all-mock-exams&show=add_mock_questions_from_library&q_paper_id=".$q_paper_id ).' ">Add Questions</a>';
        echo '<a class="paper-add-questions-button" href="'. admin_url( "admin.php?page=all-mock-exams&action=view_mock_exam&q_paper_id=".$q_paper_id ."&paper_name=".$mock_exams_data['question_paper_name'] ).' ">Exam View</a>';
        // echo '<a class="paper-add-questions-button" href="'. admin_url( "admin.php?page=all-mock-exams&action=export_mock_questions&q_paper_id=".$q_paper_id ."&paper_name=".$mock_exams_data['question_paper_name'] ).' ">Export Questions</a>';
        echo '</form>';
        
        echo '<script>
        document.getElementById("showTable-js").addEventListener("click", function() {
            document.getElementById("loader").style.display = "block";
        });
    
        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById("t-q-count").textContent = "( Total: ' . $count . ' )";
        });
    
        document.querySelectorAll("a[href*=\'remove_question\']").forEach(function(link) {
            link.addEventListener("click", function(e) {
                if (!confirm("Are you sure you want to remove this question from the exam?")) {
                    e.preventDefault();
                }
            });
        });
        </script>';
    
       
        

        
    }
    

    public function export_mock_questions($q_paper_id) {
        global $wpdb;
        while (ob_get_level()) {
            ob_end_clean();
        }
    
        nocache_headers(); 
    
        wp_suspend_cache_addition(true);
    
        $exam_table = MDNY_MOCK_QUESTION_PAPER;
        $question_table = MDNY_MOCK_QUESTION;
        $topics_table = MDNY_TOPICS;
    
        $exam_data = $wpdb->get_row($wpdb->prepare("SELECT question_ids FROM {$exam_table} WHERE q_paper_id = %d", $q_paper_id));
    
        if (!$exam_data || empty($exam_data->question_ids)) {
            wp_die("No questions found.");
        }
    
        $unserialized_questions = maybe_unserialize($exam_data->question_ids);
        $question_ids = [];
    
        if (is_array($unserialized_questions)) {
            foreach ($unserialized_questions as $entry) {
                if (isset($entry['question_id'])) {
                    $question_ids[] = $entry['question_id'];
                }
            }
        }
    
        if (empty($question_ids)) {
            wp_die("No valid question IDs found.");
        }
    
        $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));
        $query = $wpdb->prepare("SELECT * FROM {$question_table} WHERE qid IN ($placeholders)", ...$question_ids);
        $questions = $wpdb->get_results($query);
    
        if (empty($questions)) {
            wp_die("No question data found.");
        }
    
        
        while (ob_get_level()) {
            ob_end_clean();
        }
    
        
        header("Content-Type: text/csv; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"mock_questions_export_" . $q_paper_id . ".csv\"");
        header("Pragma: no-cache");
        header("Expires: 0");
    
       
        $output = fopen('php://output', 'w');
    
        
        fputcsv($output, ['question_id', 'unique_id', 'question', 'option_A', 'option_B', 'option_C', 'option_D', 'correct_option', 'explanation', 'difficulty', 'topic_ids', 'selected_paper']);
    
        foreach ($questions as $question) {
            $options = maybe_unserialize($question->options);
            $topic_ids = maybe_unserialize($question->topic_ids);
    
          
            $topic_names = [];
            if (!empty($topic_ids)) {
                foreach ($topic_ids as $topic_id) {
                    $topic_name = $wpdb->get_var($wpdb->prepare("SELECT topic_name FROM {$topics_table} WHERE topic_id = %d", $topic_id));
                    if ($topic_name) {
                        $topic_names[] = $topic_name;
                    }
                }
            }
    
            fputcsv($output, [
                trim($question->qid), 
                trim($question->unique_id),
                str_replace('"', '""', trim($question->question)),
                str_replace('"', '""', trim($options['A'] ?? '')),
                str_replace('"', '""', trim($options['B'] ?? '')),
                str_replace('"', '""', trim($options['C'] ?? '')),
                str_replace('"', '""', trim($options['D'] ?? '')),
                trim($question->correct),
                str_replace('"', '""', trim($question->explanation ?? '')),
                trim($question->difficulty),
                trim(implode(',', $topic_names) ?: ''),
                trim($question->papers ?? '')
            ]);
        }
    
        fclose($output);
    
        wp_suspend_cache_addition(false);
    
        
        die();
    }



    // public function import_mock_questions($q_paper_id, $csv_file, $user_id) {
    //     global $wpdb;
    
    //     // Open the uploaded CSV file
    //     $file_path = $csv_file['tmp_name'];
    //     if (!file_exists($file_path) || !is_readable($file_path)) {
    //         wp_die("Error reading CSV file.");
    //     }
    
    //     $handle = fopen($file_path, "r");
    //     if (!$handle) {
    //         wp_die("Failed to open CSV file.");
    //     }
    
    //     // Skip the first row (headers)
    //     fgetcsv($handle);
    
    //     $exam_table = MDNY_MOCK_QUESTION_PAPER;
        
    
    //     $new_question_ids = [];
    
        
    //     while (($data = fgetcsv($handle)) !== false) {
    //         $question_id = trim($data[0]); 
            
    
    //         if (!empty($question_id)) {
    //             $new_question_ids[] = ['question_id' => (int)$question_id, 'user_id' =>$user_id];
    //         }
    //     }

        
    
    //     fclose($handle);
    
    //     if (empty($new_question_ids)) {
    //         wp_die("No valid question IDs found in CSV.");
    //     }
    
       
    //     $existing_exam_data = $wpdb->get_var($wpdb->prepare("SELECT question_ids FROM {$exam_table} WHERE q_paper_id = %d", $q_paper_id));
    //     $existing_question_ids = maybe_unserialize($existing_exam_data);
        
    
    //     if (!is_array($existing_question_ids)) {
    //         $existing_question_ids = [];
    //     }
    
        
    //     $updated_question_ids = array_merge($existing_question_ids, $new_question_ids);
       
        
    //     $updated = $wpdb->update(
    //         $exam_table,
    //         ['question_ids' => maybe_serialize($updated_question_ids)],
    //         ['q_paper_id' => $q_paper_id]
    //     );
    
    //     if ($updated === false) {
    //         error_log("Failed to update exam question IDs: " . $wpdb->last_error);
    //         wp_die("Failed to update exam question IDs.");
    //     }
    
    //     echo '<div class="updated notice notice-success is-dismissible"><p>Questions updated successfully!</p></div>';

    // }
    
   
    public function import_mock_questions($q_paper_id, $csv_file, $user_id) {
        global $wpdb;
    
        // Open the uploaded CSV file
        $file_path = $csv_file['tmp_name'];
        if (!file_exists($file_path) || !is_readable($file_path)) {
            wp_die("Error reading CSV file.");
        }
    
        $handle = fopen($file_path, "r");
        if (!$handle) {
            wp_die("Failed to open CSV file.");
        }
    
        // Skip the first row (headers)
        fgetcsv($handle);
    
        // Table names
        $exam_table = MDNY_MOCK_QUESTION_PAPER;
        $questions_table = MDNY_MOCK_QUESTION;
    
        $new_question_ids = [];
    
        while (($data = fgetcsv($handle)) !== false) {
            $question = trim($data[2]); 
            $option_A = trim($data[3]);
            $option_B = trim($data[4]);
            $option_C = trim($data[5]);
            $option_D = trim($data[6]);
            $correct_option = trim($data[7]);
            $explanation = trim($data[8]);
            $difficulty = trim($data[9]);
            $topic_ids = trim($data[10]);
            $selected_paper = trim($data[11]);
            if (!empty($question)) {
                // Insert into questions table (auto-generate qid)
                $wpdb->insert(
                    $questions_table,
                    [
                        'question' => $question,
                        'options' => maybe_serialize(['A' => $option_A, 'B' => $option_B, 'C' => $option_C, 'D' => $option_D]),
                        'correct' => $correct_option,
                        'explanation' => $explanation,
                        'difficulty' => $difficulty,
                        'topic_ids' => maybe_serialize(explode(',', $topic_ids)), 
                        'papers' => $selected_paper,
                        'author' => $user_id,
                    ],
                    ['%s', '%s', '%s', '%s', '%s', '%s', '%s','%d']
                );
    
                if ($wpdb->last_error) {
                    error_log("Failed to insert question: " . $wpdb->last_error);
                } else {
                    // Get the auto-generated question ID
                    $generated_qid = $wpdb->insert_id;
    
                    // Add question ID to array for updating the exam table
                    $new_question_ids[] = ['question_id' => $generated_qid, 'user_id' => $user_id];
                }
            }
        }
    
        fclose($handle);
    
        if (empty($new_question_ids)) {
            wp_die("No valid questions found in CSV.");
        }
    
        // Fetch existing question IDs for the exam
        $existing_exam_data = $wpdb->get_var($wpdb->prepare("SELECT question_ids FROM {$exam_table} WHERE q_paper_id = %d", $q_paper_id));
        $existing_question_ids = maybe_unserialize($existing_exam_data);
    
        if (!is_array($existing_question_ids)) {
            $existing_question_ids = [];
        }
    
        // Merge new question IDs with existing ones
        $updated_question_ids = array_merge($existing_question_ids, $new_question_ids);
    
        // Update the exam table with new question IDs
        $updated = $wpdb->update(
            $exam_table,
            ['question_ids' => maybe_serialize($updated_question_ids)],
            ['q_paper_id' => $q_paper_id]
        );
    
        if ($updated === false) {
            error_log("Failed to update exam question IDs: " . $wpdb->last_error);
            wp_die("Failed to update exam question IDs.");
        }
    
        echo '<div class="updated notice notice-success is-dismissible"><p>Questions updated successfully!</p></div>';
    }
    
    

    public function get_username($user_id) {
        // Validate that $user_id is a positive integer
        if (!is_numeric($user_id) || intval($user_id) <= 0) {
            return 'N/A'; // Return default if not a valid user ID
        }
    
        $user_id = intval($user_id); // Convert to integer for safety
    
        $user_data = get_userdata($user_id);
    
        if ($user_data) {
            return $user_data->user_login; // Return the username
        } else {
            return 'N/A'; // Return default if user is not found
        }
    }


    public function view_mock_exam($q_paper_id){
        
        global $wpdb;
        $paper_name = $_GET['paper_name'];
        $EXAM_table_name = MDNY_MOCK_QUESTION_PAPER;
        $QUESTION_table_name = MDNY_MOCK_QUESTION;

        echo "<h1> Exam Model View :- ".strtoupper($paper_name)."</h1>";
        $exam = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $EXAM_table_name WHERE q_paper_id = %d",
                $q_paper_id
            )
        );
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
        
                    if ($questions) {
                        echo '<form id="exam-form" class="exam-form" method="post">';
                        echo '<input type="hidden" name="q_paper_id" value="' . esc_attr($q_paper_id) . '">';
        
                        foreach ($questions as $index => $question) {
                            $filtered_question = stripslashes($question->question);
                            $filtered_question = apply_filters('the_content', $filtered_question);
                            $display_style = $index === 0 ? 'block' : 'none'; // Display only the first question initially
        
                            echo '<div class="mock-question" id="question-' . $index . '" style="display: ' . $display_style . ';">';
                            echo '<div class="mock-question-text">';
                            echo '<p>Q ID: ' . esc_html($question->qid) . ' ( Only Admins can view the Q ID. )</p>';
                            echo '<p><strong>Question ' . ($index + 1) . ': </strong>' . $filtered_question . '</p>';
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
                        }
        
                        echo '</form>';
                        echo '<div id="mock-exam-navigation">';
                        echo '<button class="mock-exam-nav-btn" id="mock-admin-previous-question-btn" disabled>Previous</button>';
                        echo '<button class="mock-exam-nav-btn" id="mock-admin-next-question-btn">Next</button>';
                        echo '</div>';
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
        

        echo '<div class="edit_exam_view_navigation">';
        echo '<a class="button" href="'.admin_url( 'admin.php?page=all-mock-exams&action=edit_mock_exam&q_paper_id='.$q_paper_id).'"> Back to Edit Exam</a>';
        ?>
        <form method="post">
            <input class="mock-pdf_download_admin" type="submit" name="generate_mockpaper_pdf" value="Download PDF">
        </form><?php
        echo '</div>';

        
    }
    
    public function update_mock_exam($q_paper_id, $question_paper_name, $paper_type, $max_hours, $category_name) {
        global $wpdb;
        $table_name = MDNY_MOCK_QUESTION_PAPER; 
        
        $result = $wpdb->update(
            $table_name,
            array(
                'question_paper_name' => sanitize_text_field($question_paper_name),
                'paper_type'          => sanitize_text_field($paper_type),
                'max_hours'           => floatval($max_hours),
                'category'            => sanitize_text_field($category_name),
            ),
            array('q_paper_id' => intval($q_paper_id)), 
            array('%s', '%s', '%f', '%s'), 
            array('%d') 
        );
    
        if ($result === false) {
            
            wp_safe_redirect(admin_url('admin.php?page=all-mock-exams&error=database_error'));
            exit;
        } else {
            
            echo '<script>window.location.href="' . admin_url('admin.php?page=all-mock-exams&action=edit_mock_exam&q_paper_id='. $q_paper_id)  . '";</script>';
            exit;
        }
    }


    public function delete_mock_exam($q_paper_id) {
        global $wpdb;
        $table_name = MDNY_MOCK_QUESTION_PAPER; 
    
        $q_paper_id = intval($q_paper_id);

        $result = $wpdb->delete(
            $table_name,
            array('q_paper_id' => $q_paper_id),
            array('%d')
        );
    
        if ($result !== false) {
            wp_safe_redirect(admin_url('admin.php?page=all-mock-exams&delete=1'));
            exit;
        } else {
            wp_safe_redirect(admin_url('admin.php?page=all-mock-exams&error=delete_failed'));
            exit;
        }
    }


 
    
    
    
    
}



class view_all_mockedit_questions_table extends WP_List_Table {

    function get_exam_data() {
        $data = wp_cache_get('admin_question_data', 'all_questions_data');
    
        // Fetch fresh data if there's no cache or filters/search are applied
        if (false === $data || isset($_REQUEST['s']) || isset($_REQUEST['filter_by_paper']) || isset($_REQUEST['filter_by_topic'])) {
            global $wpdb;
            $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
            $selected_topic = isset($_REQUEST['topic_id']) ? intval($_REQUEST['topic_id']) : '';
    
            $table_name = MDNY_MOCK_QUESTION;
            $topics_table = MDNY_MOCK_TOPICS;
    
           
        // Base SELECT query
        $select = "SELECT q.qid, q.question AS questions, q.author AS author, q.correct AS correct, q.difficulty AS difficulty, q.topic_ids AS topics, q.options AS option 
        FROM $table_name q";

    
            // Default order clause
            $orderby = ' ORDER BY qid DESC';
            $order = '';
    
            // Custom order clause
            if (isset($_REQUEST['orderby']) && !empty($_REQUEST['orderby'])) {    
                if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
                    $order = $_REQUEST['order'];
                }
                $orderby = " ORDER BY " . sanitize_sql_orderby($_REQUEST['orderby'] . ' ' . $order);
            }
    
            // Where clause
            $where = ' WHERE 1=1 ';
    
            // Search filter
            if (!empty($search)) {
                $where .= $wpdb->prepare(" AND q.question LIKE %s", '%' . $wpdb->esc_like($search) . '%');
            }
    
            // Paper filter
            if (!empty($selected_paper) && isset($_REQUEST['filter_by_paper'])) {
                $where .= $wpdb->prepare(" AND q.papers = %d", $selected_paper);
            }
    
           
            $sql = $select . $where . $orderby;
            $questions = $wpdb->get_results($sql);
    
            
            if (!empty($selected_topic) && isset($_REQUEST['filter_by_topic'])) {
                foreach ($questions as $key => $question) {
                    $topic_ids = maybe_unserialize($question->topics);
    
                    if (is_array($topic_ids) && !in_array($selected_topic, $topic_ids)) {
                        unset($questions[$key]);  
                    }
                }
            }
    
      
            foreach ($questions as $key => $question) {
                $topic_ids = maybe_unserialize($question->topics);
    
                if (empty($topic_ids)) {
                    $questions[$key]->topics = "";
                    continue;
                }
    
                // Fetch topic names based on topic IDs
                $topic_names = [];
                if (!empty($topic_ids) && is_array($topic_ids)) {
                    $placeholders = implode(',', array_fill(0, count($topic_ids), '%d'));
                    $query = "SELECT topic_name FROM $topics_table WHERE topic_id IN ($placeholders)";
                    $topics = $wpdb->get_results($wpdb->prepare($query, ...$topic_ids));
    
                    foreach ($topics as $topic) {
                        $topic_names[] = $topic->topic_name;
                    }
    
                    $questions[$key]->topics = implode(',', $topic_names);
                }
            }         
            $data = json_decode(json_encode($questions), true);
            wp_cache_set('admin_question_data', $data, 'all_questions_data');
        }
    
        if (!empty($search)) {
            $searched_data = array_filter($data, function($item) use ($search) {
                return stripos($item['questions'], $search) !== false;
            });
            return $searched_data;
        }

        // _console($data);
   
        return $data;
    }
    

    function get_columns(){
        $columns = array(
        'cb'                => '<input type="checkbox" />',
        'questions'         => 'Questions', 
        'option'            => 'Options',
        'correct'           => 'Correct Answer',       
        'difficulty'        => 'Difficulty',
        'topics'            => 'Topics',
        'author'            => 'Created by'
        );
        return $columns;
    }
    function column_default( $item, $column_name ) {

        global $wpdb;
        switch( $column_name ) { 
            case 'cb':
            return $this->column_cb($item);	
            case 'topics':
                return $this->topic_value($item); 
                case 'option':
                return $this->options_value($item);     
                case 'author' :
                return $this->author_name($item);
            case 'questions':    
            case 'correct':     
            case 'difficulty':

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


    function extra_tablenav($which) {
        global $wpdb;
    
        if ($which !== 'top') {
            return;
        }
    
        $topics_table = MDNY_MOCK_TOPICS;
        $topics = $wpdb->get_results("SELECT topic_id, topic_name FROM $topics_table", ARRAY_A);
    
        if (!empty($topics)) {
            echo '<div class="alignleft actions">';
            echo '<label for="topic_filter" class="screen-reader-text">Filter by Topic</label>';
            echo '<select id="topic_filter" name="topic_id">';
            echo '<option value="">Filter by Topic</option>';
            
            foreach ($topics as $topic) {
                $selected = selected($_REQUEST['topic_id'] ?? '', $topic['topic_id'], false);
                echo '<option value="' . esc_attr($topic['topic_id']) . '" ' . $selected . '>' . esc_html($topic['topic_name']) . '</option>';
            }
    
            echo '</select>';
            submit_button('Filter', '', 'filter_by_topic', false);
            echo '</div>';
        }
    }
    

    function prepare_items() {
        $columns = $this->get_columns();
        $this->process_bulk_action();
        $data = $this->get_exam_data();
        // usort( $data, array( &$this, 'sort_data' ) );
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

    
    public function get_bulk_actions() {
        return [
            'add_to_current_mock_exam' => 'Add Selected Questions to Current Exam',
        ];
    }
        
    public function handle_row_actions($item, $column_name, $primary){

        $edit_link = admin_url( 'admin.php?page=add_new_questions_menu&edit=' .  $item['qid']);
       
        if($primary !== $column_name){
            return '';
        }
    
        $action = [];
        $action['edit'] = '<a href="'.esc_url( $edit_link ).'">'. __('View Question', 'MDNYA').'</a>';
        
    
        return $this->row_actions($action);
    }


    public function process_bulk_action() {
        global $wpdb;
    
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
            $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];
    
            if (!wp_verify_nonce($nonce, $action)) {
                wp_die('Security check failed!');
            }
        }
    
        
        $action = $this->current_action();
    
        if ($action === 'add_to_current_mock_exam') {
            if (isset($_GET['id'])) {
                $selected_qids = array_map('intval', $_GET['id']);
                $current_user_id = get_current_user_id();  
                
                $q_paper_id = isset($_GET['q_paper_id']) ? intval($_GET['q_paper_id']) : 0;
        
                if ($q_paper_id > 0 && !empty($selected_qids)) {
                    $exam_table = MDNY_MOCK_QUESTION_PAPER;
        
                   
                    $existing_exam = $wpdb->get_row(
                        $wpdb->prepare("SELECT question_ids FROM $exam_table WHERE q_paper_id = %d", $q_paper_id),
                        ARRAY_A
                    );
        
                    $existing_qids = [];
                    if (!empty($existing_exam['question_ids'])) {
                        $existing_qids = maybe_unserialize($existing_exam['question_ids']);
                        if (!is_array($existing_qids)) {
                            $existing_qids = [];
                        }
                    }
        
                    // Build a map of existing question_ids to user_ids for easy lookup
                    $qid_map = [];
                    foreach ($existing_qids as $entry) {
                        if (isset($entry['question_id']) && isset($entry['user_id'])) {
                            $qid_map[$entry['question_id']] = $entry['user_id'];
                        }
                    }
        
                    // Add new question IDs without changing existing user_ids
                    foreach ($selected_qids as $qid) {
                        if (!array_key_exists($qid, $qid_map)) {
                            $existing_qids[] = [
                                'question_id' => $qid,
                                'user_id' => $current_user_id
                            ];
                        }
                    }
        
                    // Serialize and update the table
                    $updated_qids_serialized = maybe_serialize($existing_qids);
                    $wpdb->update(
                        $exam_table,
                        ['question_ids' => $updated_qids_serialized],
                        ['q_paper_id' => $q_paper_id],
                        ['%s'],
                        ['%d']
                    );
        
                    if ($wpdb->last_error) {
                        error_log("Database Error: " . $wpdb->last_error);
                    } else {
                        // error_log("Question IDs updated successfully for Exam ID: $q_paper_id");
                        // error_log("Updated question_ids: " . print_r($existing_qids, true));
                    }
        
                    // Redirect after update
                    echo '<script>window.location.href="' . admin_url('admin.php?page=all-mock-exams&action=edit_mock_exam&q_paper_id='. $q_paper_id . '&message=questions_added')  . '";</script>';
                
                    exit;
                }
            }
        }
        
        
    }
    
    public function column_cb($item) {
        global $wpdb;
        $q_paper_id = isset($_GET['q_paper_id']) ? intval($_GET['q_paper_id']) : 0;
        $is_disabled = '';
    
        if ($q_paper_id > 0) {
            $exam_table = MDNY_MOCK_QUESTION_PAPER;
            
            // Fetch the current question_ids for the exam
            $existing_exam = $wpdb->get_row(
                $wpdb->prepare("SELECT question_ids FROM $exam_table WHERE q_paper_id = %d", $q_paper_id),
                ARRAY_A
            );
    
            if (!empty($existing_exam['question_ids'])) {
                $existing_qids = maybe_unserialize($existing_exam['question_ids']);
                if (!is_array($existing_qids)) {
                    $existing_qids = [];
                }
    
                // Build a list of question_ids
                $qid_list = array_map(function($entry) {
                    return $entry['question_id'];
                }, $existing_qids);
    
                // Check if the current question_id is in the list
                if (in_array($item['qid'], $qid_list)) {
                    $is_disabled = 'disabled';
                }
            }
        }
    
        // Return the checkbox, disabling it if the question is already in the exam
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" %s />',
            esc_attr($item['qid']),
            $is_disabled
        );
    }
    
    


    function topic_value($item) {
        $topic_ids = maybe_unserialize($item['topics']);
        return $topic_ids; 
    }
    



    public function author_name($item){
        $author = $item['author'];

        $user = get_user_by('id', $author);
        if ($user) {
            return $user->first_name ;
       
        }else{
            return "User ID not found";
        }
    }

    function options_value($item){
// _console($item['option']);
        global $wpdb;

        if($item['option']){
            $options = esc_html(implode(', ', maybe_unserialize($item['option'])));
        }else{
            $options = "";
        }
        
        return $options;

    }
      
}
