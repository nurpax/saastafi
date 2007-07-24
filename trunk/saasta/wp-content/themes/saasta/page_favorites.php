<?php
/*
Template Name: FAVORITES
*/
?>

<?php get_header(); ?>

<div id="content" class="narrowcolumn">

<?php

$user = wp_get_current_user();
if ( $user->ID ) {
?>

<h2>Your favorites</h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 

$foo = $wpdb->get_results("select p.post_title as title,f.post_id as post_id from saasta_posts p,saasta_faves f where f.post_id=p.ID and f.user_id=".$user->ID." order by title");
if (count($foo) > 0) {
	foreach ($foo as $f) {
		print '<tr><td width="100%" style="padding-top:0.5em;"><a href="'.get_permalink($f->post_id).'" title="'.$f->title.'">'.$f->title.'</a></td>';
		print '<td valign="middle" align="right" style="padding-top:0.5em;">';

        saasta_print_del_fave_form($f->post_id);

        print '</td></tr>';
		print '<tr><td colspan="2" style="height:0.5empx;border-top:1px dashed #666666;"></td></tr>';
	}
}
?>
</table>
<?php } ?>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
