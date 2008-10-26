<?php

/**
 * Retrieve list of top faved posts in a specific quarter.
 *
 * @param q Quarter (1..4)
 * @param y Year
 * @return list of associated arrays {title,author,fave_count,post_id,url,date_posted}
 */
function saasta_query_top_faved_posts($q,$y) {
	global $wpdb;

	$query = "SELECT DISTINCT 
       p.post_title        AS title,   
       u.display_name      AS author,
       COUNT(p.post_title) AS fave_count,
       f.post_id           AS post_id,
       CONCAT('".get_bloginfo('wpurl')."/?p=',f.post_id) AS url,
       DATE_FORMAT(p.post_date, '%b %d, %Y')           AS date_posted
FROM 
     saasta_posts p,
     saasta_faves f,
     saasta_users u
WHERE 
      f.post_id=p.ID AND 
      p.post_author <> f.user_id AND
      p.post_author=u.ID AND      
      QUARTER(p.post_date)=".$q." AND
      YEAR(p.post_date)=".$y."
GROUP BY 
      p.post_title 
ORDER BY 
      fave_count DESC";

	return $wpdb->get_results($query);
}

/* Return 'n' previous quarters as an array of quarters containing
   (year,q) tuples */
function saasta_prev_quarters($n)
{
    $t = getdate(time());
    $y = $t["year"];
    $m = $t["mon"];
    $q = 1+($m-1)/3;
    
    $r = array();

    // count backwards in quarters
    for ($i = 0; $i < $n; $i++)
    {
        $r[$i] = array("y" => $y, "q" => $q);

        $q = $q-1;

        if ($q == 0)
        {
            $y--;
            $q = 4;
        }
    }
    return $r;
}

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

$saasta_shipping_cost = 500;

$sticker_qties=array(0, 50, 100, 150, 200);

// List of products we're selling
$saasta_products = 
    array(1 => array("name" => "Sticker 1", "unit_price" => "1", 
                     "qty_opts" => $sticker_qties),
          2 => array("name" => "Sticker 2", "unit_price" => "1",
                     "qty_opts" => $sticker_qties));

?>
