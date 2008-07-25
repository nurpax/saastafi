<?php

require( dirname(__FILE__) . '/wp-config.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once(ABSPATH . '/saasta-common.php');

nocache_headers();

/* Auto-create saasta PO SQL table. */
function saasta_po_db_setup()
{
    global $wpdb;

    $table_orders = $wpdb->prefix . "orders";
    $table_orders_products = $wpdb->prefix . "orders_products";

    /* Check whether or not we have a faves table? */
    if($wpdb->get_var("SHOW TABLES LIKE '$table_orders'") != $table_orders) {
        dbDelta("
CREATE TABLE $table_orders (
  id int(11) NOT NULL AUTO_INCREMENT,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  email VARCHAR (55),
  address TEXT,
  order_ext_uid VARCHAR (40),
  order_state ENUM ('inbox', 'confirmed', 'paid', 'shipped'),
  PRIMARY KEY (id))");

        $wpdb->query("
CREATE TABLE $table_orders_products (
  order_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  qty int(11) NOT NULL)");
    }
}

function saasta_get_shop_base_url()
{
    return get_permalink(3480);
}

function saasta_send_confirmation_email($order_id)
{
    global $wpdb;
    global $saasta_products;

    $tbl_orders = $wpdb->prefix . "orders";
    $tbl_orders_products = $wpdb->prefix . "orders_products";

    $q = $wpdb->get_row("SELECT email,order_ext_uid,address FROM $tbl_orders WHERE id = $order_id");
    
    // TODO hardcoded URL :( -- how can we get the page id without hardcoding it?
    $page_url = saasta_get_shop_base_url();
    $confirm_url =  $page_url . "&confirm_id=" . $q->order_ext_uid;

    $order_text = "";

    $ordered_prods = $wpdb->get_results("SELECT product_id,qty FROM saasta_orders_products WHERE order_id = $order_id");
    foreach ($ordered_prods as $p) {
        $prod_name = $saasta_products[$p->product_id]['name'];
        $order_text = $order_text . "$prod_name (quantity: $p->qty)\n";
    }
    
    if ($q)
    {
        $text = "
Thank you for ordering our products!

In order to complete your order, we request you to confirm your order by clicking the following link:

$confirm_url

This will let us know that your purchase was genuine and not initiated by a nasty spammer.

Here's a summary of your order:

$order_text

Once confirmed and paid, the products you ordered will be sent to the following address:

$q->address

Thank you!
";
        wp_mail($q->email, "Purchase Confirmation", $text);
    } else
    {
        die("Couldn't find purchase order!");
    }
}

function saasta_save_order()
{
    global $saasta_products, $wpdb;

    saasta_po_db_setup();
   
    $tbl_orders = $wpdb->prefix . "orders";
    $tbl_orders_products = $wpdb->prefix . "orders_products";

    $email = $wpdb->escape($_POST['email']);
    $address = $wpdb->escape($_POST['address']);

    // TODO e-mail error checks & re-direction if not valid

    $wpdb->query("INSERT INTO $tbl_orders (order_state, email, address) VALUES ('inbox', '$email', '$address')");
    $order_id = mysql_insert_id($wpdb->dbh);

    foreach ($saasta_products as $id => $v) {
        $product_id = 'product_' . $id;
        if (isset($_POST[$product_id]) && $_POST[$product_id] != '0')
        {
            $qty = $_POST[$product_id];
            $wpdb->query("INSERT INTO $tbl_orders_products (order_id,product_id,qty) VALUES ($order_id,$id, $qty)");
        }
    }

    // Compute confirmation UID that will be used when a confirmation
    // e-mail is sent to the customer.  When the customer confirm the
    // order by clicking the UID link, we match that the order in DB
    // matches with the URL the customer clicked.
    //
    // We intentionally obscure the ID for the customer so that people
    // don't start trying to hack other people's orders by trying low
    // order ID numbers.
    $ts = $wpdb->get_row("SELECT timestamp FROM " . $wpdb->prefix . "orders WHERE id = $order_id");
    $ext_uid_str = $ts->timestamp . '_' . $email . '_' . $order_id . '_' . SECRET_KEY;
    $wpdb->query("UPDATE $tbl_orders SET order_ext_uid = '" . md5($ext_uid_str) . "' WHERE id = $order_id");

    saasta_send_confirmation_email($order_id);
}

if (isset($_POST['po'])) {
    saasta_save_order();
    wp_redirect($_REQUEST['redirect_to'] . "&saved=1");
} else
{
    wp_redirect($_REQUEST['redirect_to']);
}

?>
