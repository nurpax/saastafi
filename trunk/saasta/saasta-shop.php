<?php

require_once(ABSPATH . '/saasta-common.php');

nocache_headers();

if (isset($_POST['po'])) {
    wp_mail("jjhellst@gmail.com", "purchase order", "haks");
    
    wp_redirect($_REQUEST['redirect_to'] . "&saved=1");
} else
{
    wp_redirect($_REQUEST['redirect_to']);
}

?>
