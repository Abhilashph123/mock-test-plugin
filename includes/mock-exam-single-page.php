<?php

get_header( );
global $wpdb;

$exam_slug = get_query_var('exam_slug');
_console($exam_slug);
$exam_name = ucwords(str_replace('-', ' ', $exam_slug)); 

$table_name = MDNY_MOCK_QUESTION_PAPER;

$sql = $wpdb->get_results("select * from $table_name");
// Get all categories for this exam name
$categories = $wpdb->get_results($wpdb->prepare(
    "SELECT DISTINCT category FROM $table_name WHERE question_paper_name = %s AND status = 'Ordinary'",
    $exam_name
));


$selected_category = '';
$exam_data = null;
$question_count = 0;

// Handle form submission
if (!empty($_GET['category'])) {
    $selected_category = sanitize_text_field($_GET['category']);

    $exam_data = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$table_name} WHERE question_paper_name = %s AND category = %s", $exam_name, $selected_category)
    );

    if (!empty($exam_data)) {
        $question_data = maybe_unserialize($exam_data->question_ids);
        $question_count = !empty($question_data) ? count($question_data) : 0;
    }
}
?>

<div class="mock-exam-page">
    <center><p style="font-size: 30px;  margin-bottom: 18px; font-weight: 600 !important;">Mock Test: <?php echo esc_html($exam_name); ?></p></center>

    <form method="get" class="exam-category-list">
        <input type="hidden" name="exam_slug" value="<?php echo esc_attr($exam_slug); ?>">
        <label for="category">Select Category:</label>
        <select id="category" name="category" onchange="this.form.submit()">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo esc_attr($cat->category); ?>" 
                    <?php echo ($selected_category === $cat->category) ? 'selected' : ''; ?>>
                    <?php echo esc_html($cat->category);  ?>
                   
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (!empty($exam_data)): ?>
        <div class="exam-card">
            <h3 style="text-align: left; margin: 10px;"><?php echo esc_html($exam_data->question_paper_name); ?></h3>
            <p class="mock-card-details-text">Category: <?php echo esc_html($exam_data->category); ?></p>
            <p class="mock-card-details-text">Max Hours: <?php echo esc_html($exam_data->max_hours); ?></p>
            <p class="mock-card-details-text">Total Questions: <?php echo esc_html($question_count); ?></p>

            <form style="display:flex;" method="post" action="<?php echo esc_url(site_url('mock-exam-attend-page')) ?>">
                <input type="hidden" name="q_paper_id" value="<?php echo esc_attr($exam_data->q_paper_id); ?>">
                <input type="hidden" name="category" value="<?php echo esc_attr($exam_data->category); ?>">
                <input type="hidden" name="mock_exam_form" value="1">
                <input type="submit" class="attend-exam-btn" name="submit_exam" value="Attend Exam" 
                    <?php echo $question_count == 0 ? 'disabled' : ''; ?>>
            </form>
        </div>
    <?php elseif (!empty($selected_category)): ?>
        <p><center>No exam found for the selected category.</center></p>
    <?php endif; ?>
</div>
<?php

get_footer();