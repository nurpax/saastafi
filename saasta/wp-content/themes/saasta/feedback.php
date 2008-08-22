<?php
/*
Template Name: Feedback Form
*/
?>

<?php get_header(); ?>

<?php

function saasta_print_feedback_form() 
{
    $redirectURI = attribute_escape($_SERVER['REQUEST_URI']);

    print '<form action="'.get_option('siteurl').'/saasta-feedback.php" method="post">';
    print '<input type="hidden" name="redirect_to" value="'.$redirectURI.'"/>';
    print '<strong>Subject: </strong><br/><input size="70" type="text" name="subject"/><br/><br/>';
    print '<strong>Text:</strong><br/><textarea rows="20" cols="60" name="body"></textarea>';
    print '<br />';
    print '<input type="submit" style="border:1px solid black;font-size:smaller;" value="Send"/>';
    print '</form>';
}
?>

<div id="content" class="narrowcolumn">

<h2>Suggestion Box</h2>
<br/>

<?php if (isset($_GET['saved'])) { ?>
<p><strong>Feedback sent.  Thank you!</strong></p>
<?php } ?>

<?php
      // Simple spammer protection, maybe this will fool their simple
      // crawlers..
      if (!isset($_POST['not_sniffing_around']))
      {
          print '<form action="'.get_option('siteurl').'/?page_id=2624" method="post">';
          print '<input type="hidden" name="not_sniffing_around" value="1">';
          print '<input type="submit" name="spam" value="I am not a spammer">';
          print '</form>';
      } else
      {
?>

<?php if (!isset($_GET['saved'])) { ?>
<p>Send anonymous feedback on our site.  Improvement suggestions.. DIS..? RES! You tell us.</p>

<p>If you wish to be contacted by our officials, remember to include your e-mail address in your letter.</p>

<?php saasta_print_feedback_form (); ?>
<?php } ?>

<?php } ?>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
