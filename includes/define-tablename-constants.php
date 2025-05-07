<?php
global $wpdb;
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if ( ! defined( 'MDNY_MOCK_QUESTION' ) ) {
    define( 'MDNY_MOCK_QUESTION', $wpdb->prefix . 'mdny_mock_questions' );
}

if ( ! defined( 'MDNY_MOCK_QUESTION_PAPER' ) ) {
    define( 'MDNY_MOCK_QUESTION_PAPER', $wpdb->prefix . 'mdny_mock_question_paper' );
}

if ( ! defined( 'MDNY_MOCK_TOPICS' ) ) {
    define( 'MDNY_MOCK_TOPICS', $wpdb->prefix . 'mdny_mock_topics' );
}


if ( ! defined( 'MDNY_MOCK_PAPERS' ) ) {
    define( 'MDNY_MOCK_PAPERS', $wpdb->prefix . 'mdny_mock_papers' );
}

if ( ! defined( 'MDNY_MOCK_RESULTS' ) ) {
    define( 'MDNY_MOCK_RESULTS', $wpdb->prefix . 'mdny_mock_results' );
}



