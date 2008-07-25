<?php

require( dirname(__FILE__) . '/wp-config.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once(ABSPATH . '/saasta-common.php');

nocache_headers();

/* Process PayPal payment notification */


// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}

wp_mail("jjhellst@gmail.com", "paypal notification", "first stage");

// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen ('ssl://www.paypal.com', 80, $errno, $errstr, 30);

// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];

wp_mail("jjhellst@gmail.com", "paypal notification", "2nd stage");

if (!$fp) {
    // HTTP ERROR
    wp_mail("jjhellst@gmail.com", "paypal notification", "couldn't open http socket");
} else {
    fputs ($fp, $header . $req);
    while (!feof($fp)) {
        $res = fgets ($fp, 1024);
        if (strcmp ($res, "VERIFIED") == 0) {
            // check the payment_status is Completed
            // check that txn_id has not been previously processed
            // check that receiver_email is your Primary PayPal email
            // check that payment_amount/payment_currency are correct
            // process payment

            wp_mail("jjhellst@gmail.com", "Payment confirmation", "jebah $payment_amount $payment_currency $receiver_email!");
            
        }
        else if (strcmp ($res, "INVALID") == 0) {
            // log for manual investigation
            wp_mail("jjhellst@gmail.com", "PayPal payment failed", "xyz!");
        }
    }
    fclose ($fp);
}

?>
