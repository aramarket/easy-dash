<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Easy_Dash_Setting')) {
    class Easy_Dash_Setting {
		
		private $ew_testing_page;

        public function __construct() {
            add_action('admin_menu', array($this, 'register_menu_page'));

			$this->$ew_testing_page = new Easy_WhatsApp_Testing_Page();
        }

        function register_menu_page(){
            add_menu_page('EasyDash Dashboard','EasyDash','manage_options','easydash-main-url', array($this, 'dashboard_page'),'dashicons-chart-pie', 7);
        }
		
        public function dashboard_page() {
            ?>
                <H2>This is Home of EasyDash</H2>
            <?php
        }
    }
}

