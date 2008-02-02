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

		// TODO: uncomment this when you have a mail server set up
		// TODO: might have to set fourth parameter as "From: some address"
		/*
		$post = get_post($postId, OBJECT);
		$author = get_userdata($post->post_author);

		$msg = "Your saasta '".$post->post_title."' (".$post->guid.") was just marked as a fave by ".$user->user_login."!";

		mail($author->user_email, "[saasta.fi] - your post got faved!", $msg);
		*/
	}
	else {
		$wpdb->query("delete from saasta_faves where user_id=".$user->ID." and post_id=".$postId);
	}
}

wp_redirect($_POST['redirect_to']);

?>
