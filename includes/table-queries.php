<?php
/**
 * @Use this functions to get the table data by passing the ID parameter.
 * 
 * @only fetches data.
 * 
 */

function get_mock_questions_data_id($question_id){
    global $wpdb;
    $table_name = MDNY_MOCK_QUESTION;
    $query = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE qid = %d", 
        $question_id
    );
    return $wpdb->get_results($query, ARRAY_A);
}


function get_mock_question_paper_data_id($q_paper_id){
    global $wpdb;
    $table_name = MDNY_MOCK_QUESTION_PAPER;
    $query = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE q_paper_id = %d", 
        $q_paper_id
    );    
    return $wpdb->get_row($query, ARRAY_A);
}


function get_mock_result_data_id($result_id){
    global $wpdb;
    $table_name = MDNY_MOCK_RESULTS;
    $query = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE result_id = %d",
        $result_id
    );
    return $wpdb->get_row($query, ARRAY_A);
}


function get_mock_topics_data_id($topic_id){
    global $wpdb;
    $table_name = MDNY_MOCK_TOPICS;
    $query = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE topic_id = %d",
        $topic_id
    );  
    return $wpdb->get_results($query, ARRAY_A);
}


/**
 * 
 * @Fetch every data from the table.
 * 
 * @only fetches data.
 * 
 */


 function Get_mock_topics_all_data(){
    global $wpdb;
    $table_name = MDNY_MOCK_TOPICS;
   
    return $wpdb->get_results("select * from $table_name ");
}

function Get_all_mock_questions_data(){
    global $wpdb;
    $table_name = MDNY_MOCK_QUESTION;
   
    return $wpdb->get_results("select * from $table_name ");
}


function Get_all_mock_results_data(){
    global $wpdb;
    $table_name = MDNY_MOCK_RESULTS;
    
    return $wpdb->get_results("select * from $table_name ");
}