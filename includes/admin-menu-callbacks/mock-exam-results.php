<?php


add_action('admin_menu',function(){

    add_submenu_page(
        'midnay-mock-test',
        'Mock Test Results',
        'Mock Test Results',
        'manage_options',
        'mock-test-results',
        'mock_test_results_callback',
      6
    );

});

if (isset($_POST['generate_all_mock_results'])) {
    add_action('admin_init', 'mock_result_pdf_generation');
   }
   function mock_result_pdf_generation() {

   
        global $wpdb;
   
       ob_end_clean();
       
       require_once MDNY_MOCKTEST_PATH . '/vendor/autoload.php'; 
   
       ob_start();
       mock_pdf_build();
       $htmlContent = ob_get_clean();
   
       
       class PDFWithWatermark extends TCPDF {
           public function Header() {
              
               $this->SetFont('Helvetica', '', 50); 
               $this->SetTextColor(200, 200, 200); 
               $this->SetAlpha(0.2); 
               
               
               $this->StartTransform();
               $this->Rotate(25, $this->getPageWidth()/2, $this->getPageHeight() / 2);
               $this->Text(35, 140, 'MOCK TEST'); 
               $this->StopTransform();
   
               $this->SetAlpha(1); 
           }
   
           public function Footer() {
               // You can optionally add a footer here if needed
           }
       }
   
       // Create an instance of the custom PDF class
       $pdf = new PDFWithWatermark();
       // $pdf->SetCreator(PDF_CREATOR);
       // $pdf->SetAuthor('Your Name');
       // $pdf->SetTitle('Dynamic Watermark Example');
       // $pdf->SetSubject('Dynamic Watermark PDF');
       // $pdf->SetKeywords('TCPDF, PDF, watermark, dynamic');
   
       // Add the first page
       $pdf->AddPage();
   
       // Add the main content
       $pdf->SetFont('Helvetica', '', 12);
       $pdf->SetTextColor(0, 0, 0); // Black text for table content
       $pdf->writeHTML($htmlContent, true, true, true, false, '');
   
       // Output the PDF
       $pdf->Output( 'Mock exam Results' .'.pdf', 'D'); 
   
       exit;
   }

function mock_test_results_callback(){

    $show = isset($_GET['show']) ? $_GET['show']: '' ;
    $result_id = isset($_GET['result_id']) ? $_GET['result_id'] :'' ;

    if($show && $show == 'exam_report'){
        $Exam_result_actions = new Exam_result_actions();
        $Exam_result_actions->view_student_mock_result($result_id);
        return;
    }else{
        ?>


<?php
        $mdny_mock__exam_results = new mdny_mock__exam_results();
        $search = isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : '';
      
        echo '<div class="wrap">';
        ?>
        <form method="post">
            <input type="submit" name="generate_all_mock_results" class="button button-primary" value="Download All Mock Results PDF">
        </form>
        <?php
        echo '<h1 class="wp-heading-inline"> Mock Results</h1>';
//         echo '<a href="'.admin_url('admin.php?page=all-mock-exams&action=edit_mock_exam&result_id='.$result_id).'" class="page-title-action">Edit Exam</a>';
        echo '<form method="GET">';
        echo '<input type="hidden" name="page" value="mock-test-results"/>';
        echo '<input type="hidden" name="result_id" value="'.$result_id.'"/>';
        $mdny_mock__exam_results->prepare_items();
            // Search form
        $mdny_mock__exam_results->search_box('Search', 'search_id');
        $mdny_mock__exam_results->display();
        echo '</form>';
        echo '</div>';	
    }
    
}


function mock_pdf_build() {
    global $wpdb;

    $results_table = MDNY_MOCK_RESULTS;
    $papers_table = MDNY_MOCK_QUESTION_PAPER;
    $topics_table = MDNY_MOCK_TOPICS;

    $results = $wpdb->get_results("
        SELECT r.*, p.question_paper_name, p.category 
        FROM $results_table r
        LEFT JOIN $papers_table p ON r.q_paper_id = p.q_paper_id
        ORDER BY r.result_id DESC
    ");

    if (empty($results)) {
        echo '<div style="text-align: center; padding: 20px;">No results found</div>';
        return;
    }

    echo '<h2 style="text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 10px;">Mock Exam Results Summary</h2>';
    echo '<table border="1" cellpadding="4" cellspacing="0" style="width:100%; font-family: helvetica, sans-serif; font-size: 10px;">';
    echo '<tr style="background-color: #3498db; color: #fff; font-weight: bold;">
        <th>User Email</th>
        <th>Phone Number</th>
        <th>Exam Name</th>
        <th>Topics</th>
        <th>Overall %</th>
        <th>Category</th>
        <th>Attended Date</th>
    </tr>';

    $row_toggle = false;

    foreach ($results as $result) {
        $row_style = $row_toggle ? 'background-color:#f2f2f2;' : '';
        $row_toggle = !$row_toggle;

        $topic_results = json_decode($result->topics, true);
        $topic_summary = '';

        if (!empty($topic_results) && is_array($topic_results)) {
            foreach ($topic_results as $topic_id => $data) {
                $topic_name = $wpdb->get_var($wpdb->prepare(
                    "SELECT topic_name FROM $topics_table WHERE topic_id = %d", 
                    $topic_id
                ));
                $topic_percentage = ($data['total'] > 0 && is_numeric($data['correct'])) ? round(($data['correct'] / $data['total']) * 100) : 0;
                $topic_summary .= ($topic_name ? $topic_name : 'Topic ' . $topic_id) . ':' . $topic_percentage . '%<br>';
            }
        } else {
            $topic_summary = 'N/A';
        }

        echo '<tr style="' . $row_style . '">';
        echo '<td>' . esc_html($result->user_email) . '</td>';
        echo '<td>' . esc_html($result->phone_number ?? 'N/A') . '</td>';
        echo '<td>' . esc_html($result->question_paper_name) . '</td>';
        echo '<td>' . $topic_summary . '</td>';
        $percentage = is_numeric($result->percentage) ? round($result->percentage, 2) . '%' : 'N/A';
        echo '<td>' . $percentage . '</td>';
        echo '<td>' . esc_html($result->category ?? 'N/A') . '</td>';
        echo '<td>' . (!empty($result->exam_submission_date) ? date('F j, Y g:i a', strtotime($result->exam_submission_date)) : 'N/A') . '</td>';
        echo '</tr>';
    }

    echo '</table>';
}





