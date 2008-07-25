<?php
/*
Template Name: Saasta Shop

The purpose of this page template is to serve as an purchase order (PO) 
input form.

The purpose of this form & its associtaed logic is as follows:

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
        
        print '<td><select style="width:60px" value="qty" name="' . $id . '">';
        print '<option selected value="0">None</option>';
        print '<option value="50">50</option>';
        print '<option value="100">100</option>';
        print '</select></td>';

        print '</tr>';
    } 
?>
    <tr><td></td><td><input type="submit" value="Checkout!"/></td></tr>
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

<?php } else { ?>
<h2>Saasta Merchandise Shop</h2>

<h3>Place your order for the below products</h3>
<br/>
<?php saasta_shop_form(); ?>
<?php } ?>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
