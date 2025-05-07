<?php

if (!is_user_logged_in()) {
    echo "<p>You must be logged in to view your purchased exams.</p>";
    return;
}

global $wpdb;
$table_name = MDNY_MOCK_QUESTION_PAPER;
$mock_results_table = MDNY_MOCK_RESULTS;
$user_id = get_current_user_id();
$user_email = wp_get_current_user()->user_email;

$orders = wc_get_orders([
    'customer_id' => $user_id,
    'status' => ['completed'], 
    'limit' => -1
]);

if (empty($orders)) {
    echo "<p>No purchased exams found.</p>";
    return;
}

$exam_details = [];

foreach ($orders as $order) {
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $linked_exam_ids = get_post_meta($product_id, '_linked_exam_ids', true);
        $linked_exam_ids = !empty($linked_exam_ids) ? explode(',', $linked_exam_ids) : [];

        if (!empty($linked_exam_ids)) {
            $placeholders = implode(',', array_fill(0, count($linked_exam_ids), '%d'));
            $query = $wpdb->prepare(
                "SELECT q_paper_id, question_paper_name, max_hours, question_ids, category, status 
                 FROM $table_name WHERE q_paper_id IN ($placeholders)", 
                ...$linked_exam_ids
            );
            $exams = $wpdb->get_results($query);

            foreach ($exams as $exam) {
                $question_ids = maybe_unserialize($exam->question_ids);
                $question_count = is_array($question_ids) ? count($question_ids) : 0;

                
                $result_query = $wpdb->prepare(
                    "SELECT percentage, result_id FROM $mock_results_table WHERE q_paper_id = %d AND user_email = %s",
                    $exam->q_paper_id,
                    $user_email
                );
                $exam_result = $wpdb->get_row($result_query);
                
                $result_id = $exam_result->result_id;
                _console($result_id);
                $has_attempted = ($exam_result !== null && $exam_result->result_id); 

                $exam_details[] = [
                    'result_id'      => $result_id,
                    'paper_id'       => $exam->q_paper_id,
                    'product_name'   => get_the_title($product_id),
                    'name'           => $exam->question_paper_name,
                    'max_hours'      => $exam->max_hours,
                    'question_count' => $question_count,
                    'status'         => $exam->status,
                    'category'       => $exam->category,
                    'has_attempted'  => $has_attempted
                ];
            }
        }
    }
}


get_header();
?>
<div class="exam-product-container">
    <h2>Your Purchased Exams</h2>

    <?php if (!empty($exam_details)) : ?>
        <div class="exam-grid">
            <?php foreach ($exam_details as $exam) : ?>
                <div class="exam-card">
                    <strong>Product: <?php echo esc_html($exam['product_name']); ?></strong> <br>
                    <span>Exam Name: <?php echo esc_html($exam['name']); ?></span> <br>
                    <span>Max Hours: <?php echo esc_html($exam['max_hours']); ?></span> <br>
                    <span>Total Questions: <?php echo esc_html($exam['question_count']); ?></span> <br> <br>

                    <?php if ($exam['has_attempted']) : ?>
                        <a href="<?php echo esc_url(site_url('show-premium-exam-result-details?exam_id=' . $exam['paper_id'].'&result_id='.$exam['result_id'] )); ?>" class="show-result-btn-premium-exam">
                            Show Result
                        </a>
                    <?php else : ?>
                        <form method="post" action="<?php echo esc_url(site_url('mock-exam-attend-page')) ?>">
                            <input type="hidden" name="status" value="<?php echo esc_attr($exam['status']); ?>">
                            <input type="hidden" name="q_paper_id" value="<?php echo esc_attr($exam['paper_id']); ?>">
                            <input type="hidden" name="category" value="<?php echo esc_attr($exam['category']); ?>">
                            <input type="submit" name="submit_exam" class="attend-exam-btn" value="Attend Exam">
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p>No linked exams found. <a href="<?php echo esc_url(site_url('/buy-exam'))  ?>">Buy Exams </a></p>
    <?php endif; ?>
</div>
