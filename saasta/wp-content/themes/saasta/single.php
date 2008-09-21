<?php get_header(); ?>

	<div id="content" class="narrowcolumn">

    <?php saasta_print_upper_adsense_link_unit(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
			<?php saasta_print_post_header(); ?>
			<div class="entry">
				<?php the_content('<p class="serif">Read the rest of this entry &raquo;</p>'); ?>

<?php 

if (isset($_REQUEST['makkonen'])) {
	if (get_the_tag_list())
		echo get_the_tag_list('<div id="taglist_'.get_the_ID().'"><p style="border-top:1px solid #666666;padding-top:5px;">Tags: ',', ',' <input class="saastaui" type="button" onclick="showLiveAddTagForm('.get_the_ID().',this,true)" value="add"/></p></div>');
	else
		echo '<div id="taglist_'.get_the_ID().'"><input class="saastaui" type="button" onclick="showLiveAddTagForm('.get_the_ID().',this,false)" value="add"/></div>';
}
else {
	if (get_the_tag_list())
		echo get_the_tag_list('<p style="border-top:1px solid #666666;padding-top:5px;">Tags: ',', ','</p>');
}
?>
<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
<?php saasta_print_share_post_buttons(); ?>
				<p class="postmetadata alt">
					<small>
						This entry was posted
						<?php /* This is commented, because it requires a little adjusting sometimes.
							You'll need to download this plugin, and follow the instructions:
							http://binarybonsai.com/archives/2004/08/17/time-since-plugin/ */
							/* $entry_datetime = abs(strtotime($post->post_date) - (60*120)); echo time_since($entry_datetime); echo ' ago'; */ ?>
						on <?php the_time('l, F jS, Y') ?> at <?php the_time() ?>
						and is filed under <?php the_category(', ') ?>.
						You can follow any responses to this entry through the <?php comments_rss_link('RSS 2.0'); ?> feed.

						<?php if (('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
							// Both Comments and Pings are open ?>
							You can <a href="#respond">leave a response</a>, or <a href="<?php trackback_url(true); ?>" rel="trackback">trackback</a> from your own site.

						<?php } elseif (!('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
							// Only Pings are Open ?>
							Responses are currently closed, but you can <a href="<?php trackback_url(true); ?> " rel="trackback">trackback</a> from your own site.

						<?php } elseif (('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
							// Comments are open, Pings are not ?>
							You can skip to the end and leave a response. Pinging is currently not allowed.

						<?php } elseif (!('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
							// Neither Comments, nor Pings are open ?>
							Both comments and pings are currently closed.

						<?php } edit_post_link('Edit this entry.','',''); ?>

					</small>
				</p>
			</div>
		</div>
	<?php comments_template(); ?>

	<?php endwhile; else: ?>

		<p>Sorry, no posts matched your criteria.</p>

<?php endif; ?>


	</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
