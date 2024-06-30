<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Ed_Order_Profit_Cal')) {
    class Ed_Order_Profit_Cal {

        private $ed_function;

        public function __construct() {
            add_action('add_meta_boxes', array($this, 'order_profit_cal_box'));
            add_action('save_post', array($this, 'handel_order_profit_cal_submit'), 10, 2);

            $this->ed_function = new Easy_Dash_Function();
        }
        public function order_profit_cal_box() {
            add_meta_box(
                'cost_ship_cogs',  // The ID should be in lower case and without spaces
                'Cost of Ship & Cogs',
                array($this, 'order_profit_cal_content'),  // Correct the callback function name
                'shop_order',  // This specifies the post type, in this case, 'shop_order' is for WooCommerce orders
                'side',  // 'normal' is the context where the meta box will appear
                'default'  // 'default' is the priority
            );
        }
        // Callback function to display the content of the meta box
        public function order_profit_cal_content($post) {
            // Get order ID
            $order_id = $post->ID;
            $order = wc_get_order($order_id);
            if (!$order) {
                echo 'Order not found.';
                return;
            }
            // Get the order total
            $order_total = $order->get_total();
            // Get custom field values
            $meta_shipping_cost 	= get_post_meta($order_id, '_custom_shipping_cost', true);
            $meta_cogs_cost     	= get_post_meta($order_id, '_custom_cogs_cost', true);

            if (empty($meta_shipping_cost)) {
                $custom_shipping_cost = 100;
            } else{
                $custom_shipping_cost = $meta_shipping_cost;
            }

            if (empty($meta_cogs_cost)) {
				$cogs_data = $this->ed_function->calculate_cogs_for_order($order_id);
				if(!$cogs_data['success']){
					echo $cogs_data['message'];
					return;
				}
                $custom_cogs_cost = $cogs_data['result'];
            } else {
                $custom_cogs_cost = $meta_cogs_cost;
            }
			
            $net_profit = $order_total-($custom_shipping_cost + $custom_cogs_cost);
            $net_persantage = number_format((($net_profit / $order_total) * 100), 1).'%';

            // Output HTML for order profit calculation
            ?>
            <div>
                <p>
                    <label><?php _e('Shipping Cost'); ?></label>
                    <input type="text" class="" name="custom_shipping_cost" id="custom_shipping_cost" value="<?php echo $custom_shipping_cost; ?>">
                </p>
                <p>
                    <label><?php _e('COGS'); ?></label><br>
                    <input type="text" class="" name="custom_cogs_cost" id="custom_cogs_cost" value="<?php echo $custom_cogs_cost; ?>">
                </p>
                <p>
                    <label><?php _e($order_total.' - '.($custom_shipping_cost + $custom_cogs_cost).' = '.$net_profit.' ('.$net_persantage.') - Net Profit'); ?></label>
                </p>
                    <?php wp_nonce_field('custom_order_boxx', 'nonce_of_ship_cogs'); ?>
                <p>
                    <button type="submit" class="button button-primary" name="add_shipping_and_cogs"><?php _e('Save'); ?></button>
                </p>
            </div>
            <?php
        }
        
        
        public function handel_order_profit_cal_submit($order_ID, $post) {
            // Check if nonce is set
            if (!isset($_POST['nonce_of_ship_cogs'])) {
                return;
            }
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce_of_ship_cogs'], 'custom_order_boxx')) {
                return;
            }
            // Check if user has permissions to save
            if (!current_user_can('edit_post', $order_ID)) {
                return;
            }
            // Check if the custom flag is set to "1"
            if (isset($_POST['add_shipping_and_cogs'])) {
                // Save custom field values
                if (isset($_POST['custom_shipping_cost'])) {
                    $shipping_cost = sanitize_text_field($_POST['custom_shipping_cost']);
                    $cogs_cost = sanitize_text_field($_POST['custom_cogs_cost']);
                    update_post_meta($order_ID, '_custom_shipping_cost', $shipping_cost);
                    update_post_meta($order_ID, '_custom_cogs_cost', $cogs_cost);
                } else {
                    // Assuming you have detected an error and need to display a message
                    $error_msg = 'There was an error updating the order. Please try again.';
                    // Add an error notice to the session
                    wc_add_notice($error_msg, 'error');
                }
            }
        }
    }
}

