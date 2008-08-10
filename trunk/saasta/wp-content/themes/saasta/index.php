<?php get_header(); ?>

	<div id="content" class="narrowcolumn">

    <?php saasta_print_upper_adsense_link_unit(); ?>

    <?php $post_count = 0; ?>
	<?php if (have_posts()) : ?>

		<?php while (have_posts()) : the_post(); ?>

			<div class="post" id="post-<?php the_ID(); ?>">
				<?php saasta_print_post_header(); ?>
				<div class="entry">
					<?php the_content('Read the rest of this entry &raquo;'); ?>
				</div>
				<p class="postmetadata"><?php the_tags('Tags: ', ', ', '<br />'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>

			</div>

         <?php 
                if ($post_count == 0) {
                    // Uncomment below line for post index ads
                    if (get_option('saasta_middle_adunit_enabled')) {
                        saasta_print_middle_image_adsense_unit ();
                    }
                }
             $post_count++;
         ?>


		<?php endwhile; ?>

		<div class="navigation">
			<div class="alignleft"><?php previous_posts_link('&laquo; Newer saasta') ?></div>
			<div class="alignright"><?php next_posts_link('Older saasta &raquo;') ?></div>
		</div>

	<?php else : ?>

		<h2 class="center">Not Found</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>
		<?php include (TEMPLATEPATH . "/searchform.php"); ?>

	<?php endif; ?>

	</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
