<?php
/**
 * Plugin Name: MIDNAY MOCK TEST
 * Description: Midnay.
 * Author URI: https://midnay.com/
 * Requires at least: 6.5
 * Requires PHP:      8.0+
 * Version:           0.1.0
 * Author:            Midnay
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mdny-mock-test
 * 
 */
if (!defined('ABSPATH')) {
    exit; 
}

if ( ! defined( 'MDNY_MOCKTEST_VERSION' ) ) {
    define( 'MDNY_MOCKTEST_VERSION', '0.1.0' );
}

if ( ! defined( 'MDNY_MOCKTEST_URL' ) ) {
    define('MDNY_MOCKTEST_URL', plugin_dir_url(__FILE__));
}

if ( ! defined( 'MDNY_MOCKTEST_PATH' ) ) {
    define('MDNY_MOCKTEST_PATH', plugin_dir_path(__FILE__));
}
if ( ! defined( 'MDNY_MOCKTEST_FILE' ) ) {
    define('MDNY_MOCKTEST_FILE', __FILE__);
}


// Include Activation Class
require_once MDNY_MOCKTEST_PATH. 'includes/includes.php';

