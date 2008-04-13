<?php 
/* Figure out if we're on the index/home page or on a single post view */
$is_single_post = !(is_home () || is_page());
$user = wp_get_current_user();

function saasta_print_permalink($id) 
{
    global $wpdb;
    print '<a href="';
    print get_permalink($id);
    print '">';
    print get_the_title($id);
    print '</a>';
}

function saasta_print_if_logged_in($s)
{
    if (is_user_logged_in())
        print $s;
}

?>

	<div id="sidebar">
		<ul>
<?php if (!$is_single_post) print "<li>"; ?>
<?php if (!$is_single_post) include (TEMPLATEPATH . '/searchform.php'); ?>
<?php if (!$is_single_post) { print "</li>"; } ?>
			<?php /* If this is the frontpage */ if ( 1 || is_home() || is_page() ) { ?>

				<li><h2>Meta</h2>
				<ul>
                     <?php wp_register(); ?>
                     <li><?php wp_loginout();  saasta_print_if_logged_in(" [$user->display_name]"); ?></li>
<?php if (is_user_logged_in()) { print "<li><a href=\""; print get_bloginfo('wpurl').'/wp-admin/post-new.php">Write a new post</a></li>'; } ?>
<?php if (is_user_logged_in()) { print "<li>"; print saasta_print_permalink(140); print "</li>"; } ?>
					 <li><?php print saasta_print_permalink(2624); ?></li>
					 <?php if (is_user_logged_in()) { print "<li>"; print saasta_print_permalink(922); print "</li>"; } ?>
                     <li><?php print saasta_print_permalink(2598); ?></li>
                                                                                                                       <li>RSS: <a href="<?php bloginfo('rss2_url'); ?>">Entries</a> and <a href="<?php bloginfo('comments_rss2_url'); ?>">Comments</a></li>
					<?php wp_meta(); ?>
				</ul>
				</li>
			<?php } ?>

			<li><h2>Random saasta</h2>
			<ul><li>
			<?php
			$foo = $wpdb->get_row("SELECT ID,post_title FROM ".$wpdb->posts." WHERE post_status='publish' ORDER BY RAND() LIMIT 1");
			print '<a href="'.get_permalink($foo->ID).'" title="'.$foo->post_title.'">'.$foo->post_title.'</a>';
			?>
			</li></ul></li>

			<li>
			<?php /* If this is a 404 page */ if (is_404()) { ?>
			<?php /* If this is a category archive */ } elseif (is_category()) { ?>
			<p>You are currently browsing the archives for the <?php single_cat_title(''); ?> category.</p>

			<?php /* If this is a yearly archive */ } elseif (is_day()) { ?>
			<p>You are currently browsing the <a href="<?php bloginfo('home'); ?>/"><?php echo bloginfo('name'); ?></a> weblog archives
			for the day <?php the_time('l, F jS, Y'); ?>.</p>

			<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
			<p>You are currently browsing the <a href="<?php bloginfo('home'); ?>/"><?php echo bloginfo('name'); ?></a> weblog archives
			for <?php the_time('F, Y'); ?>.</p>

			<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
			<p>You are currently browsing the <a href="<?php bloginfo('home'); ?>/"><?php echo bloginfo('name'); ?></a> weblog archives
			for the year <?php the_time('Y'); ?>.</p>

			<?php /* If this is a monthly archive */ } elseif (is_search()) { ?>
			<p>You have searched the <a href="<?php echo bloginfo('home'); ?>/"><?php echo bloginfo('name'); ?></a> weblog archives
			for <strong>'<?php the_search_query(); ?>'</strong>. If you are unable to find anything in these search results, you can try one of these links.</p>

			<?php /* If this is a monthly archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
			<p>You are currently browsing the <a href="<?php echo bloginfo('home'); ?>/"><?php echo bloginfo('name'); ?></a> weblog archives.</p>

			<?php } ?>
			</li>

			<?php if (!$is_single_post) { wp_list_categories('show_count=1&title_li=<h2>Categories</h2>'); } ?>

</ul>
<script type="text/javascript"><!--
google_ad_client = "pub-7907497075456864";
/* 160x600, created 4/4/08 */
google_ad_slot = "3125109427";
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
<ul>

<li><h2>Recent faves</h2>
<ul>
<?php
$foo = saasta_list_recent_faves(5);

foreach ($foo as $f) {
	print '<li><a href="'.$f->url.'">'.$f->title.'</a> ('.$f->num_faves.')</li>';
    print "\n";
}
?>
</ul>
</li>
			<li><h2>Archives</h2>
				<ul>
				<?php wp_get_archives('type=monthly'); ?>
				</ul>
			</li>

		</ul>

	</div>

