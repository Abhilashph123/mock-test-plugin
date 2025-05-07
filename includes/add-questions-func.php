<?php



// if (isset($_POST['csv_file_submit'])) {
//     $author_id = $_POST['author']; 
// 	$Mdny_Mock_Add_Question = new Mdny_Mock_Add_Question();
//     $Mdny_Mock_Add_Question->process_uploaded_csv_without_library_mock($author_id);
//     wp_cache_delete( 'admin_question_data', 'all_questions_data' );
	
// }

class Mdny_Mock_Add_Question{
	
// 	public function process_uploaded_csv_without_library_mock($author_id) {
//     if (isset($_FILES['csv_file']) && !empty($_FILES['csv_file']['tmp_name'])) {
//         $upload_dir = plugin_dir_path(__FILE__) . 'uploads/';
//         $file_path = $upload_dir . basename($_FILES['csv_file']['name']);

       
// 		if (!file_exists($upload_dir)) {
// 			mkdir($upload_dir, 0755, true); 
// 		}

//         // Move the uploaded file
//         if (!move_uploaded_file($_FILES['csv_file']['tmp_name'], $file_path)) {
//             wp_die('Failed to move uploaded file.');
//         }

//         try {
//             // Open the CSV file
//             if (($handle = fopen($file_path, 'r')) !== false) {
//                 $headers = fgetcsv($handle); // Read the first row as headers
                
//                 global $wpdb;
//                 $table_name = MDNY_QUESTION;

//                 while (($row = fgetcsv($handle)) !== false) {
//                     $record = array_combine($headers, $row); // Match row values with headers

//                     $unique_id = sanitize_text_field($record['unique_id']);
//                     $question = wp_kses_post($record['question']);
//                     $options = [
//                         'A' => sanitize_text_field($record['option_A']),
//                         'B' => sanitize_text_field($record['option_B']),
//                         'C' => sanitize_text_field($record['option_C']),
//                         'D' => sanitize_text_field($record['option_D'])
//                     ];
//                     $correct = sanitize_text_field($record['correct_option']);
//                     $explanation = wp_kses_post($record['explanation']);
//                     $difficulty = sanitize_text_field($record['difficulty']);
//                     $topic_names = explode(',', sanitize_text_field($record['topic_ids']));
//                     $paper_name = sanitize_text_field($record['selected_paper']);

//                     // Get topic IDs from names
//                     $topic_ids = [];
//                     $MDNY_TOPICS = MDNY_TOPICS;
//                     foreach ($topic_names as $topic_name) {
//                         $topic_id = $wpdb->get_var($wpdb->prepare("SELECT topic_id FROM $MDNY_TOPICS WHERE LOWER(topic_name) = LOWER(%s)", trim($topic_name)));
//                         if ($topic_id) {
//                             $topic_ids[] = $topic_id;
//                         }
//                     }

//                     // Get paper ID
//                     $MDNY_PAPERS = MDNY_PAPERS;
//                     $paper_id = $wpdb->get_var($wpdb->prepare(
//                         "SELECT paper_id FROM $MDNY_PAPERS WHERE LOWER(paper_name) = LOWER(%s) LIMIT 1",
//                         $paper_name
//                     ));

//                     // Validate correct answer exists
//                     if (!isset($options[$correct])) {
//                         continue;
//                     }

//                     // Insert data into the database
//                    $inserted = $wpdb->insert(
//                         $table_name,
//                         [
//                             'author'      => $author_id,
//                             'unique_id'   => $unique_id,
//                             'question'    => $question,
//                             'options'     => maybe_serialize($options),
//                             'correct'     => $correct,
//                             'difficulty'  => $difficulty,
//                             'topic_ids'   => maybe_serialize($topic_ids),
//                             'papers'      => $paper_id,
//                             'explanation' => $explanation
//                         ],
//                         ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s']
//                     );

//                    if (!$inserted) {
//     error_log('Failed to insert question with unique_id: ' . $unique_id);
//     error_log('Database Error: ' . $wpdb->last_error);
// } else {
//     error_log('Successfully inserted question with unique_id: ' . $unique_id);
// 	 $redirect_url = admin_url('admin.php?page=view-mock-questions&success=question_added');
//         wp_safe_redirect($redirect_url);
					   
// }
//                 }
//                 fclose($handle);
//             }

//             echo '<div class="updated"><p>Questions uploaded and saved successfully!</p></div>';
//         } catch (Exception $e) {
//             wp_die('Error processing the CSV file: ' . $e->getMessage());
//         }
//     } else {
//         wp_die('No file uploaded.');
//     }
// }

    public function add_new_mock_question_form(){

        global $wpdb;
        
        function custom_wp_editor_settings($args) {
            $args['quicktags'] = true; 
            $args['toolbar1'] = 'bold,italic,underline,latex'; 
            return $args;
        }
        add_filter('wp_editor_settings', 'custom_wp_editor_settings');
        
        ?>



<div class="wrap">
    <h1>Add New Question</h1>

   
<!--         <div class="csv-upload-container">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="author" value=" echo get_current_user_id(); ?>">
            <h2>Upload Questions in CSV File</h2>
            <input type="file" name="csv_file" accept=".csv" required />
            <br><br>
            <input type="submit" name="csv_file_submit" value="Upload CSV" />
        </form>
        </div> -->
    



    <form method="post" action="" class="add-new-question-wrapper" id="add-question-form">
        <label for="question">Question:</label><br>
        <?php
                $args = array(
                    'media_buttons' => true,
                    'textarea_rows' => get_option('default_post_edit_rows', 10),
                    'editor_class' => 'required',
                    
                );
                wp_editor('', 'question', $args);
                ?>

        <label for="options">Options:</label><br>
        <?php
                $options_name = ['A', 'B', 'C', 'D'];
                for ($i = 0; $i < 4; $i++) {
                    ?>
        <div>
            <span><?php echo $options_name[$i]; ?> : </span>
            <input type="text" name="options[<?php echo $options_name[$i]; ?>]" required>
            <input type="radio" name="correct" value="<?php echo $options_name[$i]; ?>" required>
        </div>
        <?php } ?><br>
        <label for="explanation">Explanation:</label>
        <?php
                $args = array(
                    'media_buttons' => false,
                    'textarea_rows' => get_option('default_post_edit_rows', 10),
                    'editor_class' => 'required',
                    
                );
                wp_editor('', 'explanation', $args);
                ?>
        <?php


               
                $datas= get_mock_topics_all_data();
                // _console($datas);
             
                if ($datas) {
                    ?>
                    <div class="paper-table-container">
                    <label>Topics</label><br>
                    <table id="topics_table" class="wp-list-table widefat fixed striped">
                    <thead><tr>
                    <th>Select</th>
                    <th>ID</th>
                    <th>Topic Name</th>
                    <th>Topic Parent</th>
                    <th>Topic Description</th>
                    </tr></thead>
                    <tbody>

                   <?php foreach ($datas as $data) {?>
                        <tr>
                        <td><input type="checkbox" name="selected_topics[]" value="<?php echo esc_attr($data->topic_id) ?> "></td>
                        <td> <?php echo esc_html($data->topic_id) ?></td>
                        <td> <?php echo esc_html($data->topic_name) ?></td>
                        <td> <?php echo esc_html($data->topic_parent) ?></td>
                        <td> <?php echo esc_html($data->description) ?></td>
                        </tr>

                    <?php } ?>

                    </tbody>
                    </table>
                    </div>

               <?php } else { ?>
                    <tr><td colspan="6">No Topics found.</td></tr>
               <?php } ?>

        <label for="difficulty">Difficulty Level:</label> <br>
        <select id="difficulty" name="difficulty" required>
            <option value="Easy">Easy</option>
            <option value="Medium">Medium</option>
            <option value="Hard">Hard</option>
        </select><br> <br>

        <input type="submit" name="save_mock_question" value="Save Question" class="button button-primary">
    </form>
</div>
<br>

<a class="button" href="<?php echo admin_url('admin.php?page=view-mock-questions') ?>" class="button">View all Questions</a>


    <script>
        document.getElementById('add-question-form').addEventListener('submit', function(e) {
            var editorContent = tinymce.get('question').getContent({
                format: 'text'
            }).trim();
            if (editorContent === '') {
                alert('Please fill out the question field.');
                tinymce.get('question').focus();
                e.preventDefault();
            }
        });
    </script>
<?php
} 




public function save_mock_question() {
    global $wpdb;
    $table_name = MDNY_MOCK_QUESTION;

    $question = wp_kses_post($_POST['question']) ? wp_kses_post($_POST['question']) : null;
    $explanation = wp_kses_post($_POST['explanation']) ? wp_kses_post($_POST['explanation']) : null;
    $options = !empty($_POST['options']) ? array_map('sanitize_text_field', $_POST['options']) : array();
    $correct = sanitize_text_field($_POST['correct']) ? sanitize_text_field($_POST['correct']) : null;
    $difficulty = sanitize_text_field($_POST['difficulty']) ? sanitize_text_field($_POST['difficulty']) : null;
    $selected_topics = isset($_POST['selected_topics']) ? array_map('intval', $_POST['selected_topics']) : array();

    // _console(maybe_serialize($selected_topics));
    $author_id = get_current_user_id();

    // Validate correct answer
    if (!isset($options[$correct])) {
        $redirect_url = admin_url('admin.php?page=add-mock-questions&error=invalid_answer');
        wp_safe_redirect($redirect_url);
        exit;
    }

    $result = $wpdb->insert(
        $table_name,
        array(
            'author' => $author_id,
            'question' => $question,
            'options' => maybe_serialize($options),
            'correct' => $correct,
            'difficulty' => $difficulty,
            'topic_ids' => maybe_serialize($selected_topics),
            'explanation' => $explanation
        ),
        array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
    );

    if ($wpdb->insert_id) {
        echo '<script>window.location.href="' . admin_url('admin.php?page=add-mock-questions&success=question_added')  . '";</script>';
        
    } else {
        $redirect_url = admin_url('admin.php?page=add-mock-questions&error=db_error');
    }

   
    exit;
}


public function edit_mock_question_form($question_id){
        
    global $wpdb;
    $table_name = MDNY_MOCK_QUESTION;
    $topics_table = MDNY_MOCK_TOPICS;
    $question = get_mock_questions_data_id($question_id);
 
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
        if (!isset($_POST['edit_question_nonce']) || !wp_verify_nonce($_POST['edit_question_nonce'], 'edit_question_nonce')) {
            wp_die('Nonce verification failed.');
        }
        // $unique_id = sanitize_text_field($_POST['unique_id']);
        // $question_section = intval($_POST['question_section']);
        $question_text = wp_kses_post($_POST['question']);
        $explanation = wp_kses_post($_POST['explanation']);
         $options = array_map('sanitize_text_field', $_POST['options']);
        $correct = sanitize_text_field($_POST['correct']);
        $difficulty = sanitize_text_field($_POST['difficulty']);
        // $selected_paper = sanitize_text_field($_POST['selected_papers']);
       
        if (!isset($options[$correct])) {
            wp_die('Invalid correct answer.');
        }

        $wpdb->update(
            $table_name,
            array(
                'question' => $question_text,
                'options' => maybe_serialize($options),
                'correct' => $correct,
                'difficulty' => $difficulty,
                'explanation' => $explanation
               
            ),
            array('qid' => $question_id),
            array(
              
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ),
            array('%d')
        );

        if ($wpdb->last_error) {
            wp_die('Database error: ' . $wpdb->last_error);
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>Question updated successfully!</p></div>';
        }
    }


    foreach($question as $questions){
    if (isset($_GET['delete_topic'])) {
        $topic_id_to_delete = intval($_GET['delete_topic']);
        $topic_ids = maybe_unserialize($questions['topic_ids']);
        if (($key = array_search($topic_id_to_delete, $topic_ids)) !== false) {
            unset($topic_ids[$key]);
        }
        $wpdb->update(
            $table_name,
            array('topic_ids' => maybe_serialize($topic_ids)),
            array('qid' => $question_id),
            array('%s'),
            array('%d')
        );
        $questions['topic_ids'] = maybe_serialize($topic_ids); // Update question object
    }



    $topic_ids = maybe_unserialize($questions['topic_ids']);  
// _console($topic_ids);
    $topics = [];
    if (!empty($topic_ids) && is_array($topic_ids)) {
        $placeholders = implode(',', array_fill(0, count($topic_ids), '%d'));

        // _console($placeholders);
        $query = "SELECT topic_id, topic_name, description FROM $topics_table WHERE topic_id IN ($placeholders)";
        $topics = $wpdb->get_results($wpdb->prepare($query, ...$topic_ids));
    }
}

   

    ?>
<div class="wrap">
<h1>Edit Question</h1> <br>
<?php   foreach($question as $questions){ ?>
<form method="post" action="" id="edit-question-form">
    <?php wp_nonce_field('edit_question_nonce', 'edit_question_nonce'); ?>
    <br>
    
    <br>
    <label for="question">Question:</label><br>
    <?php
            $args = array(
                'media_buttons' => true,
                'textarea_rows' => get_option('default_post_edit_rows', 10),
                'editor_class' => 'required',
                'tinymce' => array(
                    'toolbar1' => 'bold italic underline | latex',
                ),
            );
          
            wp_editor($questions['question'], 'question', $args);
            ?><br>

    <label for="options">Options:</label><br>
    <?php
            $options_name = ['A', 'B', 'C', 'D'];
            $options = maybe_unserialize($questions['options']);
            for ($i = 0; $i < 4; $i++) {
                $option_name = $options_name[$i];
                $option_value = isset($options[$option_name]) ? $options[$option_name] : '';
                ?>
    <div>
        <span><?php echo $option_name; ?> : </span>
        <input type="text" name="options[<?php echo $option_name; ?>]"
            value="<?php echo esc_attr($option_value); ?>" required>
        <input type="radio" name="correct" value="<?php echo $option_name; ?>"
            <?php checked($questions['correct'], $option_name); ?> required>
    </div>
    <?php } ?><br>

    <label for="explanation">Explanation:</label><br>
    <?php
            $args = array(
                'media_buttons' => false,
                'textarea_rows' => get_option('default_post_edit_rows', 10),
                'editor_class' => 'required',
                
            );
            wp_editor($questions['explanation'], 'explanation', $args);
            ?>

    <label for="difficulty">Difficulty Level:</label><br>
    <select id="difficulty" name="difficulty" required>
        <option value="Easy" <?php selected($questions['difficulty'], 'Easy'); ?>>Easy</option>
        <option value="Medium" <?php selected($questions['difficulty'], 'Medium'); ?>>Medium</option>
        <option value="Hard" <?php selected($questions['difficulty'], 'Hard'); ?>>Hard</option>
       
    </select><br><br>
    <div id="selected_topics" class="exam-topics">
        <label for="selected_topics">Associated Topics:</label>

   
              
                    <div class="single-topic">
                    <table class="wp-list-table widefat fixed striped edit-topic-table">
                    <thead><tr>
                    <th>Topic Name</th>
                    <th>Description</th>
                    <th>Action</th>
                    </tr></thead>
                    <tbody>

                   <?php  foreach ($topics as $topic) { ?>
                    <tr>
                        <?php ?> 
                    <td><?php echo esc_html($topic->topic_name ? $topic->topic_name : "No Topics Added") ?></td>
                    <td><?php echo esc_html($topic->description) ?></td>
                    <td> <a href="<?php echo admin_url('admin.php?page=add-mock-questions&edit=' . $question_id . '&delete_topic=' . $topic->topic_id); ?>" class="button">Remove</a></td>

                    </tr>
                   <?php  } ?>
                    </tbody>
                    </table>
                    </div>      
                    <a class="button" href="<?php echo admin_url('admin.php?page=add-mock-questions&add_mock_topics='.$question_id)?>">Add Topics</a>
              
                
                </div>

            <br>
                <input type="submit" name="update" value="Update Question" class="button button-primary">

            </form><br><?php  } ?>
            <div class="edit-navigation-container">
                <a class="button" href="<?php echo admin_url('admin.php?page=view-mock-questions')  ?>">View All Questions</a>
            </div>
            </div>

        <script>
        document.getElementById('edit-question-form').addEventListener('submit', function(e) {
        var editorContent = tinymce.get('question').getContent({
            format: 'text'
        }).trim();
        if (editorContent === '') {
            alert('Please fill out the question field.');
            tinymce.get('question').focus();
            e.preventDefault();
        }
        });
        </script>
<?php
    }





public function delete_question_callback($question_id){
    global $wpdb;
    $table_name = MDNY_MOCK_QUESTION;
    $deleted = $wpdb->delete($table_name, array('qid' => $question_id), array('%d'));

if ($deleted === false) {
    wp_die('Database error: ' . $wpdb->last_error);
} elseif ($deleted === 0) {
    echo '<div class="notice notice-warning is-dismissible"><p>No question was deleted. It might not exist.</p></div>';
} else {
    echo '<div class="notice notice-success is-dismissible"><p>Question deleted successfully!</p></div>';
    echo '<br><a class="button" href="' . esc_url(admin_url('admin.php?page=view-mock-questions')) . '">View all Questions</a>';
}
}


public function add_mock_topics($question_id) {
    global $wpdb;
    $question_table = MDNY_MOCK_QUESTION;
    
    if (!$question_id) {
        echo '<div class="notice notice-error"><p>Invalid question ID.</p></div>';
        return;
    }

    
    $question = get_mock_questions_data_id($question_id);

    
    $question_topic_ids = [];
_console($question);
    $topic_exist = $question['topic_ids'];
    
    if ($question && $topic_exist) {
        $decoded_topics = maybe_unserialize($question->topic_ids, true);
        if (is_array($decoded_topics)) {
            $question_topic_ids = $decoded_topics;
            
        }
    }

    

    // Fetch all available topics
    $topics_table = MDNY_MOCK_TOPICS;
    $topics = $wpdb->get_results("SELECT * FROM $topics_table");

    // Handle form submission
    if (isset($_POST['submit_topic_selection']) && isset($_POST['topic_ids'])) {
        _console($question->topic_ids); 
        _console($question_topic_ids); 
        $selected_topics = array_map('intval', $_POST['topic_ids']);

        // Merge new topics with existing ones to prevent overwriting
        $updated_topic_ids = array_unique(array_merge($question_topic_ids, $selected_topics));

        // Update database with the merged topic IDs
        $wpdb->update(
            $question_table,
            array('topic_ids' => maybe_serialize($updated_topic_ids)),
            array('qid' => $question_id),
            array('%s'),
            array('%d')
        );

        echo '<div class="updated is-dismissible"><p>Topics updated successfully.</p></div>';
    }
?>
  <div class="wrap">
      <h1>Add Topics to Question ID: <?php echo esc_html($question_id); ?></h1>

      <form method="post" action="">
          <div class="paper-serach-wrap">
              <a class="button" style="margin-top:5px;" href="<?php echo admin_url("admin.php?page=add-mock-questions&edit=$question_id"); ?>">Go back</a>
              <div>
                  <label for="course_search">Search Topics:</label>
                  <input type="text" id="course_search" placeholder="Type to search...">
              </div>
              <input type="hidden" name="add_topics_to_question" value="add_topics_to_question">
              <input type="hidden" name="question_id" value="<?php echo esc_attr($question_id); ?>">
          </div> 
          <br>
          <table class="wp-list-table widefat fixed striped" id="papers_table">
              <thead>
                  <tr>
                      <th><input type="checkbox" id="select-all-topics"></th>
                      <th>ID</th>
                      <th>Topic Name</th>
                      <th>Description</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if ($topics) {
                      foreach ($topics as $topic) {
                          $isChecked = in_array($topic->topic_id, $question_topic_ids) ? 'checked' : ''; ?>
                          <tr>
                              <td><input type="checkbox" name="topic_ids[]" value="<?php echo esc_attr($topic->topic_id); ?>" <?php echo $isChecked; ?>></td>
                              <td><?php echo esc_html($topic->topic_id); ?></td>
                              <td><?php echo esc_html($topic->topic_name); ?></td>
                              <td><?php echo esc_html($topic->description); ?></td>
                          </tr>
                      <?php }
                  } else { ?>
                      <tr><td colspan="4">No topics found.</td></tr>
                  <?php } ?>
              </tbody>
          </table>
          <div style="display:flex; align-items:center; width:20%; justify-content:space-between;">
              <?php submit_button('Update Topics', 'primary', 'submit_topic_selection'); ?>
          </div>
      </form>
  </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("course_search");
    const table = document.getElementById("papers_table");
    const rows = table.getElementsByTagName("tr");

    if (searchInput) {
        searchInput.addEventListener("keyup", function() {
            const filter = searchInput.value.toLowerCase();
            for (let i = 1; i < rows.length; i++) {
                const paperID = rows[i].getElementsByTagName("td")[1]?.textContent.toLowerCase() || "";
                const paperName = rows[i].getElementsByTagName("td")[2]?.textContent.toLowerCase() || "";
                if (paperName.includes(filter) || paperID.includes(filter)) {
                    rows[i].style.display = "table-row";
                } else {
                    rows[i].style.display = "none";
                }
            }
        });
    }

    const selectAllCheckbox = document.getElementById("select-all-topics");
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener("click", function() {
            const checkboxes = document.querySelectorAll("input[name='topic_ids[]']");
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }
});
</script>

<?php }


public function display_topics_for_question() {
    global $wpdb;
    $question_id = isset($_GET['question_id']) ? intval($_GET['question_id']) : 0;
    if (!$question_id) {
        echo '<div class="notice notice-error"><p>Invalid question ID.</p></div>';
        return;
    }

    // Fetch the topics already associated with the question
    $question = get_mock_questions_data_id($question_id);

    $question_topic_ids = array();
    if ($question && !empty($question->topic_ids)) {
        $question_topic_ids = maybe_unserialize($question->topic_ids);
    }

    $topics_table = MDNY_MOCK_TOPICS;
    $topics = $wpdb->get_results("SELECT * FROM $topics_table");
?>
   <div class="wrap">
   <h1>Add Topics to Question ID: <?php echo esc_html($question_id) ?></h1>

   <form method="post">
   <input type="hidden" name="add_topics_to_question" value="add_topics_to_question">
   <input type="hidden" name="question_id" value="<?php echo esc_attr($question_id) ?>">
   <table class="wp-list-table widefat fixed striped">
   <thead><tr>
   <th><input type="checkbox" id="select-all-topics"></th>
   <th>ID</th>
   <th>Topic Name</th>
   <th>Description</th>
   </tr></thead>
   <tbody>
<?php
    if ($topics) {
        foreach ($topics as $topic) {
            $checkbox_disabled = in_array($topic->topic_id, $question_topic_ids) ? 'disabled' : ''; ?>
           <tr>
           <td><input type="checkbox" name="topic_ids[]" value="<?php echo esc_attr($topic->topic_id) ?>" <?php echo  $checkbox_disabled ?>></td>
           <td> <?php echo esc_html($topic->topic_id) ?></td>
           <td> <?php echo esc_html($topic->topic_name) ?></td>
           <td> <?php echo esc_html($topic->description) ?></td>
           </tr>
           <?php
        }
    } else { ?>
       <tr><td colspan="4">No topics found.</td></tr>
   <?php } ?>

   </tbody>
   </table>
    <?php echo submit_button('Add Selected Topics to Question')?>
   </form>
   <a class="button" href="<?php echo admin_url('admin.php?page=view-mock-questions')?>">View all Questions</a>
   </div>
   <script>
document.addEventListener("DOMContentLoaded", function() {
    const selectAllCheckbox = document.getElementById("select-all-topics");
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener("click", function() {
            const checkboxes = document.querySelectorAll('input[name="topic_ids[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
});
</script>

 <?php
}

public function handlemock_add_topics_to_question() {
    if (isset($_POST['add_topics_to_question']) && isset($_POST['question_id']) && isset($_POST['topic_ids'])) {
        global $wpdb;
        
        $questions_table = MDNY_MOCK_QUESTION; 
        
        $question_id = intval($_POST['question_id']);
        $topic_ids = array_map('intval', $_POST['topic_ids']); 

       
        $question = get_mock_questions_data_id($question_id);

      

        
        if ($question) {
           
            if (!$question || empty($question->topic_ids)) {
                _console("No existing topics found or question not retrieved.");
                $current_topic_ids = [];
            } else {
                $current_topic_ids = maybe_unserialize($question->topic_ids, true);
            }
            
            // Ensure it's an array, otherwise initialize as empty
            if (!is_array($current_topic_ids)) {
                $current_topic_ids = [];
            }

            _console("current topics:- " . maybe_serialize($current_topic_ids));
            _console("new topics:- " . maybe_serialize($topic_ids));

            // Merge and remove duplicates
            $updated_topic_ids = array_unique(array_merge($current_topic_ids, $topic_ids));

            // Encode for database storage
            $updated_topic_ids_serialized = maybe_serialize($updated_topic_ids);
            _console("merged topics:- " . $updated_topic_ids_serialized);

            // Exit before updating the database (for debugging)
           

            // Update the database with the new topic IDs
            $wpdb->update(
                $questions_table,
                array('topic_ids' => $updated_topic_ids_serialized),
                array('qid' => $question_id),
                array('%s'),
                array('%d')
            );

            // Check for errors
            if ($wpdb->last_error) {
                echo '<div class="notice notice-error"><p>Database error: ' . esc_html($wpdb->last_error) . '</p></div>';
            } else {
                echo '<div class="notice notice-success is-dismissible"><p>Topics added successfully!</p></div>';
            }
        }
    }

    // Redirect back to the edit question page
    $redirect_url = admin_url('admin.php?page=add-mock-questions&edit=' . intval($_POST['question_id']));
    wp_safe_redirect($redirect_url);
    exit;
}





}