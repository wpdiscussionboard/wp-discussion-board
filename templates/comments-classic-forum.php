<?php
/**
 * The template for displaying comments for the classic forum skin
 * Based on Twenty Sixteen theme template.
 * @since 1.7.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area ctdb-comments-area">

	<?php if ( have_comments() ) : ?>

		<?php the_comments_navigation(); ?>

		<ul class="comment-list">
			<?php
			//	$CT_DB_Skins = new CT_DB_Skins();
				wp_list_comments( array(
					'type'			=> 'comment',
					'style'       	=> 'ul',
					'avatar_size' 	=> 80,
					'callback'		=> 'ctdb_classic_forum_comment'
				) );
			?>
		</ul><!-- .comment-list -->

		<?php the_comments_navigation(); ?>

	<?php endif; // Check for have_comments(). ?>

	<?php
		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
		<p class="no-comments"><?php _e( 'Comments are closed.', 'wp-discussion-board' ); ?></p>
	<?php endif; ?>

	<?php
		comment_form( array(
			'title_reply_before' => '<h2 id="reply-title" class="comment-reply-title">',
			'title_reply_after'  => '</h2>',
		) );
	?>

</div><!-- .comments-area -->
