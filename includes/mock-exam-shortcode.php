<?php
function display_mock_question_papers() {
    global $wpdb;
    $table_name = MDNY_MOCK_QUESTION_PAPER;

    // Fetch distinct question papers
    $question_papers = $wpdb->get_results("SELECT DISTINCT question_paper_name FROM $table_name WHERE status = 'Ordinary' ORDER BY question_paper_name ASC");

    // Initialize selected values
    $selected_paper = '';
    $selected_category = '';
    $exam_data = null;
    $categories = [];

    // If exam is selected, fetch categories related to that exam
    if (!empty($_POST['question_paper'])) {
        $selected_paper = sanitize_text_field($_POST['question_paper']);
        $categories = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT category FROM {$table_name} WHERE question_paper_name = %s", $selected_paper));
    }

    // Handle form submission
    if (!empty($_POST['question_paper']) && !empty($_POST['category'])) {
        $selected_category = sanitize_text_field($_POST['category']);

        // Fetch exam data based on selected exam and category
        $exam_data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE question_paper_name = %s AND category = %s", $selected_paper, $selected_category)
        );

        if (!empty($exam_data)) {
            $question_data = maybe_unserialize($exam_data->question_ids);
            $question_count = !empty($question_data) ? count($question_data) : 0;
        }
    }

    ob_start(); 
    ?>
    <div class="mock-exam-page">
        <form method="POST">
            <label for="question_paper">Available Exams:</label>
            <select id="question_paper" name="question_paper" onchange="this.form.submit()">
                <option value="">-- Select Exam --</option>
                <?php foreach ($question_papers as $paper): ?>
                    <option value="<?php echo esc_attr($paper->question_paper_name); ?>" 
                        <?php echo ($selected_paper === $paper->question_paper_name) ? 'selected' : ''; ?>>
                        <?php echo esc_html($paper->question_paper_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="category">Select Category:</label>
            <select id="category" name="category" onchange="this.form.submit()" <?php echo empty($categories) ? 'disabled' : ''; ?>>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo esc_attr($cat->category); ?>" 
                        <?php echo ($selected_category === $cat->category) ? 'selected' : ''; ?>>
                        <?php echo esc_html($cat->category); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if (!empty($exam_data)): ?>
            <div class="exam-card">
                <h3><?php echo esc_html($exam_data->question_paper_name); ?></h3>
                <p class="mock-card-details-text">Category: <?php echo esc_html($exam_data->category); ?></p>
                <p class="mock-card-details-text">Max Hours: <?php echo esc_html($exam_data->max_hours); ?></p>
                <p class="mock-card-details-text">Total Questions: <?php echo esc_html($question_count ?? "0"); ?></p>
                
                <form method="post" action="<?php echo esc_url(site_url('mock-exam-attend-page')) ?>">
                    <input type="hidden" name="q_paper_id" value="<?php echo esc_attr($exam_data->q_paper_id); ?>">
                    <input type="hidden" name="category" value="<?php echo esc_attr($exam_data->category); ?>">
                    <input type="hidden" name="mock_exam_form" value="1"> 
                    <?php 
                    if($question_count == 0){
                        $disabled = 'disabled';
                    }else{
                        $disabled = '';
                    }
                    ?>
                    <input type="submit" name="submit_exam" class="attend-exam-btn" value="Attend Exam" <?php echo $disabled ?>>
                </form>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($selected_paper)): ?>
            <center>Please select a category to proceed.</center>
        <?php endif; ?>
    </div>  
    <?php
    return ob_get_clean();
}

add_shortcode('mock_exam_dropdown', 'display_mock_question_papers');



