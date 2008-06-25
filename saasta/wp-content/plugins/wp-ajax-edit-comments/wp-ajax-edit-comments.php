<?php
/*
Plugin Name: WP Ajax Edit Comments
Plugin URI: http://www.raproject.com/ajax-edit-comments-20/
Description: Allows users and admin to edit their comments inline.  Admin and editors can edit all comments.
Author: Ronald Huereca
Version: 2.1.0.0
Author URI: http://www.raproject.com/
Generated At: www.wp-fun.co.uk;
*/ 

/*  Copyright 2008  Ronald Huereca  (email : ronalfy at [gmail] . com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('WPrapAjaxEditComments')) {
    class WPrapAjaxEditComments	{
		var $commentClassName = "edit-comment"; 
		var $authorClassName = "edit-author";
		var $cookieName = "WPAjaxEditCommentsComment"; /*Please do not edit these variables*/
		var $adminOptionsName = "WPAjaxEditComments20";
		var $userOptionsName = "WPAjaxEditAuthorUserOptions";
		var $localizationName = "ajaxEdit";
		var $minutes = 5;
		/**
		* PHP 4 Compatible Constructor
		*/
		function WPrapAjaxEditComments(){$this->__construct();}
		
		/**
		* PHP 5 Constructor
		*/		
		function __construct(){
		$this->adminOptions = $this->get_admin_options();
		$this->pluginDir = get_bloginfo('wpurl') . '/wp-content/plugins/wp-ajax-edit-comments';
		$this->initialize_errors();
		$this->skip = false;
		//css
		add_action("wp_head", array(&$this,"add_css"));
		add_action('admin_head', array(&$this,"add_css")); 
		//JavaScript
		add_action('admin_print_scripts', array(&$this,'add_post_scripts'),1000); 
		add_action('wp_print_scripts', array(&$this,'add_post_scripts'),1000);
		//Custom actions for other plugin authors
		add_action('add_wp_ajax_comments_js_post', array(&$this,'add_post_scripts'));
		add_action('add_wp_ajax_comments_js_admin', array(&$this,'add_post_scripts'));
		add_action('wp_ajax_comments_comment_edited', array(&$this, 'edit_notification'),1,2);
		add_action('wp_ajax_comments_comment_edited', array(&$this, 'comment_edited'),2,2);
		add_action('wp_ajax_comments_remove_content_filter', array(&$this, 'comment_filter'));
		//Initialization stuff
		add_action('init', array(&$this, 'init'));
		//Admin options
		add_action('admin_menu', array(&$this,'add_admin_pages'));
		//When a comment is posted
		add_action('comment_post', array(&$this, 'comment_posted'),100,1);
		
		
		//Yay, filters.
		add_filter('comment_text', array(&$this, 'add_edit_links'), '1000'); //Low priority so other HTML can be added first
		add_filter('get_comment_author_link', array(&$this, 'add_author_spans'), '1000'); //Low priority so other HTML can be added first

		}
		/* add_author_spans - Adds spans to author links */
		function add_author_spans($content) {
			global $comment;
			if (empty($comment)) { return $content; }
			if ($this->can_edit($comment->comment_ID, $comment->comment_post_ID) != 1) { return $content; }
			$content = "<span id='$this->authorClassName" . "$comment->comment_ID'>$content</span>";
			return $content;
		}
		/* add_edit_links - Adds edit links to post and admin panels */
		function add_edit_links($content) {
			global $comment;
			if ($this->skip) { $this->skip = false; return $content; }
			if (empty($comment)) { return $content; }
			if ($this->can_edit($comment->comment_ID, $comment->comment_post_ID) != 1) { return $content; }
			$ajax_url = $this->pluginDir . "/php/AjaxEditComments.php";
			$edit_url = clean_url($this->pluginDir . "/php/comment-editor.php" . "?action=editcomment&p=$comment->comment_post_ID&c=$comment->comment_ID&KeepThis=true&TB_iframe=true&height=400&width=560&modal=true", "editcomment_$comment->comment_ID");
			$spam_url = clean_url( wp_nonce_url( $ajax_url . "?action=spamcomment&p=$comment->comment_post_ID&c=$comment->comment_ID", "spamcomment_$comment->comment_ID" ) );
			$delete_url = clean_url( wp_nonce_url( $ajax_url . "?action=deletecomment&p=$comment->comment_post_ID&c=$comment->comment_ID", "deletecomment_$comment->comment_ID" ) );
			$moderate_url = clean_url( wp_nonce_url( $ajax_url . "?action=unapprovecomment&p=$comment->comment_post_ID&c=$comment->comment_ID", "unapprovecomment_$comment->comment_ID" ) );
			$approve_url = clean_url( wp_nonce_url( $ajax_url . "?action=approvecomment&p=$comment->comment_post_ID&c=$comment->comment_ID", "approvecomment_$comment->comment_ID" ) );
			//Icon Classes
			$spam_class=$moderate_class=$edit_class=$approve_class=$delete_class=$beginning=$seperator=$end=$clearfix=$timer_class='';
			$edit_admin = "edit-comment-admin-links";
			if ($this->adminOptions['allow_icons'] == 'true') {
					$spam_class = 'spam-comment';
					$moderate_class = 'moderate-comment';
					$edit_class = 'edit-comment';
					$approve_class = 'approve-comment';
					$delete_class = 'delete-comment';				
			} else {
				$edit_admin = "edit-comment-admin-links-no-icon";
				$timer_class = "ajax-edit-time-left-no-icon";
				$beginning = "[ ";
				$seperator = " | ";
				$end = " ]";
			}
			if ($this->adminOptions['clear_after'] == 'true') {
				$clearfix = "clearfix";
			}
			/*If you're wondering why the JS is inline, it's because people with 500+ comments were having their browsers lock up.  With inline, the JS is run as needed.  Not elegant, but the best solution.*/
			$content = '<div class="'.$this->commentClassName. '" id="' . $this->commentClassName . $comment->comment_ID . '">' . $content .  '</div>';
			if (!$this->is_comment_owner($comment->comment_post_ID)) {
				//For anonymous users
				$content .= "<div class='$edit_admin $clearfix' id='edit-comment-user-link-$comment->comment_ID'>";
				$content .= "<a title='Ajax Edit Comments' class='$edit_class' href='$edit_url' onclick='AjaxEditComments.edit(this); return false;' id='edit-$comment->comment_ID'>";
				$content .= __("Click to Edit",$this->localizationName);
				$content .= "</a>";
				//Check to see if timer is on
				if ($this->adminOptions['show_timer'] == 'true') {
					//Check to see if user is logged in and admin can indefinitely edit
					if (!$this->can_indefinitely_edit($comment->user_id)) {
						$content .= " <span class='ajax-edit-time-left $timer_class' id='ajax-edit-time-left-$comment->comment_ID'></span>";
					}
				}
				$content .= "</div>";
			} else {
				$options = $this->get_user_options();
				if (is_admin() && $options['admin_editing'] == "false") { //We're in the admin panel
					$content .= '<div class="' .$edit_admin. ' ' . $clearfix.'" id="' . $this->commentClassName . '-admin-links' . $comment->comment_ID . '">';
					//Spans are used here instead of LIs because of style conflicts in the admin panel
					$content .= "$beginning<a title='Ajax Edit Comments' class='$edit_class' href='$edit_url' onclick='AjaxEditComments.edit(this); return false;' id='edit-$comment->comment_ID'>";
					$content .= __('Edit', $this->localizationName);
					$content .= "</a>";
					if ($_GET['page'] == 'akismet-admin') {
						//We're in the Akismet sub-panel - Add appropriate links
						$content .= "$seperator<a class='$moderate_class' href='$moderate_url' onclick='AjaxEditComments.moderate(this); return false;' id='moderate-$comment->comment_ID'>";
						$content .= __('Moderate', $this->localizationName);
						$content .= "</a>";
						$content .= "$seperator<a class='$approve_class' href='$approve_url' onclick='AjaxEditComments.approve(this); return false;' id='approve-$comment->comment_ID'>";
						$content .= __('Not Spam', $this->localizationName);
						$content .= "</a>";
						$content .= "$seperator<a class='$delete_class' href='$delete_url' onclick='AjaxEditComments.delete_comment(this); return false;' id='delete-$comment->comment_ID'>";
						$content .= __('Delete', $this->localizationName);
						$content .= "</a>";
					}
					$content .= "$end</div>";
				} elseif ($options['comment_editing'] == "true") { //We're in a post
					$content .= '<div class="' . $edit_admin . ' ' . $clearfix . '" id="' . $this->commentClassName . '-admin-links' . $comment->comment_ID . '">';
					//Spans are used here instead of LIs because of style conflicts in the admin panel
					$content .= "$beginning<a title='Ajax Edit Comments' class='$edit_class' href='$edit_url' onclick='AjaxEditComments.edit(this); return false;' id='edit-$comment->comment_ID'>";
					$content .= __('Edit', $this->localizationName);
					$content .= "</a>";
					$content .= "$seperator<a class='$moderate_class' href='$moderate_url' onclick='AjaxEditComments.moderate(this); return false;' id='moderate-$comment->comment_ID'>";
					$content .= __('Moderate', $this->localizationName);
					$content .= "</a>";
					$content .= "$seperator<a class='$spam_class' href='$spam_url' onclick='AjaxEditComments.spam(this); return false;' id='spam-$comment->comment_ID'>";
					$content .= __('Spam', $this->localizationName);
					$content .= "</a>";
					$content .= "$seperator<a class='$delete_class' href='$delete_url' onclick='AjaxEditComments.delete_comment(this); return false;' id='delete-$comment->comment_ID'>";
					$content .= __('Delete', $this->localizationName);
					$content .= "</a>";
					$content .= "$end</div>";
				}
				
			}
			return $content;
			
		} //end function add_edit_links
		/* approve_comment - Marks a comment as approved 
		Parameters - $commentID, $postID
		Returns - 1 if successful, a string error message if not */
		function approve_comment($commentID=0, $postID = 0) {
			if ($this->is_comment_owner($postID)) {
				$status = wp_set_comment_status($commentID, 'approve')? 1 : 'approve_failed';
				return $status;
			} else {
				return 'approve_failed_permission';
			}
		}
		/*
		can_edit - Determines if a user can edit a particular comment on a particular post
		Parameters - commentID, postID
		Returns - Enumeration (0=unsuccessful,1=successful,or string error code)
		*/
		function can_edit($commentID = 0, $postID = 0) {
			global $wpdb;
			
			//Check if admin/editor/post author
			if ($this->is_comment_owner($postID)) {
				return 1;
			}
			
			//Check to see if admin allows comment editing
			if ($this->adminOptions['allow_editing'] == "false") {
				return 'no_user_editing';
			}
			//Get the current comment
			$query = "SELECT UNIX_TIMESTAMP(comment_date) time, comment_author_email, comment_author_IP, comment_date_gmt, comment_post_ID, comment_ID, user_id  FROM $wpdb->comments where comment_ID = $commentID";
			$comment = $wpdb->get_row($query, ARRAY_A); 
			if (!$comment) { return 'get_comment_failed'; }
			//Check to see if the comment is spam
			if ($comment['comment_approved'] === 'spam') { 
				return 'comment_spam';
			}
			
			//Check to see if the user is logged in and can indefinitely edit
			if ($this->can_indefinitely_edit($comment['user_id'])) {
			return 1;
			}
			//Now check if options allow editing after an additional comment has been made
			if ($this->adminOptions['allow_editing_after_comment'] == "false") {
				//Admin doesn't want users to edit - so now check if any other comments have been left
				$query = "SELECT comment_ID from $wpdb->comments where comment_post_ID = $postID and comment_type <> 'pingback' and comment_type <> 'trackback' order by comment_ID DESC limit 1";
				$newComment = $wpdb->get_row($query, ARRAY_A); 
				if (!$newComment) { return 'new_comment_posted'; }
				//Check to see if there is a higher comment ID
				if ($commentID != $newComment['comment_ID']) { return 'new_comment_posted'; }
			}
			//Get post security key
			$postContent = $wpdb->get_row("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = " . $comment['comment_post_ID'] . " and meta_key = '_" . $comment['comment_ID'] . "'", ARRAY_A);//$wpdb->get_row("SELECT post_content from $wpdb->posts WHERE post_type = 'ajax_edit_comments' and guid = $commentID order by ID desc limit 1", ARRAY_A);
			if (!$postContent) { return 'comment_edit_denied'; }
			
			$hash = md5($comment['comment_author_IP'] . $comment['comment_date_gmt']);
			
			//Now check to see if there's a valid cookie
			if (isset($_COOKIE[$this->cookieName . $commentID . $hash])) {
				if ($_COOKIE[$this->cookieName . $commentID . $hash] != $postContent['meta_value']) { return 'comment_edit_denied'; }
			} else {
				return 'comment_edit_denied';
			}
			//Now we check to see if there is any time remaining for comments
			$timestamp = $comment['time'];
			$time = current_time('timestamp',get_option('gmt_offset'))-$timestamp;
			$minutesPassed = round(((($time%604800)%86400)%3600)/60); 
			
			//Get the time the admin has set for minutes
			$minutes = $this->adminOptions['minutes'];
			if (!is_numeric($minutes)) {
				$minutes = $this->minutes; //failsafe
			}
			if ($minutes < 1) {
				$minutes = $this->minutes;
			}
			if (($minutesPassed - $minutes) > 0) {
				return 'comment_time_elapsed';
			} else {
				return 1;  //Yay, user can edit
			}
			return 'get_comment_failed'; //Ah, too bad.
		} //End function can_edit
		/* can_edit_name - Checks to see if a user can edit a name
		Parameters - $commentID, $postID
		Returns true if one can, false if not */
		function can_edit_name($commentID, $postID) {
			if ($this->is_comment_owner($postID)) { return true; }
			$comment = get_comment($commentID, ARRAY_A);
			if ($this->is_logged_in($comment['user_id'])) { //logged in
				if ($this->adminOptions['registered_users_name_edit'] == "true") { return true;}
			} else { //not logged in 
				if ($this->adminOptions['allow_name_editing'] == "true") { return true;}
			}
			return false;
		}
		/* can_edit_email - Checks to see if a user can edit an e-mail
		Parameters - $commentID, $postID
		Returns true if one can, false if not */
		function can_edit_email($commentID, $postID) {
			$comment = get_comment($commentID, ARRAY_A);
			//Return false if comment is pingback or trackback
			if ($comment['comment_type'] == "pingback" || $comment['comment_type'] == 'trackback') { return false; }
			if ($this->is_comment_owner($postID)) { return true; }	
			
			if ($this->is_logged_in($comment['user_id'])) { //logged in
				if ($this->adminOptions['registered_users_email_edit'] == "true") { return true;}
			} else { //not logged in 
				if ($this->adminOptions['allow_email_editing'] == "true") { return true;}
			}
			return false;
		}
		/* can_edit_url - Checks to see if a user can edit a url
		Parameters - $commentID, $postID
		Returns true if one can, false if not */
		function can_edit_url($commentID, $postID) {
			if ($this->is_comment_owner($postID)) { return true; }
			$comment = get_comment($commentID, ARRAY_A);
			if ($this->is_logged_in($comment['user_id'])) { //logged in
				if ($this->adminOptions['registered_users_url_edit'] == "true") { return true;}
			} else { //not logged in 
				if ($this->adminOptions['allow_url_editing'] == "true") { return true;}
			}
			return false;
		}
		/* can_edit_options - Checks to see if a non-admin user can edit various options 
		Parameters - $commentID, $postID
		Returns true if one can, false if not */
		function can_edit_options($commentID, $postID) {
			if ($this->is_comment_owner($postID)) { return true; }
			$comment = get_comment($commentID, ARRAY_A);
			if ($this->is_logged_in($comment['user_id'])) { //logged in
				if ($this->adminOptions['registered_users_url_edit'] == "true" || $this->adminOptions['registered_users_email_edit'] == "true" || $this->adminOptions['registered_users_name_edit'] == "true" ) { return true;}
			} else { //not logged in 
				if ($this->adminOptions['allow_url_editing'] == "true" || $this->adminOptions['allow_email_editing'] == "true" || $this->adminOptions['allow_name_editing'] == "true") { return true;}
			}
			return false;
		}
		/* can_indefinitely_edit
		Parameters - $userID
		Returns - true if can, false if not */
		function can_indefinitely_edit($userID = 0) {
			if ($this->is_logged_in($userID)) {
				//User is logged in and this is the user's comment - Does admin allow indefinite editing?
				if ($this->adminOptions['registered_users_edit'] == "true") {
					return true; //Logged in user can indefinitely edit
				}
			}
			return false;
		}
		/* can_scroll 
		Checks to see if an admin can scroll to the comment or not 
		Returns - true if can, false if not*/
		function can_scroll() {
			if ($this->is_comment_owner()) {
				if ($this->adminOptions['javascript_scrolling'] == "true") {
					return true;
				}
			}
			return false;
		}
		/* check_spam - Checks an edited comment for spam 
		Parameters - $commentID, $postID
		Returns - True if spam, false if not */
		function check_spam($commentID = 0, $postID = 0) {
			$options = $this->adminOptions;
			//Check to see if spam protection is enabled
			if ($options['spam_protection'] == "none") { return false;} 
			//Return if user is post author or can edit posts
			if ($this->is_comment_owner($postID)) { return false; }
			
			if (function_exists("akismet_check_db_comment") && $options['spam_protection'] == 'akismet') { //Akismet
				//Check to see if there is a valid API key
				if (akismet_verify_key(get_option('wordpress_api_key')) != "failed") { //Akismet
					$response = akismet_check_db_comment($commentID);
					if ($response == "true") { //You have spam
						wp_set_comment_status($commentID, 'spam');
						return true;
					}
				}
			} elseif ($options['spam_protection'] == "defensio" && function_exists('defensio_post') ) { //Defensio
				global $defensio_conf, $wpdb;
				$comment = get_comment($commentID, ARRAY_A);
				if (!$comment) { return true; }
				$comment['owner-url'] = $defensio_conf['blog'];
				$comment['user-ip'] = $comment['comment_author_IP'];
				$comment['article-date'] = strftime("%Y/%m/%d", strtotime($wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE ID=" . $comment['comment_post_ID'])));
				$comment['comment-author'] = $comment['comment_author'];
				$comment['comment-author-email'] = $comment['comment_author_email'];
				$comment['comment-author-url'] = $comment['comment_author_url'];
				$comment['comment-content'] = defensio_unescape_string($comment['comment_content']);
				if (!isset($comment['comment_type']) or empty($comment['comment_type'])) {
					$comment['comment-type'] = 'comment';
				} else {
					$comment['comment-type'] = $comment['comment_type'];
				}
				if (defensio_reapply_wp_comment_preferences($comment) === "spam") {
					return true;
				}
				$results = defensio_post('audit-comment',$comment);
				$ar = Spyc :: YAMLLoad($results);
				if (isset($ar['defensio-result'])) {
					if ($ar['defensio-result']['spam']) {
						wp_set_comment_status($commentID, 'spam');
						return true;
					}
				}
			}
			return false;			
		} //end function check_spam
		/* - comment_edited - Run after a comment has successfully been edited 
		Parameters - $commentID, $postID*/
		function comment_edited($commentID = 0, $postID = 0) {
			//Clear the comment cache
			if (function_exists('clean_comment_cache')) { clean_comment_cache($commentID); }
			
			//For WP Cache and WP Super Cache
			if (function_exists('wp_cache_post_change')) {
				@wp_cache_post_change($postID);
			}
			//Get out if user is admin or post owner
			if ($this->is_comment_owner($postID)) { return; }
			
			//Increment the number of edited comments
			$this->increment_edit_count();
		} //end function comment_edited
		/* comment_posted - This function is run whenever a comment is posted - 
		Adds a cookie and security key for future editing
		Parameters - $commentID (the comment's ID)
		*/
		function comment_posted($commentID=0) {
			global $wpdb;
			//Get comment
			$comment = get_comment($commentID, ARRAY_A);
			//Some sanity checks
			if (!$comment) { return;}
			
			if ($comment['comment_approved'] === "spam") { return; }	
			//If admin, exit since we don't want to add anything
			if ($this->is_comment_owner($comment['comment_post_ID'])) {
				return $commentID;
			}
			//Don't save data if user can indefinitely edit
			if ($this->can_indefinitely_edit($comment['user_id'])) { return; }
			//Check to see if admin allows comment editing 
			if ($this->adminOptions['allow_editing'] == "false") { return;}
			
			//Get hash and random security key
			$hash = md5($comment['comment_author_IP'] . $comment['comment_date_gmt']);
			$rand = 'wpAjax' . $hash . md5($this->random()) . md5($this->random());
			
			//Get the minutes allowed to edit
			$minutes = $this->adminOptions['minutes'];
			if (!is_numeric($minutes)) {
				$minutes = $this->minutes;
			}
			if ($minutes < 1) {
				$minutes = $this->minutes;
			}		
			//Insert the random key into the database
			$query = "INSERT INTO " . $wpdb->postmeta .
				"(meta_id, post_id, meta_key, meta_value) " .
				"VALUES ('', " . $comment['comment_post_ID'] . ",'_" . $comment['comment_ID'] . "','" . $rand . "')";
			@$wpdb->query($query);
			
			//Set the cookie
			$cookieName = $this->cookieName . $commentID . $hash;
			$value = $rand;
			$expire = time()+60*$minutes;
			if (!isset($_COOKIE[$cookieName])) {
				setcookie($cookieName, $value, $expire, COOKIEPATH,COOKIE_DOMAIN);
			}
			return;
		}//End function comment_posted
		/* delete_comment */
		function delete_comment($commentID = 0, $postID = 0) {
			if ($this->is_comment_owner($postID)) {
				$status = wp_delete_comment($commentID)? 1 : 'delete_failed';
				return $status;
			} else {
				return 'delete_failed_permission';
			}
		}
		
		//When a comment is edited, an e-mail notification is sent out
		//Parameters - $commentID (a comment ID) and $postID (a post ID)
		//Returns false if e-mail failed
		function edit_notification($commentID = 0, $postID = 0) {
			global $wpdb;
			$options = $this->adminOptions;
			//Check admin options and also if user editing is post author
			if ($options['email_edits'] == "false") { return false; }
			//Get the comment and post
			$comment = get_comment($commentID, ARRAY_A);
			if (empty($comment)) { return false; }
			$query = "SELECT * FROM $wpdb->posts WHERE ID=$postID";
			$post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID=$postID", ARRAY_A);
			
			if (!$post) { return false; }
			if ($this->is_comment_owner($postID)) {  return false; }
			//Make sure the comment is approved and not a trackback/pingback
			if ( $comment['comment_approved'] == '1' && ($comment['comment_type'] != 'pingback' || $comment['comment_type'] != 'trackback')) { 
			//Put together the e-mail message
			$message  = sprintf(__("A comment has been edited on post %s", $this->localizationName) . ": \n%s\n\n", stripslashes($post['post_title']), get_permalink($comment['comment_post_ID']));
			$message .= sprintf(__("Author: %s\n", $this->localizationName), $comment['comment_author']);
			$message .= sprintf(__("Author URL: %s\n", $this->localizationName), stripslashes($comment['comment_author_url']));
			$message .= sprintf(__("Author E-mail: %s\n", $this->localizationName), stripslashes($comment['comment_author_email']));
			$message .= __("Comment:\n", $this->localizationName) . stripslashes($comment['comment_content']) . "\n\n";
			$message .= __("See all comments on this post here:\n", $this->localizationName);
			$message .= get_permalink($comment['comment_post_ID']) . "#comments\n\n";
			$subject = sprintf(__('New Edited Comment On: %s', $this->localizationName), stripslashes($post['post_title']));
			$subject = '[' . get_bloginfo('name') . '] ' . $subject;
			$email = get_bloginfo('admin_email');
			$site_name = str_replace('"', "'", get_bloginfo('name'));
			$charset = get_settings('blog_charset');
			$headers  = "From: \"{$site_name}\" <{$email}>\n";
			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-Type: text/plain; charset=\"{$charset}\"\n";
			//Send the e-mail
			return wp_mail($email, $subject, $message, $headers);
			}
			return false;
		} //End function edit_notification
		/* encode - Encodes comment content to various charsets 
		Parameters - $content - The comment content 
		Returns the encoded content */ 
		function encode($content) {
			if ($this->adminOptions['use_mb_convert'] == "false" || !function_exists("mb_convert_encoding")) { return $content; }
			return mb_convert_encoding($content, ''.get_option('blog_charset').'', mb_detect_encoding($content, "UTF-8, ISO-8859-1, ISO-8859-15", true));
		}
		/* get_comment - Returns a comment ready for editing
		parameters - $commentID
		returns - a response object (WP_Ajax_Response) */
		function get_comment($commentID) {
			$comment = get_comment($commentID);
			$response = new WP_Ajax_Response();
			if (!$comment) { 
				$response->add( array(
					'what' => 'error',
					'id' => $commentID,
					'data' => $this->get_error('get_comment_failed')
				));
				$response->send(); return;
			}
			//Check to see if the comment is spam if the user isn't admin or comment owner
			if (!$this->is_comment_owner($comment->comment_post_ID)) {
				if ($comment->comment_approved === 'spam') { 
					$response->add( array(
					'what' => 'error',
					'id' => $commentID,
					'data' => $this->get_error('comment_spam')
					));
					$response->send(); return;
				}
			}
			
			//Check to see if user can edit and return any appropriate error messages
			$message = $this->can_edit($commentID, $comment->comment_post_ID);
			if (is_string($message)) {
				$response->add( array(
					'what' => 'error',
					'id' => $commentID,
					'data' => $this->get_error($message)
				));
				$response->send(); return;
			}
			//Okay, user can edit - Let's prepare the comment for editing
			$comment->comment_content = format_to_edit( $comment->comment_content ,1);
			$comment->comment_content = apply_filters( 'comment_edit_pre', $comment->comment_content);
			$comment->comment_author = format_to_edit( $comment->comment_author );
			$comment->comment_author_email = format_to_edit( $comment->comment_author_email );
			$comment->comment_author_url = clean_url($comment->comment_author_url);
			$comment->comment_author_url = format_to_edit( $comment->comment_author_url );
			//Prepare the response
			$response->add( array(
					'what' => 'comment_content',
					'id' => $commentID,
					'data' => $comment->comment_content
				));
			$response->add( array(
					'what' => 'comment_author',
					'id' => $commentID,
					'data' => $comment->comment_author
				));
			$response->add( array(
					'what' => 'comment_author_email',
					'id' => $commentID,
					'data' => $comment->comment_author_email
				));
			$response->add( array(
					'what' => 'comment_author_url',
					'id' => $commentID,
					'data' => $comment->comment_author_url
				));
			if ($this->adminOptions['show_gravatar'] == "true") {
				$gravatar = get_avatar($comment, '40');
				if (!empty($gravatar)) {
					$response->add( array(
							'what' => 'gravatar',
							'id' => $commentID,
							'data' => $gravatar
						));
					}
			}
			$response = apply_filters('wp_ajax_comments_get_comment', $response, $comment);
			$response->send();
		}//End function get_comment
		/* get_comment_id - Returns an ID based on an incoming string */
		function get_comment_id($string) {
			preg_match('/([0-9]+)$/i', $string, $matches);
			if (is_numeric($matches[1])) {
				return $matches[1];
			} 
			return 0;
		} 
		/* get_error - Returns an error message based on the passed code
		Parameters - $code (the error code as a string)
		Returns an error message */
		function get_error($code = '') {
			$errorMessage = $this->errors->get_error_message($code);
			if ($errorMessage == null) {
				return __("Unknown error.", $this->localizationName);
			}
			return __($errorMessage, $this->localizationName); 
		}
		/* get_time_left - Returns time remaining in seconds
		parameters - $commentID 
		Returns 1 if no time is necessary.  -1 if time is unavailable.  Time if available.
		*/
		function get_time_left($commentID = 0) {
			global $wpdb;
			$adminMinutes = (int)$this->adminOptions['minutes'];
			$query = "SELECT ($adminMinutes * 60 - (UNIX_TIMESTAMP('" . current_time('mysql') . "') - UNIX_TIMESTAMP(comment_date))) time, comment_author_email, user_id FROM $wpdb->comments where comment_ID = $commentID";
			
			//Get the Timestamp
			$comment = $wpdb->get_row($query, ARRAY_A);
			if (!$comment) { 
				$response->add( array(
					'what' => 'error',
					'id' => $commentID,
					'data' => "-1"
				));
				$response->send();
			}
			if ($this->can_indefinitely_edit($comment['user_id'])) {	
				$response->add( array(
					'what' => 'success',
					'id' => $commentID,
					'data' => "1"
				));			
			}
			//Get the time elapsed since making the comment
			if ((int)$comment['time'] <= 0) { return "-1"; }
			$timeleft = (int)$comment['time'];
			$minutes = floor($timeleft/60);
			$seconds = $timeleft - ($minutes*60);
			$response = new WP_Ajax_Response();
			$response->add( array(
				'what' => 'minutes',
				'id' => $commentID,
				'data' => $minutes
			));
			$response->add( array(
				'what' => 'seconds',
				'id' => $commentID,
				'data' => $seconds
			));
			$response->send();
		}//end function get_time_left
		
		/* increment_edit_count - Increments the number of edits */
		function increment_edit_count() {
			$numEdits = intval($this->adminOptions['number_edits']);
			$numEdits += 1;
			$this->adminOptions['number_edits'] = $numEdits;
			$this->save_admin_options();
		}
		/* Initializes all the error messages */
		function initialize_errors() {
			$this->errors = new WP_Error();
			$this->errors->add('new_comment_posted', __('You cannot edit a comment after other comments have been posted.', $this->localizationName));
			$this->errors->add('comment_time_elapsed', __('The time to edit your comment has elapsed.',$this->localizationName));
			$this->errors->add('comment_edit_denied',__('You do not have permission to edit this comment.',$this->localizationName) );
			$this->errors->add('comment_marked_spam',$this->adminOptions['spam_text']);
			$this->errors->add('comment_spam',__('This comment cannot be edited because it is marked as spam.',$this->localizationName) );
			$this->errors->add('get_comment_failed',__('Comment loading failed.',$this->localizationName) );
			$this->errors->add('no_user_editing',__('Comment editing has been disabled.',$this->localizationName) );
			$this->errors->add('comment_spam_failed', __('Could not mark as spam.',$this->localizationName));
			$this->errors->add('comment_spam_failed_permission', __('You do not have permission to mark this comment as Spam.',$this->localizationName));
			$this->errors->add('delete_failed',__('Could not delete comment.',$this->localizationName) );
			$this->errors->add('delete_failed_permission',__('You do not have permission to delete this comment.',$this->localizationName) );
			$this->errors->add('approve_failed_permission', __('You do not have permission to approve this comment.',$this->localizationName));
			$this->errors->add('approve_failed', __('Could not approve comment.',$this->localizationName));
			$this->errors->add('moderate_failed', __('Could not mark this comment for moderation.',$this->localizationName));
			$this->errors->add('moderate_failed_permission', __('You do not have permission to mark this comment for moderation.',$this->localizationName));
			$this->errors->add('invalid_email', __('Please enter a valid email address.',$this->localizationName));
			$this->errors->add('required_fields', __('Please fill in the required fields (Name, E-mail)',$this->localizationName));
			$this->errors->add('content_empty',__('You cannot have an empty comment.',$this->localizationName) );
			//$this->errors->add('', );
			//$this->errors->add('', );
			//$this->errors->add('', );
		} //end function initialize_errors
		/* init - Run upon WordPress initialization */
		function init() {
			//* Begin Localization Code */
			$wp_ajax_edit_comments_locale = get_locale();
			$wp_ajax_edit_comments_mofile = dirname(__FILE__) . "/languages/" . $this->localizationName . "-". $wp_ajax_edit_comments_locale.".mo";
			load_textdomain($this->localizationName, $wp_ajax_edit_comments_mofile);
		//* End Localization Code */
		}//end function init
		/* is_comment_owner - Checks to see if a user can edit a comment */
		/* Parameters - postID */
		/* Returns - true if user is comment owner, false if not*/
		function is_comment_owner($postID = 0) {
			if (!isset($this->admin)) { $this->admin = false; }
			if ($this->admin) { return true; }
			//Check to see if user is admin of the blog
			if (current_user_can('edit_users')) {
				$this->admin = true;
				return true;
			} elseif( current_user_can( 'edit_page', $postID) || current_user_can( 'edit_post', $postID)) {
				$this->admin = false;
				return true;
			}
			return false;
		}
		/* is_logged_in - Checks to see if the user (non-admin) is logged in 
		Parameters - $userID
		Returns true if logged in, false if not */
		function is_logged_in($userID = 0) {
			if ($this->get_user_id() == $userID) {
				return true;
			} else { 
				return false;
			}
		}	
		/* moderate_comment - Marks a comment as unapproved 
		Parameters - $commentID, $postID
		Returns - 1 if successful, a string error message if not */
		function moderate_comment($commentID=0, $postID = 0) {
			if ($this->is_comment_owner($postID)) {
				$status = wp_set_comment_status($commentID, 'hold')? 1 : 'moderate_failed';
				return $status;
			} else {
				return 'moderate_failed_permission';
			}
		}
		/* save_comment - Saves a new comment
		Parameters - $commentID, $postID, $commentarr (comment array)
		Returns errors or response*/
		function save_comment($commentID, $postID, $commentarr) {
			global $wpdb;
			$response = new WP_Ajax_Response();
			//Make sure the comment has something in it
			if ('' == $commentarr['comment_content'] || $commentarr['comment_content'] == "undefined") {
				$response->add( array(
					'what' => 'error',
					'id' => $commentID,
					'data' => $this->get_error('content_empty')
				));
				$response->send(); return;
			}
			//Check to see if user can edit
			$message = $this->can_edit($commentID, $postID);
			if (is_string($message)) {
				$response->add( array(
					'what' => 'error',
					'id' => $commentID,
					'data' => $this->get_error($message)
				));
				$response->send(); return;
			}
			
			//Sanity checks
			if (!$this->is_comment_owner($postID)) {
				//Make sure required fields are filled out
				if ( get_option('require_name_email') && ((6 > strlen($commentarr['comment_author_email']) && $this->can_edit_email($commentID, $postID)) || ('' == $commentarr['comment_author'] && $this->can_edit_name($commentID, $postID)))) {
					$response->add( array(
						'what' => 'error',
						'id' => $commentID,
						'data' => $this->get_error('required_fields')
					));
					$response->send(); return;
				}
			}// end comment_owner check
			//Make sure the e-mail is valid - Skip if pingback or trackback
			if (!($this->admin  && empty($commentarr['comment_author_email']))) {
				if (!is_email($commentarr['comment_author_email']) && $commentarr['comment_type'] != "pingback" && $commentarr['comment_type'] != "trackback") {
					if ($this->can_edit_email($commentID, $postID)) {
						$response->add( array(
							'what' => 'error',
							'id' => $commentID,
							'data' => $this->get_error('invalid_email')
						));
						$response->send(); return;
					}
				}
			}
			if (strtolower(get_option('blog_charset')) != 'utf-8') { @$wpdb->query("SET names 'utf8'");} //comment out if getting char errors
			
			//Save the comment
			$commentarr['comment_ID'] = (int)$commentID;
			wp_update_comment($commentarr);
			
			//For security, get the new comment
			$comment = get_comment($commentID, ARRAY_A);
			//Check for spam
			if (!$this->is_comment_owner($postID)) {
				if($this->check_spam($commentID, $postID)) {
					$response->add( array(
						'what' => 'error',
						'id' => $commentID,
						'data' => $this->get_error('comment_marked_spam')
					));
					$response->send(); return;
				};
			}
			//Do actions after a comment has successfully been edited
			do_action_ref_array('wp_ajax_comments_comment_edited', array(&$commentID, &$postID));
			//Condition the data for returning
			do_action('wp_ajax_comments_remove_content_filter');
			$response->add( array(
					'what' => 'comment_content',
					'id' => $commentID,
					'data' => stripslashes(apply_filters('comment_text',apply_filters('get_comment_text',$this->encode($comment['comment_content']))))
			));
			$response->add( array(
					'what' => 'comment_author',
					'id' => $commentID,
					'data' => stripslashes(apply_filters('comment_author', apply_filters('get_comment_author', $this->encode($comment['comment_author']))))
			));
			$response->add( array(
					'what' => 'comment_author_url',
					'id' => $commentID,
					'data' => stripslashes(apply_filters('comment_url', apply_filters('get_comment_author_url', $comment['comment_author_url'])))
			));
			return $response;
		}
		/* spam_comment - Marks a comment as spam or de-spams a comment
		Parameters - $commentID, $postID
		Returns - 1 if successful, a string error message if not */
		function spam_comment($commentID = 0, $postID = 0) {
			if ($this->is_comment_owner($postID)) {
				$status = wp_set_comment_status($commentID, 'spam')? 1 : 'comment_spam_failed';
				return $status;
			} else {
				return 'comment_spam_failed_permission';
			}
		}
		/*BEGIN UTILITY FUNCTIONS - Grouped by function and not by name */
		function add_admin_pages(){
				add_options_page('Ajax Edit Comments', 'Ajax Edit Comments', 9, basename(__FILE__), array(&$this, 'print_admin_page'));
		}
		//Removes the div and various links from a comment 
		function comment_filter() {
			$this->skip = true;
		}
		//Provides the interface for the admin pages
		function print_admin_page() {
			include dirname(__FILE__) . '/php/admin-panel.php';
		}
		//Returns an array of admin options
		function get_admin_options() {
			//todo - possibly an array_merge here
			if (empty($this->adminOptions)) {
				$adminOptions = array(
					'allow_editing' => 'true',
					'allow_editing_after_comment' => 'true',
					'minutes' => '5', 
					'edit_text' => '', 
					'show_timer' => 'true',
					'spam_text' => __('Your edited comment was marked as spam.  If this is in error, please contact the admin.', $this->localizationName),
					'email_edits' => 'false',
					'number_edits' => '5',
					'spam_protection' => 'akismet',
					'registered_users_edit' => 'false',
					'registered_users_name_edit' => 'true',
					'registered_users_email_edit' => 'false',
					'registered_users_url_edit' => 'true',
					'use_mb_convert' => 'true',
					'allow_name_editing' => 'true',
					'allow_email_editing' => 'false',
					'allow_url_editing' => 'true',
					'allow_css' => 'true',
					'allow_css_editor' => 'true',
					'show_gravatar' => 'true',
					'allow_icons' => 'true',
					'show_options' => 'true',
					'clear_after' => 'true',
					'editor_title' => 'WP Ajax Edit Comments',
					'editor_url' => 'http://wordpress.org/extend/plugins/wp-ajax-edit-comments/',
					'javascript_scrolling' => 'true',
					'post_style_url' => '',
					'editor_style_url' => ''
				);
				$options = get_option($this->adminOptionsName);
				if (!empty($options)) {
					foreach ($options as $key => $option)
						$adminOptions[$key] = $option;
				}
				$this->adminOptions = $adminOptions;
				$this->save_admin_options();								
			}
			return $this->adminOptions;
		}
		//Returns an array of "all" user options
		function get_all_user_options() {
			if (!function_exists("get_currentuserinfo")) { return; }
			if (empty($this->userOptions)) {
				$user_email = $this->get_user_email(); 
				$defaults = array(
				'comment_editing' => 'true', 
				'admin_editing' => 'false'
				);
				$this->userOptions = get_option($this->userOptionsName);
				if (!isset($this->userOptions)) {
					$this->userOptions = array();
				}
				//See if an older version doesn't match the new defaults
				if (empty($this->userOptions[$user_email])) {
					$this->userOptions[$user_email] = $defaults;
				}	elseif(!is_array($this->userOptions[$user_email])) {
					$this->userOptions[$user_email] = $defaults;
				} else {
						foreach ($this->userOptions[$user_email] as $key => $option) {
							$defaults[$key] = $option;								
						}
						$this->userOptions[$user_email] = $defaults;
				}
				$this->save_admin_options();
			}
			return $this->userOptions;
		}
		//Returns a logged-in user's e-mail address
		function get_user_email() {
			global $user_email;
			if (!function_exists("get_currentuserinfo")) { return ''; }
			if (empty($user_email)) {get_currentuserinfo();} //try to get user info
			if (empty($user_email)) { return '0'; } //Can't get user info, so return empty string
			return $user_email;
		}
		// Returns a logged-in user's ID
		function get_user_id() {
			global $user_ID;
			if (!function_exists("get_currentuserinfo")) { return "-1"; }
			if (empty($user_ID)) {get_currentuserinfo();} //try to get user info
			if (empty($user_ID)) { return '-1'; } //Can't get user info, so return empty string
			return $user_ID;
		}
		
		//Returns an array of an individual's options
		function get_user_options() {
			if (empty($this->userOptions)) { $this->userOptions = $this->get_all_user_options(); }
			return $this->userOptions[$this->get_user_email()];
		}
		//Saves Ajax Edit Comments settings for admin and admin users
		function save_admin_options(){
			if (!empty($this->adminOptions)) {
				update_option($this->adminOptionsName, $this->adminOptions);
			}
			if (!empty($this->userOptions)) {
				update_option($this->userOptionsName, $this->userOptions);
			}
		}
		//Checks for script addition on single or page posts
		function add_post_scripts() {
			if (is_single() || is_page()) {
				$this->add_scripts();
			} elseif (is_admin()) {
				$this->add_admin_scripts();
			}
		}
		/* Private - Adds JavaScript in the admin panel if admin has enabled the option */
		function add_admin_scripts() {
			//Page detection for other plugin authors
			switch (basename($_SERVER['SCRIPT_FILENAME'])) {
				case "edit-comments.php";
				break;
				case "edit.php";
				if (isset($_GET['p'])) { break; }
				default:
				return;
			}
			$options = $this->get_user_options();
			if ($options['admin_editing'] == 'true') { return; }
			$this->add_scripts();
		}
		//Adds the appropriate scripts to WordPress
		function add_scripts(){
			global $wp_scripts;
			//Check to make sure other thickbox scripts haven't been added
			wp_enqueue_script("jquery");
			wp_enqueue_script("wp-ajax-response");
			if ($wp_scripts->query('ngg-thickbox', 'queue')) { //For Next Gen Gallery compatability
				$wp_scripts->remove(array('ngg-thickbox'));
			} 
			wp_enqueue_script('thickbox');
			wp_enqueue_script('wp_ajax_edit_comments_script', get_bloginfo('wpurl') . '/wp-content/plugins/wp-ajax-edit-comments/js/wp-ajax-edit-comments.js.php', array("jquery", "wp-ajax-response", "thickbox") , 2.0); 
		}
		
		/**
		* Adds a link to the stylesheet to the header
		*/
		function add_css(){
				if (is_single() || is_page() || is_admin()) { 
					if (empty($this->adminOptions['post_style_url'])) {
							//For RTL readers
							$locale = get_locale();
							if ($locale == "ar") {
								echo '<link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-content/plugins/wp-ajax-edit-comments/css/edit-comments-rtl.css" type="text/css" media="screen"  />'; 
							} else {
								echo '<link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-content/plugins/wp-ajax-edit-comments/css/edit-comments.css" type="text/css" media="screen"  />'; 
							}
						/* From http://blue-anvil.com/archives/experiments-with-floats-whats-the-best-method-of-clearance */
							if ($this->adminOptions['clear_after'] == "true") { 
	?>
<!-- Ajax Edit Comments -->
<!--[if IE]>
<style>
  .clearfix {display: inline-block;}
  /* Hides from IE-mac \*/
  * html .clearfix {height: 1%;}
  .clearfix {display: block;}
  /* End hide from IE-mac */
</style>
<![endif]-->
<?php
							} /* clear after */
					} /* post_style_url */ else {
						echo '<link rel="stylesheet" href="'.get_bloginfo('template_directory').$this->adminOptions['post_style_url'].'" type="text/css" media="screen"  />';
					}
			echo '<link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-includes/js/thickbox/thickbox.css" type="text/css" media="all"  />'; 
			}
		}
		//Returns a random security key
		function random() {
		 $chars = "%CDEF#cGHIJ\:ab!@defg9ABhijklmn<>;opqrstuvwxyz10234/+_-=5678MKL^&*NOP";
		 $pass = '';
		 for ($i = 0; $i < 50; $i++) {
			$pass .= $chars{rand(0, strlen($chars)-1)};
		 }
		 return $pass;
		}
		/*END UTILITY FUNCTIONS*/
    }
}
//instantiate the class
if (class_exists('WPrapAjaxEditComments')) {
	if (get_bloginfo('version') >= "2.5") {
		$WPrapAjaxEditComments = new WPrapAjaxEditComments();
	}
}




?>