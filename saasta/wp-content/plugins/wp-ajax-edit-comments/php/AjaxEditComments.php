<?php 
require_once("../../../../wp-config.php");
header('Content-Type: text/html; charset='.get_option('blog_charset').'');
if (isset($_POST['action']) && isset($WPrapAjaxEditComments)) {
	$commentID = isset($_POST['cid'])? (int) $_POST['cid'] : 0;
	$postID = isset($_POST['pid'])? (int) $_POST['pid'] : 0;
	$action = $_POST['action'];
	
	if ($action == "gettimeleft") {
		$WPrapAjaxEditComments->get_time_left($commentID);
		die('');
	} elseif ($action == "editcomment") {
		$WPrapAjaxEditComments->get_comment($commentID, $postID);
		die('');
	} elseif ($action == "savecomment") {
			if ($WPrapAjaxEditComments->is_comment_owner($postID)) {
				check_admin_referer('wp-ajax-edit-comments_save-comment');
			}
			$comment = get_comment($commentID, ARRAY_A);
			$comment['comment_content'] = trim(urldecode($_POST['comment_content']));
			if ($WPrapAjaxEditComments->can_edit_name($commentID, $postID)) {
				$comment['comment_author'] = trim(strip_tags(urldecode($_POST['comment_author'])));
			}
			if ($WPrapAjaxEditComments->can_edit_email($commentID, $postID)) {
				$comment['comment_author_email'] = trim(strip_tags(urldecode($_POST['comment_author_email'])));
			}
			if ($WPrapAjaxEditComments->can_edit_url($commentID, $postID)) {
				$comment['comment_author_url'] = trim(strip_tags(urldecode($_POST['comment_author_url'])));
			//Quick JS Test
			if ($comment['comment_author_email'] == "undefined") {$comment['comment_author_email']='';}
			if ($comment['comment_author_url'] == "undefined") {$comment['comment_author_url']='http://';}
			if ($comment['comment_author'] == "undefined") {$comment['comment_author']='';}
		}
		$response = new WP_Ajax_Response();
		$response = $WPrapAjaxEditComments->save_comment($commentID, $postID, $comment);
		$response = apply_filters('wp_ajax_comments_save_comment', $response, $comment, $_POST);
		$response->send();
		die('');
	} else {
		check_ajax_referer($action . "_" . $commentID);
	}
	
	
	switch ($action) {
		case "deletecomment":
			$message = $WPrapAjaxEditComments->delete_comment($commentID, $postID); 
			$message = ($message == 1 ? 1: $WPrapAjaxEditComments->get_error($message));
			die('' . $message);
			break;
		case "spamcomment":
			$message = $WPrapAjaxEditComments->spam_comment($commentID, $postID);
			$message = ($message == 1 ? 1: $WPrapAjaxEditComments->get_error($message));
			die('' . $message);
			break;
		case "approvecomment":
			$message = $WPrapAjaxEditComments->approve_comment($commentID, $postID);
			$message = ($message == 1 ? 1: $WPrapAjaxEditComments->get_error($message));
			die('' . $message);
			break;
		case "unapprovecomment":
			$message = $WPrapAjaxEditComments->moderate_comment($commentID, $postID);
			$message = ($message == 1 ? 1: $WPrapAjaxEditComments->get_error($message));
			die('' . $message);
			break;
	}
}
?>
