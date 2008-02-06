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
    print '<strong>Subject: <input size="70" type="text" name="subject"/><br/><br/>';
    print '<strong>Text:</strong><textarea rows="20" cols="60" name="body"></textarea>';
    print '<br />';
    print '<input type="submit" style="border:1px solid black;font-size:smaller;background-color:#ddd391" value="Send"/>';
    print '</form>';
}

?>

<div id="content" class="narrowcolumn">

<?php if (isset($_GET['saved'])) { ?>
<p><strong>Feedback sent.  Thank you!</strong></p>
<?php } else { ?>
<h2>Feedback to Saasta Administrations</h2>
<p>Let us know what you think about our site:</p>
<?php saasta_print_feedback_form (); ?>
<?php } ?>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
