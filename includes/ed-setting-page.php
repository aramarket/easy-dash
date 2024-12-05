<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Easy_Dash_Setting')) {
    class Easy_Dash_Setting {
		
		private $ew_testing_page;

        public function __construct() {
            add_action('admin_menu', array($this, 'register_menu_page'));
			add_action( 'init', [$this, 'add_woocommerce_review_capabilities'] );

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
		
		public function add_woocommerce_review_capabilities() {
			// Get the role object, e.g., 'shop_manager'
			$role = get_role('shop_manager'); // Replace 'shop_manager' with the desired role slug

			// Add WooCommerce review management capabilities
			$role->add_cap('moderate_comments'); // Required to moderate comments
			$role->add_cap('edit_comment'); // Required to edit comments
			$role->add_cap('read'); // Allows a user to read
		}
		
    }
}

