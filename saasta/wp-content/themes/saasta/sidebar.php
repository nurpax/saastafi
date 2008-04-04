	<div id="sidebar">
		<ul>

			<li>
				<?php include (TEMPLATEPATH . '/searchform.php'); ?>
			</li>

<?php 
$user = wp_get_current_user();
if ( $user->ID ) {
    printf ("<li><span style=\"font-size:1.2em;\">Howdy <span style=\"font-size : 1.3em; font-weight : bold; font-style:italic;\">%s</span>!</span></li>", $user->display_name);
}
?>

			<?php /* If this is the frontpage */ if ( 1 || is_home() || is_page() ) { ?>
	<?php /* wp_list_bookmarks();*/ ?>

			<li>
            <a href="<?php print get_permalink(2553); ?>"><span style="font-size:2em;">Winners of Q4/2007!</span></a>
			</li>

				<li><h2>Meta</h2>
				<ul>
					<?php wp_register(); ?>
				    <li><?php wp_loginout(); ?></li>
<?php if ($user->ID) { ?>
<li><a href="<?php get_bloginfo('wpurl').'/wp-admin/post-new.php' ?>">Write a new post</a></li>
<?php } ?>
					 <li><a href="<?php print get_permalink(140); ?>"><?php print get_the_title(140); ?></a></li>
					 <li><a href="<?php print get_permalink(2624); ?>"><?php print get_the_title(2624); ?></a></li>
					 <li><a href="<?php print get_permalink(922); ?>"><?php print get_the_title(922); ?></a></li>
                     <li><a href="<?php print get_permalink(2598); ?>"><?php print get_the_title(2598); ?></a></li>
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
			</li></ul>

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

			<?php wp_list_categories('show_count=1&title_li=<h2>Categories</h2>'); ?>


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

			<li><h2>Archives</h2>
				<ul>
				<?php wp_get_archives('type=monthly'); ?>
				</ul>
			</li>

		</ul>

	</div>

