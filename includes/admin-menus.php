<?php
class MIDNAY_Admin_Menu {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu() {
        add_menu_page(
            'Mock Test',           
            'Mock Test',           
            'manage_options',             
            'midnay-mock-test',           
            [$this, 'dashboard_callback'],
            'dashicons-welcome-learn-more', 
            5                             
        );
       
    }



public function dashboard_callback() {
    global $wpdb;

    // List of table names to check
    // $table_names = [
    //     MDNY_MOCK_RESULTS,
    //     MDNY_MOCK_PAPERS,
    //     MDNY_MOCK_TOPICS,
    //     MDNY_MOCK_QUESTION_PAPER,
    //     MDNY_MOCK_QUESTION
    // ];


    $exams =  MDNY_MOCK_QUESTION_PAPER;  
    $query = $wpdb->get_results("select * from $exams");
   
    $results = Get_all_mock_results_data();
    $questions = Get_all_mock_questions_data();

    ?>
    <div class="wrap mock-dash">
        <h1 class="dashboard-title" style="color: #fff;font-size: 30px;">Mock Exam Dashboard</h1>

        <div class="dash-wrap">
            <div class="col card">
                <span class="card-title">Total Exams</span>
                <span class="card-value"><?php echo count($query); ?></span>
                <a href="<?php echo admin_url('admin.php?page=create-mock-question-paper'); ?>" class="card-link">
                    Create New Exam <span class="icon">+</span>
                </a>
            </div>

            <div class="col card">
                <span class="card-title">Total Attendees</span>
                <span class="card-value"><?php echo count($results); ?></span>
                <a href="<?php echo admin_url('admin.php?page=mock-test-results'); ?>" class="card-link">
                    Show Results <span style="font-size: 1.65rem; margin-left: 4px;" class="icon">&rarr;</span>
                </a>
            </div>

            <div class="col card">
                <span class="card-title">Total Questions</span>
                <span class="card-value"><?php echo count($questions); ?></span>
                <a href="<?php echo admin_url('admin.php?page=add-mock-questions'); ?>" class="card-link">
                    Create New Question <span class="icon">+</span>
                </a>
            </div>
        </div>
    </div>

<?php
    }
   
}

new MIDNAY_Admin_Menu();

