<?php

if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

add_action('admin_menu',function(){
	add_submenu_page(
		'midnay-mock-test',
		'Mock Topics',
		'Mock Topics',
		'manage_options',
		'mock-topics',
		'mdny_mock_topics_menu_callback',
        5,
		
	);

});


class MDNY_MOCK_TOPICS_DISPLAY extends WP_List_Table {
	function get_topics_data(){
		global $wpdb;
		$table_name = MDNY_MOCK_TOPICS;
		$search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
		$select= "SELECT topic_id, topic_name as title, topic_parent as parent, description";

		$orderby=' ORDER BY topic_id DESC';
		$order='';

		$where ="";


		if (!empty($search)) {
            $where = $wpdb->prepare(" WHERE topic_name LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

		if( isset( $_REQUEST['orderby'] ) && !empty($_REQUEST['orderby'])){	

			if( isset( $_REQUEST['order'] ) && !empty($_REQUEST['order']))
				$order=$_REQUEST['order'];

			$orderby= " ORDER BY ".$_REQUEST['orderby']." ".$order;
		}
		$sql =  $select." FROM ".$table_name.$where.$orderby; 
		$data = $wpdb->get_results($sql,ARRAY_A);
		$MDNY_MOCK_TOPIC_HANDLE = new MDNY_MOCK_TOPIC_HANDLE();
		$hierarchy=$MDNY_MOCK_TOPIC_HANDLE->buildHierarchy($data);
// 		_console($hie);
		
		return $hierarchy;

	}

	function get_columns(){
		$columns = array(
			'cb'                => '<input type="checkbox" />',
// 			'topic_id'          => "ID",
			'title'             => 'Title', 
			'description'       => 'Descripion',
			'parent'            => 'Parent'
		);
		return $columns;
	}
	function column_default( $item, $column_name ) {
		global $wpdb;
		switch( $column_name ) { 
			case 'cb':
				return $this->column_cb($item);	   
// 			case 'topic_id':
			case 'title':
			case 'parent':
			case 'description':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}
	public function column_title(  $item ) {	
		$edit_link = admin_url( 'admin.php?page=mock-topics&action=edit&topic_id=' .  $item['topic_id']  );
		$delete_link = admin_url( 'admin.php?page=mock-topics&action=delete&topic_id=' .  $item['topic_id']  );

		$output    = '';
		// Title.
		$output .= '<strong><a href="' . esc_url( $edit_link ) . '" class="row-title">' . esc_html( $item['title']   ) . '</a></strong>';
		// Get actions.
		$actions = array(
			'edit'   => '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit', 'MDNYA' ) . '</a>',
			'delete'   => '<a href="' . esc_url( $delete_link ) . '">' . esc_html__( 'Delete', 'MDNYA' ) . '</a>',

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
		$this->process_bulk_action();
		$data = $this->get_topics_data();
// 		usort( $data, array( &$this, 'sort_data' ) );
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
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			$item['topic_id']
		);
		// _console($item);
	}
}


function  mdny_mock_topics_menu_callback(){

	global $wpdb;
	$table_name = MDNY_MOCK_TOPICS;
	$MDNY_MOCK_TOPIC_HANDLE = new MDNY_MOCK_TOPIC_HANDLE();
	if(isset($_POST['create-topic'])){
		$MDNY_MOCK_TOPIC_HANDLE->create_new_topic_submit($_POST);		    
	} 
	if (isset($_GET['action'])) {
		switch ($_GET['action']) {
			case 'edit':
				$MDNY_MOCK_TOPIC_HANDLE->edit_topic_form();
				break;
			case 'delete':
				$MDNY_MOCK_TOPIC_HANDLE->delete_topic();
				break;
			default:
				$MDNY_MOCK_TOPIC_HANDLE->create_new_topic_form();
				break;	
		}
	}
	else{
		$MDNY_MOCK_TOPIC_HANDLE->create_new_topic_form(); 
		
	}

}

class MDNY_MOCK_TOPIC_HANDLE{
	public function create_new_topic_form(){
		global $wpdb;
		$table_name = MDNY_MOCK_TOPICS;
		$topics = $wpdb->get_results("SELECT topic_id, topic_name FROM $table_name");
		$desc = $wpdb->get_results("DESCRIBE $table_name");

?>
<div class="wrap">
	<h1 class="wp-heading-inline">Mock Topics</h1>
	<div id="col-container">
		<div id="col-left" style="margin-top: 1em;">
			<div class="col-wrap">
				<h2>Add New Topic</h2>

				<div class="form-wrap">
					<form action="" method="post" class="topic-form">
						<div class="topic-input-wrapper form-field">
							<label for="topic-name">Topic Name</label>
							<input type="text" name="topic_name" required>
						</div>
						<div class="topic-input-wrapper form-field">
							<label for="topic-parent">Topic Parent</label>
							<select name="topic_parent" >
								<option value="0">none</option>
								<?php
                                    foreach($topics as $topic){
                                        echo '<option value="'. esc_attr($topic->topic_id).'">'. esc_attr($topic->topic_name).'</option>';
                                    }
								?>
							</select>
						</div>
						<div class="topic-input-wrapper form-field">
							<label for="description">Description</label>
							<textarea name="description" rows="4" cols="50"></textarea>
						</div>
						<input type="submit" class="button button-primary" name="create-topic" value="Add New Topic">
					</form>
				</div>
			</div>
		</div>
		<div id="col-right">
			<form method="GET">
				<?php
		$MDNY_MOCK_TOPICS_DISPLAY = new MDNY_MOCK_TOPICS_DISPLAY();
		echo '<form method="GET">';
     	echo '<input type="hidden" name="page" value="mock-topics"/>';
		$MDNY_MOCK_TOPICS_DISPLAY->prepare_items();
		$MDNY_MOCK_TOPICS_DISPLAY->search_box('Search', 'search_id');
		$MDNY_MOCK_TOPICS_DISPLAY->display();
		echo '</form>';
				?>
			</form>
		</div>
	</div>
</div>


<?php
	}

	public function create_new_topic_submit($data){
		global $wpdb;
		$table_name = MDNY_MOCK_TOPICS;
		$topic_name = sanitize_text_field($data['topic_name']);
		$topic_parent = sanitize_text_field($data['topic_parent']);
		$description = sanitize_textarea_field($data['description']);
		$wpdb->insert(
			$table_name,
			array(
				'topic_name' => $topic_name,
				'topic_parent' => intval($topic_parent),
				'description' => $description,
				'author'=>get_current_user_id(),
			),
			array('%s', '%d', '%s','%d')
		);		   
		if($wpdb->last_error) {
			wp_die('Database error: ' . $wpdb->last_error);
		} else {
			echo '<div class="notice notice-success is-dismissible"><p>Topic added successfully!</p></div>';
			$_POST='';
		}
	}

	public function edit_topic_submit($data){

		global $wpdb;
		$table_name = MDNY_MOCK_TOPICS;
		$topic_id = $_GET['topic_id'];
		$topic_name = sanitize_text_field($data['topic_name']);
		$topic_parent = $data['topic_parent'];
		$description = sanitize_textarea_field($data['description']);

		$wpdb->update(
			$table_name,
			array(
				'topic_name' => $topic_name,
				'topic_parent' => $topic_parent,
				'description' => $description
			),
			array('topic_id' => $topic_id)
		);	   
		if($wpdb->last_error) {
			wp_die('Database error: ' . $wpdb->last_error);
		} else {
			echo '<div class="notice notice-success is-dismissible"><p>Topic updated!</p></div>';
			$_POST='';
		}
	}

	public function edit_topic_form(){
    ?>
            <div class="wrap">
                <?php
                    if(isset($_POST['edit-topic'])){
                        $this->edit_topic_submit($_POST);
                    }
                    global $wpdb;
                    $table_name = MDNY_MOCK_TOPICS;
                    $topics = $wpdb->get_results("SELECT topic_id, topic_name FROM $table_name");
                    $edit_data= $wpdb->get_row("SELECT * FROM $table_name WHERE topic_id={$_GET['topic_id']}");
                ?>
                <h1 class="wp-heading-inline">Topics</h1><a href="<?php echo admin_url('admin.php?page=mock-topics'); ?>" class="page-title-action">Create New Topic</a>
                <div id="col-container">
                    <div id="col-left" style="margin-top: 1em;">
                        <div class="col-wrap">
                            <h2>Edit Topic #<?php echo $_GET['topic_id'];?></h2>

                            <div class="form-wrap">
                                <form action="" method="post" class="topic-form">
                                    <div class="topic-input-wrapper form-field">
                                        <label for="topic-name">Topic Name</label>
                                        <input type="text" value="<?php echo $edit_data->topic_name; ?>" name="topic_name" required>
                                    </div>
                                    <div class="topic-input-wrapper form-field">
                                        <label for="topic-parent">Topic Parent</label>
                                        <select name="topic_parent" >
                                            <option value="0">none</option>
                                            <?php
                                            foreach($topics as $topic){

                                                $selected='';
                                                if($topic->topic_id == $_GET['topic_id']){
                                                    continue;
                                                }
                                                if($topic->topic_id == $edit_data->topic_parent){
                                                    $selected=' selected';
                                                }
                                                echo '<option value="'. esc_attr($topic->topic_id).'"'.$selected.'>'. esc_attr($topic->topic_name).'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="topic-input-wrapper form-field">
                                        <label for="description">Description</label>
                                        <textarea name="description" rows="4" cols="50"><?php echo $edit_data->description; ?></textarea>
                                    </div>
                                    <input type="submit" class="button button-primary" name="edit-topic" value="Update Topic">
                                    <a class="button back-to-topics-btn" href="<?php echo admin_url( 'admin.php?page=mock-topics')?>">Back to Topics</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <?php
	}
	public function buildHierarchy($array, $parent_id = 0, $level = 0) {
    $result = array();

    // Filter elements by parent_id
    $filtered = array_filter($array, function($element) use ($parent_id) {
        return $element['parent'] == $parent_id;
    });

    // Sort filtered elements by name
    usort($filtered, function($a, $b) {
        return strcmp($a['title'], $b['title']);
    });

    // Add sorted elements to result
    foreach ($filtered as $element) {
        $element['title'] = str_repeat('— ', $level). $element['title'];
        $result[] = $element;
        // Recursively call reorder_array to find children
        $children = $this->buildHierarchy($array, $element['topic_id'], $level + 1);
        if ($children) {
            foreach ($children as $child) {
                $result[] = $child;
            }
        }
    }
    return $result;
}



	public function delete_topic(){
		global $wpdb;
		$table_name = MDNY_MOCK_TOPICS;

		if (isset($_GET['topic_id'])) {
			$topic_id = intval($_GET['topic_id']);
			$wpdb->delete($table_name, array('topic_id' => $topic_id), array('%d'));

			if ($wpdb->last_error) {
				wp_die('Database error: ' . $wpdb->last_error);
			} else {

				?>
				<script>
					var newURL = location.href.split("&")[0];
					window.history.pushState('object', document.title, newURL);
				</script>
				<?php
				echo '<div class="notice notice-success"><p>Topic deleted successfully!</p></div>';
				$this->create_new_topic_form();
			}
		}
	}
}
