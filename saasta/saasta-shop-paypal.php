<?php

require( dirname(__FILE__) . '/wp-config.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once(ABSPATH . '/saasta-common.php');

nocache_headers();

/* Process PayPal payment notification */

wp_mail("jjhellst@gmail.com", "Payment confirmation", "jebah!");

?>
