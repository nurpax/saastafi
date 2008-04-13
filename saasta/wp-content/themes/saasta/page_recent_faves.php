<?php
/*
Template Name: Recent Faves
*/
?>

<?php get_header(); ?>

<div id="content" class="narrowcolumn">

<h2>Recently Faved Posts</h2>

<ul>
<?php
$foo = saasta_list_recent_faves(30);

foreach ($foo as $f) {
	print '<li><a href="'.$f->url.'">'.$f->title.'</a> ('.$f->num_faves.')</li>';
    print "\n";
}
?>
</ul>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
