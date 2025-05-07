<?php

class createMockpaper{
   
    public function display_add_mock_form(){
    ?>
    <div class="wrap">
        <h1>Add New Mock Paper</h1>
        <div class="main-wrap-mock">
            <form method="post">
                <div class="paper-name common-cont-wrap">
                    <label> Add Paper Name</label>
                    <input type="text" name="paper_name" required>
                </div> <br>
                <div class="max-hours common-cont-wrap">
                    <label> Add Max Hours For Exam</label>
                    <input type="text" placeholder="eg:- 1 or 2" name="max_hours" required>
                </div> <br>
                <div class="paper-type common-cont-wrap">
                    <label>Paper Type</label>
                    <select id="mock-paper-type" name="paper_type" required>
                    <option value="Easy">Easy</option>
                    <option value="Medium">Medium</option>
                    <option value="Hard">Hard</option>
                    </select>
                </div> <br>
                <div class="exam-type common-cont-wrap">
                    <label>Exam Type</label>
                    <select id="exam-type" name="status" required>
                        <option value="Ordinary">Ordinary Exam</option>
                        <option value="Premium">Premium Exam</option>
                    </select>
                </div>
                <br>


                <div class="category-name common-cont-wrap">
                    <label>Category Name</label>
                    <input type="text" name="category_name" required>
                </div> <br>
                <div class="mock-paper-submit common-cont-wrap">
                    <input type="submit" name="mock_paper_save" value="Create Paper" class="paper-submit-button">
                </div>
            </form>
        </div>
    </div>
    <?php
    }

    public function submit_add_mock_form() {
        global $wpdb;
        $table_name = MDNY_MOCK_QUESTION_PAPER;

        $paper_name = sanitize_text_field($_POST['paper_name']);
        $status = sanitize_text_field($_POST['status']);
        $max_hours = floatval($_POST['max_hours']); 
        $paper_type = sanitize_text_field($_POST['paper_type']);
        $category_name = sanitize_text_field($_POST['category_name']);
        $author = get_current_user_id(  );
            
        $result = $wpdb->insert(
            $table_name,
            array(
                'question_paper_name' => $paper_name,
                'max_hours'           => $max_hours,
                'paper_type'          => $paper_type,
                'category'            => $category_name,
                'author'              => $author,
                'status'              => $status
            ),
            array('%s', '%f', '%s', '%s', '%d', '%s')
        );

        
        if ($result) {
            $inserted_id = $wpdb->insert_id;
        
           
        do_action('mdny_new_exam_created', $inserted_id);
        
        echo '<div class="notice notice-success"><p>Mock paper created successfully! ID: <a href="' . admin_url('admin.php?page=all-mock-exams&action=edit_mock_exam&q_paper_id=' . intval($inserted_id)) . '">Show Created Exam</a></p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Error saving mock paper. Please try again.</p></div>';
        }
}

}
