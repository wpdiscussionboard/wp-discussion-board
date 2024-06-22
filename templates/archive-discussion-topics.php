<?php

/**
 * The template for displaying Discussion Topics archives
 */

get_header(); ?>

<?php
/*
	 * The hook for the template's opening tags
	 * @hooked ctdb_open_wrapper_archive
	*/ ?>
<?php do_action('ctdb_open_wrapper_archive'); ?>

<?php if (have_posts()) : ?>

	<header class="page-header">
		<?php
		ctdb_the_archive_title('<h1 class="page-title">', '</h1>');
		ctdb_the_archive_description('<div class="taxonomy-description">', '</div>');
		?>
	</header><!-- .page-header -->

	<?php
	// Start the Loop.
	while (have_posts()) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="entry-header">
				<?php the_title(sprintf('<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url(get_permalink())), '</a></h2>'); ?>
			</header><!-- .entry-header -->

			<div class="entry-content">
				<?php the_content(sprintf(
					__('Continue reading %s', 'wp-discussion-board'),
					the_title('<span class="screen-reader-text">', '</span>', false)
				)); ?>
			</div><!-- .entry-content -->

			<footer class="entry-footer">
				<?php echo '<span class="comments-link">';
				comments_popup_link(__('Leave a comment', 'wp-discussion-board'), __('1 Comment', 'wp-discussion-board'), __('% Comments', 'wp-discussion-board'));
				echo '</span>'; ?>
				<?php edit_post_link(__('Edit', 'wp-discussion-board'), '<span class="edit-link">', '</span>'); ?>
			</footer><!-- .entry-footer -->

		</article>

	<?php // End the loop.
	endwhile;

	// Previous/next page navigation.
	the_posts_pagination(array(
		'prev_text'          => __('Previous page', 'wp-discussion-board'),
		'next_text'          => __('Next page', 'wp-discussion-board'),
		'before_page_number' => '<span class="meta-nav screen-reader-text">' . __('Page', 'wp-discussion-board') . ' </span>',
	));

// If no content, include the "No posts found" template.
else : ?>

	<section class="not-found">

		<header class="page-header">
			<h2 class="page-title"><?php _e('Discussion Board', 'wp-discussion-board'); ?></h2>
		</header><!-- .page-header -->

		<div class="page-content">
			<?php _e('There were no topics found.', 'wp-discussion-board'); ?>
		</div><!-- .page-content -->

		</article>

	<?php endif;

/*
		 * The hook for the template's closing tags
		 * @hooked ctdb_open_wrapper_archive
		*/ ?>
	<?php do_action('ctdb_close_wrapper_archive'); ?>

	<?php get_footer(); ?>