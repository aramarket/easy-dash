<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Ed_Frequantly_Bought_Together')) {
    class Ed_Frequantly_Bought_Together {
	
        public function __construct() {
            add_action('admin_menu', array($this, 'register_menu_page'));
        }

        function register_menu_page(){
            add_submenu_page('easydash-main-url','Product Sold Together','Product Sold Together','manage_options','ed-product-sold-url', array($this, 'frequantly_bought_together_page'));
        }

        public function frequantly_bought_together_page() {
            ?>
                <h2>Find Frequently Bought Together</h2>

                <form method="post" action="">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Enter product ID</th>
                            <td>
                                <input type="number" name="product_id" required>
                                <input type="submit" name="submit_fbt" class="button-primary" value="Get Products">
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="update_status" value="1">
                </form>
            <?php

            if (isset($_POST['submit_fbt'])) {
                $product_id = intval($_POST['product_id']);
                if ($product_id) {
                    $products_sold_together = $this->get_products_sold_together($product_id);
                    if (!empty($products_sold_together)) {
                        echo '<h3>Products Sold Together:</h3>';
                        echo '<ul>';
                        foreach ($products_sold_together as $pid => $quantity) {
                            $product = wc_get_product($pid);
                            $product_title = $product ? $product->get_name() : 'Unknown Product';
                            echo '<li>Product ID: ' . esc_html($pid) . ' - Quantity Sold: ' . esc_html($quantity) . ' - Title: ' . esc_html($product_title) .'</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo 'No products found that were sold together with this product.';
                    }
                } else {
                    echo 'Please enter a valid Product ID.';
                }
            }
        }

        public function get_products_sold_together($product_id) {
            global $wpdb;
        
            // Step 1: Get all order IDs containing the specified product ID
            $order_ids = $wpdb->get_col($wpdb->prepare("
                SELECT DISTINCT order_id
                FROM {$wpdb->prefix}woocommerce_order_items oi
                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
                WHERE oim.meta_key = '_product_id' AND oim.meta_value = %d
            ", $product_id));
        
            if (!is_array($order_ids) || empty($order_ids)) {
                return array();
            }
        
            $products_sold = array();
        
            foreach ($order_ids as $order_id) {
                $order = wc_get_order($order_id);
        
                if (!$order) {
                    continue;
                }
        
                foreach ($order->get_items() as $item) {
                    $pid = $item->get_product_id();
                    $quantity = $item->get_quantity();
        
                    if ($pid == $product_id) {
                        continue; // Skip the input product itself
                    }
        
                    if (isset($products_sold[$pid])) {
                        $products_sold[$pid] += $quantity;
                    } else {
                        $products_sold[$pid] = $quantity;
                    }
                }
            }
        
            // Sort the array by quantity sold in descending order
            arsort($products_sold);
        
            $top_10_products = array_slice($products_sold, 0, 10, true);
        
            return $top_10_products;
        }
    }
}

