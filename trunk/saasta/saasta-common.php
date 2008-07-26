<?php

function saasta_get_shop_base_url()
{
    return get_permalink(3480);
}

function saasta_order_paid($order_id)
{
    global $wpdb;

    $tbl_orders = $wpdb->prefix . "orders";

    $wpdb->query("UPDATE $tbl_orders SET order_state = 'paid', order_paid_on = CURRENT_TIMESTAMP() WHERE id = $order_id");

    $q = $wpdb->get_row("SELECT email,order_ext_uid,address FROM $tbl_orders WHERE id = $order_id");

    wp_mail($q->email, "saasta.fi merchandise payment confirmed",
            "
Your order #$order_id was successfully paid with PayPal.

We will deliver your products as soon as possible.

Thanks!");
}

$saasta_paypal_account = "jjhellst@gmail.com";

// List of products we're selling
$saasta_products = 
      array(1 => array("name" => "Sticker 1"),
            2 => array("name" => "Sticker 2"));

?>
