<?php

require( dirname(__FILE__) . '/wp-config.php' );

nocache_headers();

if (isset($_POST['add_post_id'])) {
    $add = true;
    $postId = (int) $_POST['add_post_id'];
} else if (isset($_POST['del_post_id'])) {
    $add = false;
    $postId = (int) $_POST['del_post_id'];
}

// If the user is logged in
$user = wp_get_current_user();
if ( $user->ID ) {
    if ($add) {
        $wpdb->query("insert ignore into saasta_faves (user_id,post_id) values (".$user->ID.",".$postId.")");

        $post = get_post($postId, OBJECT);
        $author = get_userdata($post->post_author);

		$foo = $wpdb->get_row("SELECT COUNT(*) AS cnt FROM saasta_faves WHERE post_id=".$postId);

        $msg = "Your saasta '".$post->post_title."' (".$post->guid.") was just faved! It has now a total of ".$foo->cnt." faves! High five!";

        wp_mail($author->user_email, "[saasta.fi] - your post got faved!", $msg);
    }
    else {
        $wpdb->query("delete from saasta_faves where user_id=".$user->ID." and post_id=".$postId);
    }
}

wp_redirect($_POST['redirect_to']);

?>
