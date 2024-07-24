<?php
/*
 * Plugin Name:       EasyDash
 * Plugin URI:        https://easy-ship.in
 * Description:       EasyDash is Dashboard.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            AKASH
 * Update URI:        https://easy-ship.in
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin version and directory constants
if (!defined('EASY_DASH_VERSION')) {
    define('EASY_DASH_VERSION', '1.0.0');
}

if (!defined('EASY_DASH_DIR')) {
    define('EASY_DASH_DIR', plugin_dir_path(__FILE__));
}

// Include the main class file
require_once EASY_DASH_DIR . 'includes/ed-setting-page.php';

// Pages
require_once EASY_DASH_DIR . 'includes/ed-get-product-list.php';
require_once EASY_DASH_DIR . 'includes/ed-frequantly-bought-together.php'; 

// Functions
require_once EASY_DASH_DIR . 'includes/ed-general-function.php';
require_once EASY_DASH_DIR . 'includes/ed-order-profit-cal.php';

require_once EASY_DASH_DIR . 'includes/ed-order-profit-cal.php';



// Initialize the plugin
function run_easy_dash_main() {
    new Easy_Dash_Setting();
	new Ed_Order_Profit_Cal();
	new Ed_Get_Product_List();
	new Ed_Frequantly_Bought_Together();
}

run_easy_dash_main();
