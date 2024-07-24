<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Ed_Get_Product_List')) {
    class Ed_Get_Product_List {

        public function __construct() {
            add_action('admin_menu', array($this, 'register_menu_page'));
        }

        function register_menu_page(){
            add_submenu_page('easydash-main-url','Product list','Product List','manage_options','ed-product-list-url', array($this, 'product_list_page_creation'));
        }

        // Display the list of aggregated products and quantities from processing orders
        public function product_list_page_creation() {
            ?>
            <h2>Update Order Status</h2>
			<?php 
			if (isset($_POST['update_status']))  {
				if (class_exists('AramarketCustom')) {
					(new AramarketCustom())->autoCheckAndUpdateOrderStatus();
					echo '<div class="notice notice-success is-dismissible"><p>Order updated successfully</p></div>';
				} else {
					echo '<div class="notice notice-error is-dismissible"><p>Class AramarketCustom not found</p></div>';
				}
			}
			?>
            <form method="post" action="">
                <table class="form-table">
                <tr valign="top">
                    <th scope="row">Update Status</th>
                    <td><input type="submit" name="update_status" class="button-primary" value="Update Status"></td>
                </tr>
                </table>
            </form>

            <h2>Aggregated Products and Quantities from Processing Orders</h2>

            <form method="post" action="">
                <table class="form-table">
                <tr valign="top">
                    <th scope="row">Select Order Status</th>
                    <td>
                        <?php
                            $order_statuses = wc_get_order_statuses();
                            foreach ( $order_statuses as $status => $status_label ) {
                                $checked = isset( $_POST['selected_statuses'] ) && in_array( $status, $_POST['selected_statuses'] ) ? 'checked' : '';
                                echo '<label><input type="checkbox" name="selected_statuses[]" value="' . esc_attr( $status ) . '" ' . $checked . '>'
                                . esc_html( $status_label ) . '</label> | ';
                            }
                        ?>
                    </td>
                </tr>
                </table>
                <input type="submit" name="product_list_submit" class="button-primary" value="Submit">
            </form>

            <?php

            if (isset($_POST['product_list_submit'])) {
                // Get selected statuses from the form
                $selected_statuses = isset($_POST['selected_statuses']) ? $_POST['selected_statuses'] : array();
            
                // Call the function with selected statuses
                $aggregated_products = $this->get_aggregated_products_from_processing_orders($selected_statuses);
                ?>
                </br>
                <button class="print-button" onclick="printTable();">Print Table</button>
                <script>
                    function printTable() {
                        var printContents = document.getElementById("printable-table").innerHTML;
                        var originalContents = document.body.innerHTML;

                        document.body.innerHTML = printContents;
                        window.print();

                        document.body.innerHTML = originalContents;
                    }
                </script>
                <div id="printable-table" style="font-size: 15px; font-weight: 500;">
                    <p>Selected Order Statuses: 
                        <?php    
                        $order_statuses = wc_get_order_statuses();
                        foreach ($selected_statuses as $status) {
                            if (isset($order_statuses[$status])) {
                                $status_title = $order_statuses[$status];
                                echo $status_title . ', ';
                            }
                        }
                        ?>
                    </p>
                    <table class="eashyship-table">
                        <tr>
                            <th>Sr.no</th>
                            <th>Image</th>
                            <th>Product name</th>
                            <th>QTY</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                        <?php 
                            $counter = 0;
                            $Gtotal = 0;
                            foreach ($aggregated_products as $product_data) {
                                // Get product data
                                $product = wc_get_product($product_data['product_id']);
                                $product_name = $product->get_name();
                                $counter ++;
                                $total = ((int) $product_data['quantity'])*((int) get_post_meta( $product_data['product_id'], '_cogs', true ));
                                $Gtotal += $total;
                                ?>
                                <tr>
                                    <td><?php echo $counter ?></td>
                                    <td><img alt="img" style="length:50px; width:50px" src="<?php echo get_the_post_thumbnail_url($product_data['product_id'], 'thumbnail'); ?>"></td>
                                    <td><?php echo $product_data['quantity'].'x'.$product_name ?></td>
                                    <td><?php echo $product_data['quantity'] ?></td>
                                    <td><?php echo get_post_meta( $product_data['product_id'], '_cogs', true ); ?></td>
                                    <td><?php echo $total; ?></td>
                                </tr>
                                <?php
                            }
                        ?>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>Total</th>
                            <th></th>
                            <th><?php echo $Gtotal ?></th>
                        </tr>
                    </table>
                </div>
                <?php
            }
        }

        // Function to retrieve products and aggregated quantities from processing orders
        public function get_aggregated_products_from_processing_orders($selected_statuses) {
            global $wpdb;

            // Convert selected statuses to comma-separated string for SQL query
            $statuses_string = "'" . implode("', '", $selected_statuses) . "'";

            $aggregated_products = array(); // Initialize the array

            // ... Rest of your function code

            // Modify the WHERE clause to use the selected statuses
            $order_product_query = $wpdb->get_results(
                "SELECT order_item_meta.meta_value AS product_id, order_item_meta_qty.meta_value AS quantity
                FROM {$wpdb->prefix}woocommerce_order_items AS order_items
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_qty ON order_items.order_item_id = order_item_meta_qty.order_item_id
                WHERE order_items.order_item_type = 'line_item'
                AND order_item_meta.meta_key = '_product_id'
                AND order_item_meta_qty.meta_key = '_qty'
                AND order_items.order_id IN (
                    SELECT ID FROM {$wpdb->prefix}posts
                    WHERE post_type = 'shop_order'
                    AND post_status IN ($statuses_string)
                )"
            );

            foreach ($order_product_query as $product_data) {
                $product_id = $product_data->product_id;
                $quantity = $product_data->quantity;

                if (isset($aggregated_products[$product_id])) {
                    // If the product already exists, add the quantity to the existing total
                    $aggregated_products[$product_id]['quantity'] += $quantity;
                } else {
                    // If the product doesn't exist, create a new entry
                    $aggregated_products[$product_id] = array(
                        'product_id' => $product_id,
                        'quantity' => $quantity
                    );
                }
            }
            return $aggregated_products;
        }
    }
}
