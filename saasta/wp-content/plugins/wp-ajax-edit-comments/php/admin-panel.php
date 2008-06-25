<?php 
/* Admin Panel Code - Created on April 19, 2008 by Ronald Huereca 
Last modified on June 09, 2008
*/
if (empty($this->adminOptionsName)) { die(''); }

global $wpdb,$user_email;
$WPAjaxEditComments = $this->get_admin_options(); //global settings
$author_options = $this->get_user_options(); //user settings
//Check to see if a user can access the panel
if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die("nope");
//Delete security keys 
if (isset($_POST['wpAJAXSecurityKeys'])) {
	if ($_POST['wpAJAXSecurityKeys'] == "true") {
		check_admin_referer('wp-ajax-edit-comments_admin-options');
		$query = "delete from $wpdb->postmeta where left(meta_value, 6) = 'wpAjax'";
		@$wpdb->query($query);
		$query = "delete from $wpdb->posts where post_type = 'ajax_edit_comments'";
		@$wpdb->query($query); 
		?>
			<div class="updated"><p><strong><?php _e('Security keys deleted', $this->localizationName) ?></strong></p></div>
		<?php
	}
}
//Update settings
if (isset($_POST['update_wp_ajaxEditCommentSettings'])) { 
	 check_admin_referer('wp-ajax-edit-comments_admin-options');
	$error = false;
	$updated = false;
	//Validate the comment time entered
	if (isset($_POST['wpAJAXCommentTime'])) {
		$commentTimeErrorMessage = '';
		$commentClass = 'error';
		if (!preg_match('/^\d+$/i', $_POST['wpAJAXCommentTime'])) {
			$commentTimeErrorMessage = __("Comment time must be a numerical value",$this->localizationName);
			$error = true;
		}	elseif($_POST['wpAJAXCommentTime'] < 1) {
			$commentTimeErrorMessage = __("Comment time must be greater than one minute.",$this->localizationName);
			$error = true;
		} else {
			$WPAjaxEditComments['minutes'] = $_POST['wpAJAXCommentTime'];
			$updated = true;
		}
		if (!empty($commentTimeErrorMessage)) {
		?>
<div class="<?php echo $commentClass;?>"><p><strong><?php _e($commentTimeErrorMessage, $this->localizationName);?></p></strong></div>
		<?php
		}
	}
		//Update global settings
		$WPAjaxEditComments['allow_editing'] = $_POST['wpAJAXCommentAllowEdit'];
		$WPAjaxEditComments['allow_editing_after_comment'] = $_POST['wpAJAXEditAfterComment'];
		$WPAjaxEditComments['spam_text'] = apply_filters('pre_comment_content',apply_filters('comment_save_pre', $_POST['wpAJAXSpamText']));
		$WPAjaxEditComments['show_timer'] = $_POST['wpAJAXShowTimer'];
		$WPAjaxEditComments['email_edits'] = $_POST['wpAJAXEmailEdits'];
		$WPAjaxEditComments['spam_protection'] = $_POST['wpAJAXSpam'];
		$WPAjaxEditComments['use_mb_convert'] = $_POST['wpAJAXmbConvert'];
		$WPAjaxEditComments['registered_users_edit'] = $_POST['wpAJAXregisterEdit'];
		$WPAjaxEditComments['registered_users_name_edit'] = $_POST['wpAJAXregisterEditName'];
		$WPAjaxEditComments['registered_users_url_edit'] = $_POST['wpAJAXregisterEditURL'];
		$WPAjaxEditComments['registered_users_email_edit'] = $_POST['wpAJAXregisterEditEmail'];
		$WPAjaxEditComments['allow_email_editing'] = $_POST['wpAJAXCommentAllowEmailEdit'];
		$WPAjaxEditComments['allow_url_editing'] = $_POST['wpAJAXCommentAllowURLEdit'];
		$WPAjaxEditComments['allow_name_editing'] = $_POST['wpAJAXCommentAllowNameEdit'];
		$WPAjaxEditComments['show_gravatar'] = $_POST['wpAJAXCommentAllowGravatar'];
		$WPAjaxEditComments['allow_icons'] = $_POST['wpAJAXCommentAllowIcons'];
		$WPAjaxEditComments['show_options'] = $_POST['wpAJAXCommentShowOptions'];
		$WPAjaxEditComments['clear_after'] = $_POST['wpAJAXCommentAllowClear'];
		$WPAjaxEditComments['editor_title'] = stripslashes_deep(trim($_POST['wpAJAXCommentEditorTitle']));
		$WPAjaxEditComments['editor_url'] = stripslashes_deep(trim($_POST['wpAJAXCommentEditorURL']));
		$WPAjaxEditComments['javascript_scrolling'] = $_POST['wpAJAXCommentAllowScrolling'];
		$WPAjaxEditComments['post_style_url'] = stripslashes_deep(trim($_POST['wpAJAXCommentPostCSS']));
		$WPAjaxEditComments['editor_style_url'] = stripslashes_deep(trim($_POST['wpAJAXCommentEditorCSS']));
		
		//Update user setings
		$author_options['author_editing'] = $_POST['wpAJAXAuthor'];
		$author_options['comment_editing'] = $_POST['wpAJAXComment'];
		$author_options['admin_editing'] = $_POST['wpAJAXAdminEdits'];
		$author_options['inline_editing'] = $_POST['wpAJAXInlineEdits'];
		$author_options['show_links'] = $_POST['wpAJAXLinks'];
		$updated = true;
	}
	if ($updated && !$error) {
		$this->adminOptions = $WPAjaxEditComments;
		$this->userOptions[$this->get_user_email()] = $author_options;
		$this->save_admin_options();
	?>
<div class="updated"><p><strong><?php _e('Settings successfully updated.', $this->localizationName) ?></strong></p></div>
<?php
}
?>
<div class="wrap">
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<?php wp_nonce_field('wp-ajax-edit-comments_admin-options') ?>
<h2>Ajax Edit Comments</h2>
<p><?php _e("Your commentators have edited their comments ", $this->localizationName) ?><?php echo number_format(intval($WPAjaxEditComments['number_edits'])); ?> <?php _e("times", $this->localizationName) ?> - <?php _e('Please',$this->localizationName ); ?> <a href='https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ronalfy%40gmail%2ecom&item_name=Ajax%20Edit%20Comments%202%2ex&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8'><?php _e('Donate',$this->localizationName); ?></a></p>
<h3><?php _e('Global Options - These Options Affect Everyone', $this->localizationName);?></h3>
<table class="form-table">
	<tbody>
  	<tr valign="top">
      <th scope="row"><?php _e('Set comment time (minutes):', $this->localizationName) ?></th>
      <td><input type="text" name="wpAJAXCommentTime" value="<?php echo $WPAjaxEditComments['minutes'] ?>" id="comment_time"/></td>
    </tr>
  <tr valign="top">
  	<th scope="row"><?php _e('Spam notification text.', $this->localizationName) ?></th>
    <td>
    <p><?php _e('Please limit to one line if possible since this text will show up when editing the comment or author (Tags allowed: em, a, strong, blockquote):', $this->localizationName) ?></p>
    <p><textarea cols="100" rows="3" name="wpAJAXSpamText" id="spam_text"><?php _e(stripslashes(apply_filters('comment_edit_save', $WPAjaxEditComments['spam_text'])), $this->localizationName)?></textarea></p>
    </td>
  </tr>
  <tr valign="top">
  	<th scope="row"><?php _e('Anonymous Users', $this->localizationName) ?></th>
    <td>
    <p><strong><?php _e('Allow Anyone to Edit Their Own Comments?', $this->localizationName);?></strong></p><p><?php _e('Selecting "No" will turn off comment editing for everyone except admin types who have post and page editing permissions.', $this->localizationName) ?></p>
    <p><label for="wpAJAXCommentAllowEdit_yes"><input type="radio" id="wpAJAXCommentAllowEdit_yes" name="wpAJAXCommentAllowEdit" value="true" <?php if ($WPAjaxEditComments['allow_editing'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXCommentAllowEdit_no"><input type="radio" id="wpAJAXCommentAllowEdit_no" name="wpAJAXCommentAllowEdit" value="false" <?php if ($WPAjaxEditComments['allow_editing'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
    <p><strong><?php _e('Allow editing after additional comments have been posted?', $this->localizationName);?></strong></p><p><?php _e('Selecting "No" will prevent users from editing their comments if another comment has been made on a post.', $this->localizationName) ?></p>
    <p><label for="wpAJAXEditAfterComment_yes"><input type="radio" id="wpAJAXEditAfterComment_yes" name="wpAJAXEditAfterComment" value="true" <?php if ($WPAjaxEditComments['allow_editing_after_comment'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXEditAfterComment_no"><input type="radio" id="wpAJAXEditAfterComment_no" name="wpAJAXEditAfterComment" value="false" <?php if ($WPAjaxEditComments['allow_editing_after_comment'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
     <p><strong><?php _e('Allow Users to Edit Their Name?', $this->localizationName);?></strong></p><p><?php _e('Selecting "No" will turn off editing of Names', $this->localizationName) ?></p>
    <p><label for="wpAJAXCommentAllowNameEdit_yes"><input type="radio" id="wpAJAXCommentAllowNameEdit_yes" name="wpAJAXCommentAllowNameEdit" value="true" <?php if ($WPAjaxEditComments['allow_name_editing'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXCommentAllowNameEdit_no"><input type="radio" id="wpAJAXCommentAllowNameEdit_no" name="wpAJAXCommentAllowNameEdit" value="false" <?php if ($WPAjaxEditComments['allow_name_editing'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
     <p><strong><?php _e('Allow Users to Edit Their E-mail Addresses?', $this->localizationName);?></strong></p><p><?php _e('Selecting "No" will turn off editing of e-mail addresses.  One of the reasons you may want this on is for users with Avatars.', $this->localizationName) ?></p>
    <p><label for="wpAJAXCommentAllowEmailEdit_yes"><input type="radio" id="wpAJAXCommentAllowEmailEdit_yes" name="wpAJAXCommentAllowEmailEdit" value="true" <?php if ($WPAjaxEditComments['allow_email_editing'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXCommentAllowEmailEdit_no"><input type="radio" id="wpAJAXCommentAllowEmailEdit_no" name="wpAJAXCommentAllowEmailEdit" value="false" <?php if ($WPAjaxEditComments['allow_email_editing'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
     <p><strong><?php _e('Allow Users to Edit Their URLs?', $this->localizationName);?></strong></p><p><?php _e('Selecting "No" will turn off editing of URLs', $this->localizationName) ?></p>
    <p><label for="wpAJAXCommentAllowURLEdit_yes"><input type="radio" id="wpAJAXCommentAllowURLEdit_yes" name="wpAJAXCommentAllowURLEdit" value="true" <?php if ($WPAjaxEditComments['allow_url_editing'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXCommentAllowURLEdit_no"><input type="radio" id="wpAJAXCommentAllowURLEdit_no" name="wpAJAXCommentAllowURLEdit" value="false" <?php if ($WPAjaxEditComments['allow_url_editing'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
    
    
    </td>
  </tr>
  <tr valign="top">
  	<th scope="row"><?php _e('Registered Users', $this->localizationName);?></th>
    <td><p><strong><?php _e('Allow Registered Users to Edit Comments Indefinitely?', $this->localizationName); ?></strong></p>
    		<p><?php _e('Selecting "Yes" will allow users registered on your blog to edit comments without a time limit.', $this->localizationName);?></p>
        <p><label for="wpAJAXregisterEdit_yes"><input type="radio" id="wpAJAXregisterEdit_yes" name="wpAJAXregisterEdit" value="true" <?php if ($WPAjaxEditComments['registered_users_edit'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXregisterEdit_no"><input type="radio" id="wpAJAXregisterEdit_no" name="wpAJAXregisterEdit" value="false" <?php if ($WPAjaxEditComments['registered_users_edit'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
    <p><strong><?php _e('Allow Registered Users to Edit Their Name?', $this->localizationName); ?></strong></p>
    		<p><?php _e('Selecting "Yes" will allow users registered on your blog to edit their names.  This can prevent issues if a user wishes to impersonate others.', $this->localizationName);?></p>
        <p><label for="wpAJAXregisterEditName_yes"><input type="radio" id="wpAJAXregisterEditName_yes" name="wpAJAXregisterEditName" value="true" <?php if ($WPAjaxEditComments['registered_users_name_edit'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXregisterEditName_no"><input type="radio" id="wpAJAXregisterEditName_no" name="wpAJAXregisterEditName" value="false" <?php if ($WPAjaxEditComments['registered_users_name_edit'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
<p><strong><?php _e('Allow Registered Users to Edit Their E-mail Address?', $this->localizationName); ?></strong></p>
    		<p><?php _e('Selecting "Yes" will allow users registered on your blog to edit their e-mail address.', $this->localizationName);?></p>
        <p><label for="wpAJAXregisterEditEmail_yes"><input type="radio" id="wpAJAXregisterEditEmail_yes" name="wpAJAXregisterEditEmail" value="true" <?php if ($WPAjaxEditComments['registered_users_email_edit'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXregisterEditEmail_no"><input type="radio" id="wpAJAXregisterEditEmail_no" name="wpAJAXregisterEditEmail" value="false" <?php if ($WPAjaxEditComments['registered_users_email_edit'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
 <p><strong><?php _e('Allow Registered Users to Edit Their URL?', $this->localizationName); ?></strong></p>
    		<p><?php _e('Selecting "Yes" will allow users registered on your blog to edit their URL.', $this->localizationName);?></p>
        <p><label for="wpAJAXregisterEditURL_yes"><input type="radio" id="wpAJAXregisterEditURL_yes" name="wpAJAXregisterEditURL" value="true" <?php if ($WPAjaxEditComments['registered_users_url_edit'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXregisterEditURL_no"><input type="radio" id="wpAJAXregisterEditURL_no" name="wpAJAXregisterEditURL" value="false" <?php if ($WPAjaxEditComments['registered_users_url_edit'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
    </td>
	</tr>  
  <tr valign="top">
  	<th scope="row"><?php _e('Countdown Timer', $this->localizationName) ?></th>
    <td>
    <p><strong><?php _e('Show a Countdown Timer?', $this->localizationName) ?></strong></p><p><?php _e('Selecting "No" will turn off the countdown timer for non-admin commentators.', $this->localizationName) ?></p>
    <p><label for="wpAJAXShowTimer_yes"><input type="radio" id="wpAJAXShowTimer_yes" name="wpAJAXShowTimer" value="true" <?php if ($WPAjaxEditComments['show_timer'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXShowTimer_no"><input type="radio" id="wpAJAXShowTimer_no" name="wpAJAXShowTimer" value="false" <?php if ($WPAjaxEditComments['show_timer'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
    </td>
  </tr>
  <tr valign="top">
  	<th scope="row"><?php _e('Edit E-mails', $this->localizationName) ?></th>
    <td>
    <p><strong><?php _e('Allow Edit E-mails?', $this->localizationName) ?></strong></p><p>  <?php _e('Selecting "Yes" will send you an email each time someone edits their comment.', $this->localizationName) ?></p>
    <p><label for="wpAJAXEmailEdits_yes"><input type="radio" id="wpAJAXEmailEdits_yes" name="wpAJAXEmailEdits" value="true" <?php if ($WPAjaxEditComments['email_edits'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXEmailEdits_no"><input type="radio" id="wpAJAXEmailEdits_no" name="wpAJAXEmailEdits" value="false" <?php if ($WPAjaxEditComments['email_edits'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
    </td>
  </tr>
  <tr valign="top">
  	<th scope="row"><?php _e('Spam Protection',$this->localizationName); ?></th>
    <td>
    <p><label for="wpAJAXAkismet"><input type="radio" id="wpAJAXAkismet" name="wpAJAXSpam" value="akismet" <?php if ($WPAjaxEditComments['spam_protection'] == "akismet") { echo('checked="checked"'); }?> /> <?php _e('Akismet',$this->localizationName); ?></label><br /><label for="wpAJAXDefensio"><input type="radio" id="wpAJAXDefensio" name="wpAJAXSpam" value="defensio" <?php if ($WPAjaxEditComments['spam_protection'] == "defensio") { echo('checked="checked"'); }?>/> <?php _e('Defensio',$this->localizationName); ?></label><br /><label for="wpAJAXNoSpam"><input type="radio" id="wpAJAXNoSpam" name="wpAJAXSpam" value="none" <?php if ($WPAjaxEditComments['spam_protection'] == "none") { echo('checked="checked"'); }?>/> <?php _e('None',$this->localizationName); ?></label></p>
    </td>
  </tr>
  <tr valign="top">
    <th scope="row"><?php _e('Styles',$this->localizationName); ?></th>
    <td>
    <p><?php _e('Tweaking the style options is recommended for advanced users only.  If you choose to disable styles, you will have to provide your own styles in your default WordPress stylesheet.', $this->localizationName) ?></p>
    <p><strong><?php _e('Turn Off Icons?', $this->localizationName) ?></strong></p>
    <p><?php _e('Selecting "Yes" will prevent the plugin icons from showing.', $this->localizationName) ?></p>
<p><label for="wpAJAXCommentAllowIcons_yes"><input type="radio" id="wpAJAXCommentAllowIcons_yes" name="wpAJAXCommentAllowIcons" value="false" <?php if ($WPAjaxEditComments['allow_icons'] == "false") { echo('checked="checked"'); }?> /> <?php _e('Yes', $this->localizationName) ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXCommentAllowIcons_no"><input type="radio" id="wpAJAXCommentAllowIcons_no" name="wpAJAXCommentAllowIcons" value="true" <?php if ($WPAjaxEditComments['allow_icons'] == "true") { echo('checked="checked"'); }?>/> <?php _e('No', $this->localizationName) ?></label></p>
<p><strong><?php _e('Turn Off clearfix:after?', $this->localizationName) ?></strong></p>
    <p><?php _e('The clearfix is enabled by default for maximum compatibility with themes.', $this->localizationName) ?></p>
<p><label for="wpAJAXCommentAllowClear_yes"><input type="radio" id="wpAJAXCommentAllowClear_yes" name="wpAJAXCommentAllowClear" value="false" <?php if ($WPAjaxEditComments['clear_after'] == "false") { echo('checked="checked"'); }?> /> <?php _e('Yes', $this->localizationName) ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXCommentAllowClear_no"><input type="radio" id="wpAJAXCommentAllowClear_no" name="wpAJAXCommentAllowClear" value="true" <?php if ($WPAjaxEditComments['clear_after'] == "true") { echo('checked="checked"'); }?>/> <?php _e('No', $this->localizationName) ?></label></p>
<p><strong><?php _e('Turn Off Admin JavaScript Scrolling?', $this->localizationName) ?></strong></p>
    <p><?php _e('The plugin tries to correct incorrect offsets on a post if you are admin.', $this->localizationName) ?></p>
<p><label for="wpAJAXCommentAllowScrolling_yes"><input type="radio" id="wpAJAXCommentAllowScrolling_yes" name="wpAJAXCommentAllowScrolling" value="false" <?php if ($WPAjaxEditComments['javascript_scrolling'] == "false") { echo('checked="checked"'); }?> /> <?php _e('Yes', $this->localizationName) ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXCommentAllowScrolling_no"><input type="radio" id="wpAJAXCommentAllowScrolling_no" name="wpAJAXCommentAllowScrolling" value="true" <?php if ($WPAjaxEditComments['javascript_scrolling'] == "true") { echo('checked="checked"'); }?>/> <?php _e('No', $this->localizationName) ?></label></p>
<p><?php _e("Leaving a style empty will revert back to the default plugin styles.", $this->localizationName) ?></p>
<p><?php _e("The URL should be relative to: ", $this->localizationName); bloginfo('template_directory'); ?></p>
		<p><strong><?php _e('Post/Admin CSS URL', $this->localizationName) ?></strong></p>
<p><input id="wpAJAXCommentPostCSS" type="text" size="40" value="<?php echo attribute_escape($WPAjaxEditComments['post_style_url']);?>" name="wpAJAXCommentPostCSS" /></p>
    <p><strong><?php _e('Editor CSS URL', $this->localizationName) ?></strong></p>
    <p><input id="wpAJAXCommentEditorCSS" type="text" size="40" value="<?php echo attribute_escape($WPAjaxEditComments['editor_style_url']);?>" name="wpAJAXCommentEditorCSS" /></p>
    </td>
  </tr>
  <tr valign="top">
    <th scope="row"><?php _e('Gravatars',$this->localizationName); ?></th>
    <td>
    <p><?php _e('Turning off Gravatars may solve some plugin compatibility issues.', $this->localizationName) ?></p>
    <p><strong><?php _e('Turn Off Gravatars?', $this->localizationName) ?></strong></p>
<p><label for="wpAJAXCommentAllowGravatar_no"><input type="radio" id="wpAJAXCommentAllowGravatar_no" name="wpAJAXCommentAllowGravatar" value="false" <?php if ($WPAjaxEditComments['show_gravatar'] == "false") { echo('checked="checked"'); }?> /> <?php _e('Yes', $this->localizationName) ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXCommentAllowGravatar_yes"><input type="radio" id="wpAJAXCommentAllowGravatar_yes" name="wpAJAXCommentAllowGravatar" value="true" <?php if ($WPAjaxEditComments['show_gravatar'] == "true") { echo('checked="checked"'); }?>/> <?php _e('No', $this->localizationName) ?></label></p>
    </td>
  </tr>
  <tr valign="top">
    <th scope="row"><?php _e('Editor Options',$this->localizationName); ?></th>
    <td>
    <p><?php _e('The following affects the editing interface.', $this->localizationName) ?></p>
    <p><strong><?php _e('Turn Off More Options?', $this->localizationName) ?></strong></p>
<p><label for="wpAJAXCommentShowOptions_no"><input type="radio" id="wpAJAXCommentShowOptions_no" name="wpAJAXCommentShowOptions" value="false" <?php if ($WPAjaxEditComments['show_options'] == "false") { echo('checked="checked"'); }?> /> <?php _e('Yes', $this->localizationName) ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXCommentShowOptions_yes"><input type="radio" id="wpAJAXCommentShowOptions_yes" name="wpAJAXCommentShowOptions" value="true" <?php if ($WPAjaxEditComments['show_options'] == "true") { echo('checked="checked"'); }?>/> <?php _e('No', $this->localizationName) ?></label></p>
    <p><?php _e("Please leave as is if you wish to retain proper plugin credit.", $this->localizationName) ?></p>
		<p><strong><?php _e('Editor Title', $this->localizationName) ?></strong></p>
<p><input id="wpAJAXCommentEditorTitle" type="text" size="40" value="<?php echo attribute_escape($WPAjaxEditComments['editor_title']);?>" name="wpAJAXCommentEditorTitle" /></p>
    <p><strong><?php _e('Title URL', $this->localizationName) ?></strong></p>
    <p><input id="wpAJAXCommentEditorURL" type="text" size="40" value="<?php echo attribute_escape($WPAjaxEditComments['editor_url']);?>" name="wpAJAXCommentEditorURL" /></p>
    </td>
  </tr>
  <tr valign="top">
  	<th scope="row"><?php _e('Character Encoding',$this->localizationName) ?></th>
    <td>
    <p><strong><?php _e('Enable mb_convert_encoding?', $this->localizationName) ?></strong></p>
    <p><?php _e('Some servers do not have this installed.  If you disable this option, be sure to test out various characters.  The mb_convert_encoding function is necessary to convert from UTF-8 to various charsets.', $this->localizationName) ?></p>
    <p><label for="wpAJAXmbConvert_yes"><input type="radio" id="wpAJAXmbConvert_yes" name="wpAJAXmbConvert" value="true" <?php if ($WPAjaxEditComments['use_mb_convert'] == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes',$this->localizationName); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXmbConvert_no"><input type="radio" id="wpAJAXmbConvert_no" name="wpAJAXmbConvert" value="false" <?php if ($WPAjaxEditComments['use_mb_convert'] == "false") { echo('checked="checked"'); }?>/> <?php _e('No',$this->localizationName); ?></label></p>
    </td>
  </tr>
 </tbody>
</table>
<?php
		$comment = $author_options['comment_editing'];
		$adminEdits = $author_options['admin_editing'];
?>
<div class="submit">
  <input type="submit" name="update_wp_ajaxEditCommentSettings" value="<?php _e('Update Settings', $this->localizationName) ?>" />
</div>
<h3><?php _e('Individual Options - These Options Only Affect You', $this->localizationName);?></h3>
<table class="form-table">
	<tbody>
  <tr valign="top">
  	<th scope="row"><?php _e('Admin Panel Editing', $this->localizationName) ?></th>
    <td>
    <p><strong><?php _e('Turn Off Comment Editing in Admin Panel?', $this->localizationName) ?></strong></p>
<p><?php _e('Selecting "Yes" will disable comment editing in the Admin Comments Panel.', $this->localizationName) ?></p>
<p><label for="wpAJAXAdminEdits_yes"><input type="radio" id="wpAJAXAdminEdits_yes" name="wpAJAXAdminEdits" value="true" <?php if ($adminEdits == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes', $this->localizationName) ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXAdminEdits_no"><input type="radio" id="wpAJAXAdminEdits_no" name="wpAJAXAdminEdits" value="false" <?php if ($adminEdits == "false") { echo('checked="checked"'); }?>/> <?php _e('No', $this->localizationName) ?></label></p>
    </td>
  </tr>
  <tr valign="top">
  	<th scope="row"><?php _e('Post Editing', $this->localizationName) ?></th>
    <td>
    <p><strong><?php _e('Turn On Comment Editing?', $this->localizationName) ?></strong></p>
    <p><?php _e('Selecting "Yes" will enable your ability to edit a user\'s comment.  Selecting "No" will disable your ability to edit comments on a post', $this->localizationName) ?></p>
<p><label for="wpAJAXComment_yes"><input type="radio" id="wpAJAXComment_yes" name="wpAJAXComment" value="true" <?php if ($comment == "true") { echo('checked="checked"'); }?> /> <?php _e('Yes', $this->localizationName) ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXComment_no"><input type="radio" id="wpAJAXComment_no" name="wpAJAXComment" value="false" <?php if ($comment == "false") { echo('checked="checked"'); }?>/> <?php _e('No', $this->localizationName) ?></label></p>
    </td>
  </tr>
  </tbody>
</table>
<div class="submit">
  <input type="submit" name="update_wp_ajaxEditCommentSettings" value="<?php _e('Update Settings', $this->localizationName) ?>" />
</div>
<h3><?php _e('Ajax Edit Comments Cleanup', $this->localizationName);?></h3>
<table class="form-table">
	<tbody>
  <tr valign="top">
  	<th scope="row"><?php _e('Delete all security keys', $this->localizationName) ?></th>
    <td>
    <p><?php _e("Each time a user leaves a comment, a security key is stored as a custom key.  Periodically you may want to delete this information.  Please backup your database first.", $this->localizationName) ?></p>
    <p><label for="wpAJAXSecurityKeys_yes"><input type="radio" id="wpAJAXSecurityKeys_yes" name="wpAJAXSecurityKeys" value="true" /> <?php _e('Yes', $this->localizationName) ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpAJAXSecurityKeys_no"><input type="radio" id="wpAJAXSecurityKeys_no" name="wpAJAXSecurityKeys" value="false" checked="checked"/> <?php _e('No', $this->localizationName) ?></label></p>
		</td>
	</tr>
  </tbody>
</table>  
<div class="submit">
  <input type="submit" name="update_wp_ajaxEditCommentSettings" value="<?php _e('Update Settings', $this->localizationName) ?>" />
</div>
</form>
</div>
