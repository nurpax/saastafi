<?php 
$commentID = (int)$_GET['c'];
$postID = (int)$_GET['p'];
$commentAction = $_GET['action'];
require_once("../../../../wp-config.php");
wp_enqueue_script('wp_ajax_edit_comments_screen', get_bloginfo('wpurl') . '/wp-content/plugins/wp-ajax-edit-comments/js/comment-editor.js.php', array("jquery", "wp-ajax-response") , 2.0); 

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
$localization = $WPrapAjaxEditComments->localizationName;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php 
if (empty($WPrapAjaxEditComments->adminOptions['editor_style_url'])) {
	if ($locale == "ar") {
		echo '<link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-content/plugins/wp-ajax-edit-comments/css/comment-editor-rtl.css" type="text/css" media="screen"  />'; 
	} else {
		echo '<link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-content/plugins/wp-ajax-edit-comments/css/comment-editor.css" type="text/css" media="screen"  />'; 
	}
} else {
	echo '<link rel="stylesheet" href="'.get_bloginfo('template_directory').$this->adminOptions['editor_style_url'].'" type="text/css" media="screen"  />';
}
do_action('add_wp_ajax_comments_css_editor');
?>
<title>WP Ajax Edit Comments Comment Editor</title>
<?php do_action("wp_head"); ?>
</head>
<body>
<div>
<div id="comment-edit-header">
	<div id="gravatar"></div>
  <div id="close"><a href="#"><img src="../images/no.png" width="16" height="16" alt="<?php _e('Close',$localization); ?>" title="<?php _e('Close',$localization); ?>"/></a></div>
  <div id="title"><a href="<?php echo attribute_escape($WPrapAjaxEditComments->adminOptions['editor_url']);?>"><?php echo attribute_escape($WPrapAjaxEditComments->adminOptions['editor_title']);?></a></div>
</div> <!-- end comment-edit-header -->
<?php 
/* Admin nonce */
if ($WPrapAjaxEditComments->is_comment_owner($postID)) {
	wp_nonce_field('wp-ajax-edit-comments_save-comment');
}
?>
<div><input type="hidden" id="commentID" value="<?php echo $commentID;?>" />
  <input type="hidden" id="postID" value="<?php echo $postID;?>" />
  <input type="hidden" id="action" value="<?php echo $commentAction;?>" /></div>
<?php if ($WPrapAjaxEditComments->can_edit_options($commentID, $postID)): 
	$showicons = $WPrapAjaxEditComments->adminOptions['allow_icons'];
	$showoptions = $WPrapAjaxEditComments->adminOptions['show_options'];
	if ($showoptions == "true") :
?>

<div id="comment-options" class="postbox closed">
  <h3>
  <a class="togbox">+</a>
  <?php _e("More Options", $localization); ?>
  </h3>
	<div class="inside">
  <?php endif; /*options */ ?>
  	<table class="form inputs">
    <tbody>
    	<?php if ($WPrapAjaxEditComments->can_edit_name($commentID, $postID)): ?>
      <tr>
        <td><label for="name"><?php _e('Name',$localization); ?></label></td>
        <td><span> : </span><input type="text" size="35" name="name" id="name" /></td>
      </tr>
      <?php endif;?>
      <?php if ($WPrapAjaxEditComments->can_edit_email($commentID, $postID)): ?>
      <tr>
        <td><label for="e-mail"><?php _e('E-mail',$localization); ?></label></td>
        <td><span> : </span><input type="text" size="35" name="e-mail" id="e-mail" /></td>
      </tr>
      <?php endif;?>
      <?php if ($WPrapAjaxEditComments->can_edit_url($commentID, $postID)): ?>
      <tr>
        <td><label for="URL"><?php _e('URL',$localization); ?></label></td>
        <td><span> : </span><input type="text" size="35" name="URL" id="URL" /></td>
      </tr>
      <?php endif;?>
    </tbody>
    </table>
    <table><tbody>
    <?php do_action('wp_ajax_comments_editor'); ?>
    </tbody></table>
    <?php if ($showoptions == "true") : ?>
  </div>
</div> <!-- end comment-options -->
<?php endif; /*options*/ ?>
<?php endif; ?>
<div class="form"><textarea cols="50" rows="<?php echo ($showoptions == "true") ? "5" : "8";?>" name="comment" id="comment">&nbsp;</textarea></div>
<div class="form" id="buttons">
	<div><input type="button" id="save" name="save" disabled="true" value="<?php _e('Save',$localization); ?>" /></div>
  <div><input type="button" name="cancel" id="cancel" disabled="true" value="<?php _e('Cancel',$localization); ?>" /></div>
  <div id="timer<?php echo $commentID ?>"></div>
</div>
<div id="status"><span id="message"></span><span id="close-option">&nbsp;-&nbsp;<a href="#"><?php _e('Close',$localization); ?></a></span></div>
</div> <!-- end comment-edit-container-->
</body>
</html>
