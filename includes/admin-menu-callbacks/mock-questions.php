<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
add_action('admin_menu',function(){

    add_submenu_page(
        'midnay-mock-test',
        'View All Questions',
        'View All Questions',
        'manage_options',
        'view-mock-questions',
        'view_mock_questions_callback',
       3
    );

});


function view_mock_questions_callback(){
    $qdata = Get_all_mock_questions_data();
   
	$view_all_mock_questions_table = new view_all_mock_questions_table();
    $search = isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : ''; ?>

	<div class="wrap">
	<h1 class="wp-heading-inline"> All Questions</h1>
	<a href="<?php echo admin_url('admin.php?page=add-mock-questions')?> " class="page-title-action">Add New Question</a>
    <form method="GET">
    <input type="hidden" name="page" value="view-mock-questions"/>
    <?php
    $view_all_mock_questions_table->prepare_items();
    $view_all_mock_questions_table->search_box('Search', 'search_id');
    $view_all_mock_questions_table->display();
    ?>
    </form>
	</div>		
<?php
}






class view_all_mock_questions_table extends WP_List_Table {

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
    public function process_bulk_action() {

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );

        }

        $action = $this->current_action();

        switch ( $action ) {

            case 'trash':
                /*global $wpdb;
                $table_name = si_alert_table_name();				
                foreach($_GET['id'] as $id) {
                $wpdb->update($table_name,
                        array('status'=> 'trash'),
                        array('alert_id' => $id)
                        );
                }
    */
                wp_redirect( admin_url( 'admin.php?page=view-mock-questions&success=Trashed succesfully'));
                exit;
                break;

            default:
                // do nothing or something else
                return;
                break;
        }

        return; 
    }	

    public function column_title(  $item ) {
        

            $edit_link = admin_url( 'admin.php?page=view-mock-questions&edit=' .  $item['qid']);
            $delete_link =  admin_url('admin.php?page=view-mock-questions&delete=' . $item['qid']);
        
            $output    = '';
            // Title.
            $output .= '<strong><a href="' . esc_url( $edit_link ) . '" class="row-title">' . esc_html( $item['title']   ) . '</a></strong>';

            //Get actions.
            $actions = array(
                'edit'   => '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit', 'MDNYA' ) . '</a>',
                'delete'   => '<a href="' . esc_url( $delete_link ) . '">' . esc_html__( 'Delete', 'MDNYA' ) . '</a>',
            );
        
        // 	if($item['status']=='Trash'){
        // 		$actions = array(
        //             'restore'   => '<a href="' . esc_url( $restore_link ) . '">' . esc_html__( 'Restore', 'MDNYA' ) . '</a>',
        // 			'delete'   => '<a href="' . esc_url( $delete_link ) . '">' . esc_html__( 'Delete Permanently', 'MDNYA' ) . '</a>',
        //         );
        // 	}
        
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

    public function get_bulk_actions(){
        return array(
            'question_delete'  => __('Delete', 'MDNYA')
        );
    }
        
    public function handle_row_actions($item, $column_name, $primary){

        $edit_link = admin_url( 'admin.php?page=add-mock-questions&edit=' .  $item['qid']);
        $delete_link =  admin_url('admin.php?page=add-mock-questions&delete=' . $item['qid']);
        // $add_topics_link =  admin_url('admin.php?page=add-mock-questions&question_id=' . $item['qid']);
        if($primary !== $column_name){
            return '';
        }

        $action = [];
        $action['edit'] = '<a href="'.esc_url( $edit_link ).'">'. __('Edit', 'MDNYA').'</a>';
        $action['delete'] = '<a href="'.esc_url( $delete_link ).'">'. __('Delete', 'MDNYA').'</a>';
        // $action['question_id'] = '<a href="'.esc_url( $add_topics_link ).'">'. __('Add Topics', 'MDNYA').'</a>';

        return $this->row_actions($action);
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['qid']
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
            
            return $user->data->display_name ;
       
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
