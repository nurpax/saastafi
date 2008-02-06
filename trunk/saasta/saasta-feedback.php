<?php

require( dirname(__FILE__) . '/wp-config.php' );

nocache_headers();

if (isset($_POST['body'])) {
    $subject = "[saasta.fi feedback] - ";
    if (isset($_POST['subject'])) {
        $subject .= $_POST['subject'];
    }

    wp_mail("saasta-feedback@saasta.fi", $subject, $_POST['body']);
    
    wp_redirect($_REQUEST['redirect_to'] . "&saved=1");
} else
{
    wp_redirect($_REQUEST['redirect_to']);
}

?>
