<?php
  /* 
   * Saasta shop admin
   */

header ('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);

require_once('../../../wp-config.php');
require_once(ABSPATH . '/saasta-common.php');

get_currentuserinfo();
// no user
if ('' == $user_ID)
	return;

// Return orders that still require delivery
function query_undelivered_orders()
{
	global $wpdb;
    
    $order_tbl = $wpdb->prefix . "orders";
    return $wpdb->get_results("SELECT * FROM $order_tbl WHERE order_state <> 'shipped'");
}

function query_order($id)
{
	global $wpdb;
    
    $order_tbl = $wpdb->prefix . "orders";
    return $wpdb->get_row("SELECT * FROM $order_tbl WHERE id = $id");
}

function print_order_table_row($odd_even, $o)
{
    $row_bg = $odd_even ? "#eee" : "#ccc";
    $theme_dir = get_bloginfo('template_directory');

    print "<tr style=\"background-color:$row_bg;\">";
    print "<td>$o->id</td><td>$o->timestamp</td><td>$o->first_name</td><td>$o->last_name</td>\n";
    print "<td>$o->email</td><td>$o->order_state</td>\n";
    print "<td><pre>$o->address</pre></td>";
    print "<td><a href=\"$theme_dir/shop-admin.php/?edit_id=$o->id\">Edit</a></td>\n";
    print "<td><a href=\"$theme_dir/shop-admin.php/?delete_id=$o->id\">Delete</a></td>\n";
    print "</tr>";
}

function check_deletions()
{
    global $wpdb;

    if ($_GET['delete_id'])
    {
        $order_tbl = $wpdb->prefix . "orders";
        $id = $_GET['delete_id'];
        echo "<p style=\"background-color:#faa;\">Deleted order #$id</p>\n";

        $esc_id = $wpdb->escape($id);
        $wpdb->query("DELETE FROM $order_tbl WHERE id = $esc_id");
    }
}

function print_undelivered_orders()
{
?>
<h2>Currently Undelivered Orders</h2>

<table>
<tr>
  <th>ID</th><th>Timestamp</th><th>First name</th><th>Last name</th><th>E-mail</th>
  <th>Status</th><th>Address</th>
</tr>
<?php
$row_cnt = 0;
$undelivered_orders = query_undelivered_orders();
foreach ($undelivered_orders as $o) {
    print_order_table_row($row_cnt%2==0, $o);
    $row_cnt++;
}
?>
</table>
<?php
}

function check_edit_post_params($id)
{
    global $wpdb;
    $order_tbl = $wpdb->prefix . "orders";

    if ($_POST['order_state'])
    {
        $os = $_POST['order_state'];
        $wpdb->query("UPDATE $order_tbl SET order_state = '$os' WHERE id = $id");
    }
}

function print_option($cur_state, $o)
{
    if ($cur_state == $o)
        print "<option value=\"$o\" selected>$o</input>";
    else
        print "<option value=\"$o\">$o</input>";
}

function print_order_editor()
{
    global $wpdb;
    global $saasta_products;

    $admin_url = get_bloginfo('template_directory') . '/shop-admin.php';
    $id = $_GET['edit_id'];
    if ($id)
    {
        $edit_url = $admin_url . "?edit_id=$id";
        print "<h2>Saasta Order Editor - editing order #$id</h2>\n";

        check_edit_post_params($id);

        $o = query_order($id);
?>
<table>
     <tr><td id="oe">First name</td><td id="oe"><?php echo ($o->first_name); ?></td></tr>
     <tr><td id="oe">Order placed</td><td id="oe"><?php echo ($o->timestamp); ?></td></tr>
     <tr><td id="oe">Last name</td><td id="oe"><?php echo ($o->last_name); ?></td></tr>
     <tr><td id="oe">Address</td><td id="oe"><pre><?php echo ($o->address); ?></pre></td></tr>
</table>

<br/>

<h3>Ordered products</h3>
<table>
<tr><th>Product</th><th>Quantity</th></tr>
<?php
    $ordered_prods = $wpdb->get_results("SELECT product_id,qty FROM saasta_orders_products WHERE order_id = $id");
    foreach ($ordered_prods as $p) 
    {
        $prod_name = $saasta_products[$p->product_id]['name'];
        print "<tr><td id=\"oe\">$prod_name</td><td id=\"oe\">$p->qty</td></tr>\n";
    }
?>
</table>

<p>Total price: <strong><?php echo $o->price; ?></strong> EUR</p>

<br />

<table>
  <tr><td id="ord_status">Order status now:</td><td><?php echo ($o->order_state); ?></td></tr>
  <tr><td id="ord_status">Change to:</td>
    <form action="<?php echo $edit_url; ?>" method="post">
    <td>
      <select name="order_state">
        <?php print_option($o->order_state, "inbox");?>
        <?php print_option($o->order_state, "confirmed");?>
        <?php print_option($o->order_state, "paid");?>
        <?php print_option($o->order_state, "shipped");?>
      </select>
    </td>
    <td>
      <input name="submit" type="submit" value="Save"/>
    </td>
    </form>
  </tr>
</table>

<?php
    } else
    {
        die ("WTF");
    }
}

?>
<html>
<head>
<title>Saasta Shop Admin</title>
<style type="text/css">
<!--
body { font-family: Verdana; }
tr { vertical-align:top; }
td#oe { border-color:gray; border-width:1px; border-style:solid; }
#ord_status { color: #448; font-weight:bold; font-size: 1.2em; }
-->
</style>
</head>
<body>
<h1><a href="<?php bloginfo('template_directory'); ?>/shop-admin.php">Saasta Shop Admin</a></h1>

<?php check_deletions(); ?>

<?php 
if ($_GET['edit_id'])
    print_order_editor();
else
    print_undelivered_orders();
?>

</body>
</html>
