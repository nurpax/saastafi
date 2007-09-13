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

			<?php wp_list_pages('title_li=<h2>Pages</h2>' ); ?>

			<li>
			<a href="<?php print get_permalink(1465); ?>"><img src="vote.gif"/><br/><span style="font-size:2em;">Vote for Best Saasta of Q3/2007!</span></a>
			</li>

			<li>
			<a href="<?php print get_permalink(916); ?>"><span style="font-size:1.5em;">Best Saasta of Q2/2007!</span></a>
			</li>
			
			<li><h2>Random saasta</h2>
			<ul><li>
			<?php
			$foo = $wpdb->get_row("SELECT ID,post_title FROM saasta_posts WHERE post_status='publish' ORDER BY RAND() LIMIT 1");
			print '<a href="'.get_permalink($foo->ID).'" title="'.$foo->post_title.'">'.$foo->post_title.'</a>';
			?>
			</li></ul>
			<li><h2>Archives</h2>
				<ul>
				<?php wp_get_archives('type=monthly'); ?>
				</ul>
			</li>

			<?php wp_list_categories('show_count=1&title_li=<h2>Categories</h2>'); ?>

			<?php /* If this is the frontpage */ if ( 1 || is_home() || is_page() ) { ?>
				<?php wp_list_bookmarks(); ?>

				<li><h2>Meta</h2>
				<ul>
					<?php wp_register(); ?>
					<li><?php wp_loginout(); ?></li>
					<li><a href="http://validator.w3.org/check/referer" title="This page validates as XHTML 1.0 Transitional">Valid <abbr title="eXtensible HyperText Markup Language">XHTML</abbr></a></li>
					<li><a href="http://gmpg.org/xfn/"><abbr title="XHTML Friends Network">XFN</abbr></a></li>
					<li><a href="http://wordpress.org/" title="Powered by WordPress, state-of-the-art semantic personal publishing platform.">WordPress</a></li>
					<?php wp_meta(); ?>
				</ul>
				</li>
			<?php } ?>
		</ul>
	</div>

