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
	}
	else {
		$wpdb->query("delete from saasta_faves where user_id=".$user->ID." and post_id=".$postId);
	}
}

wp_redirect($_POST['redirect_to']);

?>
