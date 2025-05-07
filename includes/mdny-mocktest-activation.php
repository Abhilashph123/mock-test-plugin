<?php

class MockPluginActivation {
	public static function activate() {
		ob_start();
		self::create_mdny_mock_question_table();
		self::create_mdny_mock_topics_table();
		self::create_question_paper_table();
		self::create_mdny_mock_results_table();
		self::create_mdny_mock_papers_table();
		self::mdny_assessment_tables_exists();
		self::mdny_create_confirmation_page();
		self::add_col_results();
		ob_end_clean(); 
	}
	
	private static function mdny_assessment_tables_exists(){
		global $wpdb;
		$flag = true;

		$table_names = [MDNY_MOCK_RESULTS, MDNY_MOCK_PAPERS, MDNY_MOCK_TOPICS, MDNY_MOCK_QUESTION_PAPER, MDNY_MOCK_QUESTION];

		foreach ($table_names as $table_name) {
			// _console($table_name);
			$table_exists = $wpdb->get_var($wpdb->prepare(
				"SHOW TABLES LIKE %s", 
				$table_name
			));

			if (!$table_exists) {
				$flag = false;
				break;


			} 
		}
		if($flag!=false){
			update_option('mdny_tables_created',true);
		}

	}

	private static function create_mdny_mock_question_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mdny_mock_questions';  
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            qid int(9) NOT NULL AUTO_INCREMENT,
            question_title varchar(255) NOT NULL,
            question longtext NOT NULL,
            options text NOT NULL,
            correct varchar(10) NOT NULL,
            type varchar(255) NOT NULL,
            author int(9),
            topic_ids varchar(255),
            difficulty varchar(255) NOT NULL,
			papers varchar(255),
			explanation longtext,
            last_modified timestamp DEFAULT NOW() ON UPDATE NOW(),
            PRIMARY KEY  (qid)       
        ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}



	private static function create_question_paper_table() {
        global $wpdb;

        
        $table_name = $wpdb->prefix . 'mdny_mock_question_paper';  
        
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // SQL query to create the table
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            q_paper_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            question_paper_name VARCHAR(255) NOT NULL,
            question_ids LONGTEXT NOT NULL,
            max_hours FLOAT(11) NOT NULL,
            author BIGINT(20) UNSIGNED NOT NULL,
            paper_type VARCHAR(255) NOT NULL,
            category VARCHAR(255) NOT NULL,
			status VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (q_paper_id)
        ) $charset_collate;";

       
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        
        dbDelta($sql);
    }


	private static function create_mdny_mock_topics_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mdny_mock_topics'; 
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
            topic_id int(9) NOT NULL AUTO_INCREMENT,
            topic_name varchar(255) NOT NULL,
            author int(9) NOT NULL,
            topic_parent int(9),
            date_created timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
            last_modified timestamp DEFAULT NOW() ON UPDATE NOW(),
            description text,
            PRIMARY KEY (topic_id)
        ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

	}


	private static function create_mdny_mock_papers_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mdny_mock_papers'; 
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
            paper_id int(9) NOT NULL AUTO_INCREMENT,
            paper_name varchar(255) NOT NULL,
            date_created timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
            last_modified timestamp DEFAULT NOW() ON UPDATE NOW(),
            description text,
            PRIMARY KEY (paper_id)
        ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

	}



	private static function create_mdny_mock_results_table() {
        global $wpdb;
       
        $table_name = $wpdb->prefix . 'mdny_mock_results';
        
        // Charset and collation for the table
        $charset_collate = $wpdb->get_charset_collate();
        
        // SQL query to create the table
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            result_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            questions LONGTEXT NOT NULL,
            q_paper_id INT(11) NOT NULL,
            percentage VARCHAR(255) NOT NULL,
            category VARCHAR(255) NOT NULL,
            user_email VARCHAR(255) NOT NULL,
            phone_number VARCHAR(20) NOT NULL,
			topics VARCHAR(65535),
            PRIMARY KEY (result_id)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        
        dbDelta($sql);
    }

	private static function mdny_create_confirmation_page() {
		// Check if the page already exists
		$page = get_page_by_path('mock-submission-confirmation');
	
		if (!$page) {
			// Create the page
			$page_data = [
				'post_title'     => 'Mock Submission Confirmation',
				'post_name'      => 'mock-submission-confirmation',
				'post_content'   => '[mdny_mock_submission_popup]', 
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => 1
			];
	
			wp_insert_post($page_data);
		}
	}


	private static function add_col_results() {
		global $wpdb;
		$table_name = MDNY_MOCK_QUESTION_PAPER;
	
		// Add 'status' column if it doesn't exist
		$status_column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'status'");
		if (empty($status_column_exists)) {
			$sql = "ALTER TABLE $table_name ADD COLUMN status VARCHAR(255) DEFAULT NULL;";
			$wpdb->query($sql);
		}
	}
	
	
	
	
	


}

register_activation_hook(MDNY_MOCKTEST_FILE, 'MockPluginActivation::activate');
