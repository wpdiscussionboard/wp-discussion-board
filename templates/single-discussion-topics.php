<?php
/**
 * The template for displaying a single Discussion Topic post type
 * Follow the guidance at wpdiscussionboard.com to update this for your theme
 */

get_header(); ?>

	<?php
	/*
	 * The hook for the template's opening tags
	 * @hooked ctdb_open_wrapper_single
	*/ ?>
	<?php do_action ( 'ctdb_open_wrapper_single' ); ?>
			
		<?php
		// Start the loop.
		while ( have_posts() ) : the_post(); ?>
			
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header><!-- .entry-header -->

				<div class="entry-content">
					<?php the_content(); ?>
				</div><!-- .entry-content -->
			
			</article>

			<?php // If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;
			
			// Previous/next post navigation.
			the_post_navigation ( array(
				'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next', 'wp-discussion-board' ) . '</span> ' .
					'<span class="screen-reader-text">' . __( 'Next topic:', 'wp-discussion-board' ) . '</span> ' .
					'<span class="post-title">%title</span>',
				'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous', 'wp-discussion-board' ) . '</span> ' .
					'<span class="screen-reader-text">' . __( 'Previous topic:', 'wp-discussion-board' ) . '</span> ' .
					'<span class="post-title">%title</span>',
			) );

		// End the loop.
		endwhile;
		?>

	<?php
	/*
	 * The hook for the template's closing tags
	 * @hooked ctdb_close_wrapper_single
	*/ ?>
	<?php do_action ( 'ctdb_close_wrapper_single' ); ?>

<?php get_footer(); ?>
