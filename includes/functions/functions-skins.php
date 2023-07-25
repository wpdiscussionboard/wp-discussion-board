<?php
/*
 * Functions for skins
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment callback for classic forum
 * @since 1.7.0
 */
if ( ! function_exists ( 'ctdb_classic_forum_comment' ) ) {
	function ctdb_classic_forum_comment( $comment, $args, $depth ) {
		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li'; 
		
		// Build the comment HTML
		$classes = get_comment_class( empty( $args['has_children']) ? 'parent' : '', $comment );
		$comment_html = '<' . $tag . ' id="comment-' . get_comment_ID() . '" class="' . esc_attr( join( ' ', $classes ) ) . '">';
		$comment_html .= '<article id="div-comment-' . get_comment_ID() . '" class="comment-body">';
					
		$comment_html .= '<header class="comment-header">';
		$comment_html .= '<div class="comment-metadata">';
		$comment_html .= '<a href="' . esc_url( get_comment_link( $comment, $args ) ) . '">';
		$comment_html .= '<time class="comment-timeago" datetime="' . get_comment_time( 'c' ) . '">';
		$comment_html .= sprintf( __( '%1$s at %2$s' ), get_comment_date( '', $comment ), get_comment_time() );
		$comment_html .= '</time>';
		$comment_html .= '</a>';
	                       
		// do_action( 'ctdb_comment_metadata_end' );
					
		$comment_html .= '</div><!-- .comment-metadata -->';

		if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
			$comment_html .= sprintf( '<span class="edit-link ctdb-edit-link"><a href="%s">%s</a></span>', get_edit_comment_link(), __( 'Edit', 'wp-discussion-board' ) );
		}
		$comment_html .= '</header>';
		$comment_html .= '<footer class="comment-meta">';
		$comment_html .= '<div class="comment-author vcard">';
		$comment_html .= get_avatar( $comment, $args['avatar_size'] );
		$comment_html .= get_comment_author_link( $comment );
		$comment_html .= '</div><!-- .comment-author -->';
		// do_action( 'ctdb_comment_author_end' );
		$comment_html .= '</footer><!-- .comment-meta -->';

		$comment_html .= '<div class="comment-content">';
						
		$comment_html .= wpautop( get_comment_text() );
		
		if ( '0' == $comment->comment_approved ) {
			$comment_html .= '<p class="comment-awaiting-moderation">' . __( 'Your comment is awaiting moderation.', 'wp-discussion-board' ) . '</p>';
		}
		$comment_html .= get_comment_reply_link( array_merge( $args, array(
			'add_below' => 'div-comment',
			'depth'     => $depth,
			'max_depth' => $args['max_depth'],
			'before'    => '<span class="reply ctdb-reply">',
			'after'     => '</span>'
		) ) );
		$comment_html .= '</div><!-- .comment-content -->';
		$comment_html .= '</article><!-- .comment-body -->';
		
		$comment_html = apply_filters( 'ctdb_filter_comment_html', $comment_html, $comment, $args, $depth );
		
		echo $comment_html;
	}
}

/*
 * A list of fields displaying meta data for topics
 * Called in CT_DB_Admin
 * Called in CT_DB_Skins
 * Update the value, never the key
 * @returns Array
 * @since 1.7.0
 */
function ctdb_meta_data_fields() {
	$fields = array( 
		'replies' 	=> __( 'Replies', 'wp-discussion-board' ),
		'voices'	=> __( 'Voices', 'wp-discussion-board' ),
	);
	$fields = apply_filters( 'ctdb_topic_meta_data_fields', $fields );
	return $fields;
}

/*
 * Return the list of selected meta data fields
 * We use this to check what fields to display in the single topic page
 * Used in CT_DB_Skins
 * @returns Array
 * @since 1.7.0
 */
function ctdb_selected_meta_fields() {
	$options = get_option( 'ctdb_design_settings' );
	
	$meta_data_fields = array();
	if( isset( $options['meta_data_fields'] ) ) {
		$meta_data_fields = $options['meta_data_fields'];
	}

	// Check what fields are permitted, in case user disables Pro version
	$permitted_fields = ctdb_meta_data_fields();
	// Remove any fields that aren't permitted
	$meta_data_fields = array_diff( $meta_data_fields, $permitted_fields );

	return $meta_data_fields;
}

/*
 * Return the topic date and time
 * Used in CT_DB_Skins
 * @returns HTML
 * @since 2.2.9
 */
function ctdb_topic_date_time() {
	$time = date( 'c', strtotime( get_the_date( 'Y-m-d' ) . ' ' . get_the_time() ) );
	$date = '<time class="timeago" datetime="' . $time . '">' . get_the_date() . ' ' . __( 'at', 'wp-discussion-board' ) . ' ' . get_the_time() . '</time>';
	
	$date = apply_filters( 'ctdb_filter_topic_date_time', $date, $time );
	
	return $date;
}

/**
 * Returns most recent comment time
 * @since 2.2.9
 * @return Mixed
 */
if ( ! function_exists ( 'ctdb_get_most_recent_comment_time' ) ) {
	function ctdb_get_most_recent_comment_time() {
		global $post;
		$args = array(
			'number'	=> '1',
			'post_id'	=> $post->ID
		);
		$comments = get_comments( $args );
		if( ! empty( $comments ) ) {
			$datetime = date( 'c', strtotime( $comments[0]->comment_date ) );
			$date_format = get_option( 'date_format' );
			$date = date( $date_format, strtotime( $comments[0]->comment_date ) );
			
			$datetime = '<time class="timeago" datetime="' . $datetime . '">' . $date . '</time>';
			
			return $datetime;
		}
		return false;
	}
}

/**
 * Count how many voices there are in a topic, i.e. poster + number of commenters
 * Also accessed from CT_DB_Front_End return_table_layout
 * @return Integer
 * @since 2.2.9
 */
function ctdb_count_voices() {
	global $post;
	$comments = get_comments( $post );
	// No comments, return 1
	if( empty( $comments ) ) {
		return 1;
	} else {
		// Count commenters by email
		$commenters = array();
		foreach( $comments as $comment ) {
			if( ! in_array( $comment->comment_author_email, $commenters ) ) {
				$commenters[] = $comment->comment_author_email;
			}
		}
		$total = count( $commenters );
		// If original poster has not commented, we need to add their voice
		if( ! in_array( get_the_author_meta( 'email' ), $commenters ) ) {
			$total++;
		}
	}
	return $total;
}