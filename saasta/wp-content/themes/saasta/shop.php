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
    $q = $wpdb->get_row("SELECT email,id,order_state,address,price FROM $tbl_orders WHERE order_ext_uid='".$i."'");

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

            $price = $q->price;
            $return_url = saasta_get_shop_base_url()."&thanks=true";

            print ("<p>Please click the below PayPal button to pay for your purchase:</p>");
            print ("<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">\n");
            print ("<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">\n");
            print ("<input type=\"hidden\" name=\"business\" value=\"jjhellst@gmail.com\">\n");
            print ("<input type=\"hidden\" name=\"item_name\" value=\"Saasta Merchandise\">\n");
            print ("<input type=\"hidden\" name=\"item_number\" value=\"$q->id\">\n");
            print ("<input type=\"hidden\" name=\"amount\" value=\"$price\">\n");
            print ("<input type=\"hidden\" name=\"no_shipping\" value=\"0\">\n");
            print ("<input type=\"hidden\" name=\"no_note\" value=\"1\">\n");
            print ("<input type=\"hidden\" name=\"page_style\" value=\"Saasta\">\n");
            print ("<input type=\"hidden\" name=\"currency_code\" value=\"EUR\">\n");
            print ("<input type=\"hidden\" name=\"return\" value=\"$return_url\">\n");
            print ("<input type=\"hidden\" name=\"lc\" value=\"FI\">\n");
            print ("<input type=\"hidden\" name=\"bn\" value=\"PP-BuyNowBF\">\n");
            print ("<input type=\"image\" src=\"https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif\" border=\"0\" name=\"submit\" alt=\"PayPal - The safer, easier way to pay online!\">\n");
            print ("<img alt=\"\" border=\"0\" src=\"https://www.paypal.com/en_US/i/scr/pixel.gif\" width=\"1\" height=\"1\">\n");
            print ("</form>\n");

            print ("<p>Once we have received your payment, we will process your order and deliver your products to the following address:</p>\n");

            print ("<pre style=\"font-size:1.2em\">$q->address</pre>\n");
            print ("<p>Thank you!</p\n");
        } else if ($q->order_state == 'paid')
        {
            echo "<h2>Order #$q->id paid and waiting to be delivered</h2>";
        }
    } else
    {
        echo '<h2>Unknown order!</h2>';
    }
}

function saasta_shop_form()
{
    global $saasta_products;
    global $saasta_shipping_cost;

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
        $unit_price=$v["unit_price"]; // in cents
        print '<tr><td><img src="http://www.saasta.fi/saasta/people/unknown.png"/></td>';
        print '<td>' . $n . '</td>';
        
        print '<td><select style="width:60px" name="product_' . $id . '">';
        foreach ($v["qty_opts"] as $o)
        {
            if ($o == 0)
                print "<option selected value=\"0\">None</option>";
            else
                print "<option selected value=\"$o\">$o</option>";
        }
        print '</select><input type="hidden" name="unit_price_product_'.$id.'" value="'.$unit_price.'"/></td>';

        print '</tr>';
    } 
?>
    <tr><td/><td>Price:</td>
      <td><strong id="order_price">0</strong> EUR
          <input type="hidden" name="to_js" id="shipping_cost" value="<?php print ($saasta_shipping_cost); ?>"/>
      </td>
    </tr>
    </table>

    <h3>Fill in your personal details</h3>
    <p style="color:#c00; font-weight:bold;">Note: you must fill in all the fields!</p>
    <table>
    <tr><td>First Name</td><td><input name="first_name" type="text"/></td></tr>
    <tr><td>Last Name</td><td><input name="last_name" type="text"/></td></tr>
    <tr><td>E-mail</td><td><input name="email" type="text"/></td></tr>
    <tr><td>Delivery Address</td><td><textarea rows="5" cols="40" name="address"></textarea></td></tr>

    <tr><td></td><td><input type="submit" value="Place order!"/></td></tr>
    </table>
    </form>

<?php
}
?>

<div id="content" class="narrowcolumn">

<?php if (isset($_GET['saved'])) { ?>
<h2>Thank you for shopping at saasta.fi!</h2>
<p>Please confirm your purchase order by following the instructions that you were just sent to via an automated e-mail.</p>
<p><a href="<?php echo saasta_get_shop_base_url();?>">Back to the shop..</a></p>

<?php 
} elseif (isset($_GET['confirm_id'])) {
    saasta_confirm_order($_GET['confirm_id']);
} elseif (isset($_GET['thanks'])) {
?><h2>PayPal payment successfully completed!</h2>
<p>Thank you for your order.  We will deliver your products as soon as possible.</p>
<?php
} else {
?>

<h2>Saasta Merchandise Shop</h2>

<h3>Select the products you wish to order</h3>
<br/>
<?php
saasta_shop_form();
}
?>

    <!-- Register Javascript handlers for computing order prices -->
    <script language="JavaScript" type="text/javascript">addLoadEvent(registerPriceCompute);</script>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
