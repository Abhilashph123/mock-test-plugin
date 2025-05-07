<?php

add_filter('tutor_dashboard/nav_items','mock_premium_exam_dashboard_menu');
function mock_premium_exam_dashboard_menu($link) {

        $custom_button = array(
            'premium-exam' => array(
                'title' => 'Purchased Exams',
                'icon' => 'tutor-icon-mortarboard-o',
            ),
         
        );
        $index = array_search('enrolled-courses', array_keys($link));
        if ($index !== false) {
            $link = array_slice($link, 0, $index + 1, true) +
                    $custom_button +
                    array_slice($link, $index + 1, null, true);
        }
     
        return $link;
    }

    add_filter( 'load_dashboard_template_part_from_other_location',function($path){

        global  $wp_query;

        if($wp_query->query_vars['tutor_dashboard_page']=='premium-exam'){
            return  MDNY_MOCKTEST_PATH.'includes/premium-exam.php';
            
        }

        return $path;     
});
    

