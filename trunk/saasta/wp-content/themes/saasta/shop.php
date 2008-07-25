<?php
/*
Template Name: Saasta Shop

The purpose of this page template is to serve as an purchase order (PO) 
input form.

The purpose of this form & its associated logic is as follows:

1) Input POs from customers

2) Jot down the PO into our DB

3) Sent a confirmation e-mail to the customer asking him to confirm
his order.  The e-mail contains a secret link that when clicked will
activate the order.  This is done to protect ourselves against
spammers.

4) Accept confirmation URL clicks from customers, sending activated
POs further down our purchase pipeline.

Database handling & validation code is located in /saasta/saasta-shop.php.
*/
?>

<?php get_header(); ?>

<?php

require_once(ABSPATH . '/saasta-common.php');

function saasta_confirm_order($ext_id)
{
    global $saasta_products, $wpdb;

    $tbl_orders = $wpdb->prefix . "orders";

    $i = $wpdb->escape($ext_id);
    $q = $wpdb->get_row("SELECT email,id,order_state,address FROM $tbl_orders WHERE order_ext_uid='".$i."'");

    if ($q)
    {
        // Mark new orders as 'confirmed':
        if ($q->order_state == 'inbox') 
        {
            $wpdb->query("UPDATE $tbl_orders SET order_state = 'confirmed' WHERE id = $q->id");
        }
        
        // Let the user know about his confirmation:
        if ($q->order_state == 'inbox' || $q->order_state == 'confirmed')
        {
            echo "<h2>Order #$q->id confirmed and waiting for payment</h2>";
            echo "<p>We're waiting to confirm that your order has been successfully paid for.  Once we can verify that the payment for the purchase was successful, we will ship the product to the below address:</p>";
            echo "<pre style=\"font-size:1.2em\">$q->address</pre>";
        }
    } else
    {
        echo '<h2>Unknown order!</h2>';
    }
}

function saasta_shop_form()
{
    global $saasta_products;
    $redirectURI = attribute_escape($_SERVER['REQUEST_URI']);

    print '<form action="'.get_option('siteurl').'/saasta-shop.php" method="post">';
    print '<input type="hidden" name="po" value="1"/>';
    print '<input type="hidden" name="redirect_to" value="'.$redirectURI.'"/>';
?>
    <table>
    <tr><th> </th><th>Product</th><th>Quantity</th></tr>
<?php
    foreach ($saasta_products as $key => $v) {
        $id = $key;
        $n = $v["name"];
        print '<tr><td><img src="http://www.saasta.fi/saasta/people/unknown.png"/></td>';
        print '<td>' . $n . '</td>';
        
        print '<td><select style="width:60px" name="product_' . $id . '">';
        print '<option selected value="0">None</option>';
        print '<option value="50">50</option>';
        print '<option value="100">100</option>';
        print '</select></td>';

        print '</tr>';
    } 
?>
    <tr><td/><td>E-mail (*)</td><td><input name="email" type="text"/></td></tr>
    <tr><td/><td>Delivery address (*)</td><td><textarea rows="5" cols="40" name="address"></textarea></td></tr>
    <tr><td></td><td></td><td><input type="submit" value="Place order!"/></td></tr>
    </table>
    </form>
<?php
}
?>

<div id="content" class="narrowcolumn">

<?php if (isset($_GET['saved'])) { ?>
<h2>Thank you for shopping at saasta.fi!</h2>
<p>Please confirm your purchase order by following the instructions that you were just sent to via an automated e-mail.</p>
<p><a href="<?php echo attribute_escape($_SERVER['REQUEST_URI']);?>">Back to the shop</a></p>

<?php 
} elseif (isset($_GET['confirm_id'])) {

saasta_confirm_order($_GET['confirm_id']);

} else {
?>
<h2>Saasta Merchandise Shop</h2>

<h3>Place your order for the below products</h3>
<br/>
<?php
saasta_shop_form();
}
?>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
