<?php

require( dirname(__FILE__) . '/wp-config.php' );

nocache_headers();

// If the user is logged in
$user = wp_get_current_user();
if ( $user->ID ) {
    wp_set_object_terms($_REQUEST['id'], 
                        explode(',', $_REQUEST['tags']),
                        "post_tag", true);

    wp_redirect($_REQUEST['redirect_to']);
} 
else
{
    $url = "wp-login.php?redirect_to=" . urlencode("saasta-addtags.php?id=".$_REQUEST['id']."&tags=".$_REQUEST['tags']."&redirect_to=".$_REQUEST['redirect_to']);
    wp_redirect($url);
}

?>
