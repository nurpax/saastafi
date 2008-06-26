<?php

require( dirname(__FILE__) . '/wp-config.php' );

nocache_headers();

if (isset($_REQUEST['add_post_id'])) {
    $add = true;
    $postId = (int) $_REQUEST['add_post_id'];
} else if (isset($_REQUEST['del_post_id'])) {
    $add = false;
    $postId = (int) $_REQUEST['del_post_id'];
}

// If the user is logged in
$user = wp_get_current_user();
if ( $user->ID ) {
    if ($add) {
        $wpdb->query("INSERT ignore into ".$wpdb->prefix."faves (user_id,post_id,fave_date) values (".$user->ID.",".$postId.", NOW())");

        $post = get_post($postId, OBJECT);
        $author = get_userdata($post->post_author);

		$foo = $wpdb->get_row("SELECT COUNT(*) AS cnt FROM ".$wpdb->prefix."faves WHERE post_id=".$postId);

        $msg = "Your saasta '".$post->post_title."' (".$post->guid.") was just faved! It now has a total of ".$foo->cnt." faves! High five!";

        wp_mail($author->user_email, "[saasta.fi] - your post got faved!", $msg);
    }
    else {
        $wpdb->query("delete from ".$wpdb->prefix."faves where user_id=".$user->ID." and post_id=".$postId);
    }
    wp_redirect($_REQUEST['redirect_to']);
} 
else
{
    $url = "wp-login.php?redirect_to=" . urlencode("saasta-handlefaves.php" . "?add_post_id=" . $postId . "&redirect_to=" . $_REQUEST['redirect_to']);
    wp_redirect($url);
}


?>
