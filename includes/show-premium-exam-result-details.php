<?php
get_header( );
?>
<div class="si-container entry-content">
    <?php
    $result_id =  isset($_GET['result_id']) ? $_GET['result_id'] : null;
    $Exam_result_actions = new Exam_result_actions();
    $Exam_result_actions->view_student_mock_result($result_id);
    ?>
</div>
<?php

get_footer( );