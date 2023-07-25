<?php
/**
 * Discussion Board Skins class
 * @since 1.7.0
*/

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'CT_DB_Skins' ) ) {

	class CT_DB_Skins {

		public $skin = 'classic';
		public $single_layout = 'classic';
		private $meta_fields = array();
		public $show_icons = true;

		public function __construct() {

		}

		/**
		 * Initialize the class and start calling our hooks and filters
		 * @since 1.7.0
		 */
		public function init() {

			add_action( 'init', array( $this, 'get_vars' ) );

			add_filter( 'ctdb_filter_single_content_end', array( $this, 'filter_single_content' ), 50, 2 );

			// Priority 5 to ensure that same filter in CT_DB_Front_End runs later
			// Hiding comments template from users without permission
			add_filter( 'comments_template', array( $this, 'comments_template' ), 5 );

			add_filter( 'ctdb_meta_fields', array( $this, 'insert_meta_fields' ), 10, 3 );

			add_filter( 'ctdb_info_meta_after_author', array( $this, 'topic_date_table' ) );
			add_filter( 'ctdb_info_meta_after_classic_meta', array( $this, 'topic_date_classic' ) );

		}

		/**
		 * Filter the single content
		 * Add meta data fields and author data
		 * $position	below-title | below-content
		 * @since 1.7.0
		 */
		public function filter_single_content( $content, $position ) {

			if ( ! in_the_loop() ) return $content;
			if( 'discussion-topics' != get_post_type() ) return $content;

			/**
			 * Layout options include:
			 * standard	| Archive
			 * classic	| Classic
			 * table	| Table
			 *
			 * Prints the topic meta
			 */
			if( $this->single_layout == 'standard' ) {
				$topic_meta = $this->archive_topic_meta();
			} else {
				$topic_meta = $this->classic_topic_meta();
			}

			// Get the author avatar and name
			$author = $this->author_data();

			if( $this->single_layout == 'standard' ) {
				if( $position == 'below-title' ) {
					$content = $topic_meta . $content;
				} else if( $position == 'below-content' ) {
					$content = $content . $topic_meta;
				}
			} else if( $this->single_layout == 'table' ) {
				if( $position == 'below-title' ) {
					$content = $author . $topic_meta . $content;
				} else if( $position == 'below-content' ) {
					$content = $author . $content . $topic_meta;
				}
			} else if( $this->single_layout == 'classic' ) {
				$content = $topic_meta . $author . '<div class="ctdb-content-wrap">' . $content . '</div><!-- .ctdb-content-wrap -->';
			}

			return $content;

		}

		/**
		 * Display information about the topic - author, date, number comment
		 * Used in the archive layout style
		 * Previously info_bar_single in CT_DB_Front_End
		 * @since 1.7.0
		 */
		public function archive_topic_meta() {

			global $post;

			// Use icons?
			$show_icons = ctdb_use_icons();

			// Comments
			$output = '<div class="ctdb-information-bar">';
				$output .= '<div class="ctdb-info-bar-row">';
					$output .= '<div class="ctdb-span-3">';
						if( $show_icons ) $output .= '<span class="dashicons dashicons-format-chat"></span>';
						$output .= get_comments_number() . _n( ' reply', ' replies', get_comments_number(), 'wp-discussion-board' );
					$output .= '</div><!-- .ctdb-span-3 -->';

					// Topic author
					$output .= '<div class="ctdb-span-3 ctdb-border-left">';
						if( $show_icons ) $output .= '<span class="dashicons dashicons-admin-users"></span>';
						$author = get_the_author();
						$author = apply_filters( 'ctdb_author_name', $author );
						$output .= $author;
					$output .= '</div><!-- .ctdb-span-3 -->';

					// Topic date
					$output .= '<div class="ctdb-span-3 ctdb-border-left">';
						if( $show_icons ) $output .= '<span class="dashicons dashicons-calendar-alt"></span>';
						$output .= get_the_date();
					$output .= '</div><!-- .ctdb-span-3 -->';
				$output .= '</div><!-- .ctdb-info-bar-row -->';
			$output .= '</div><!-- .ctdb-information-bar -->';

			return $output;

		}

		/**
		 * The topic meta for a classic layout
		 * @since 1.7.0
		 */
		public function classic_topic_meta() {

			global $post;

			// Selected meta fields
			$fields = ctdb_selected_meta_fields();
			// Use this filter to check legitimacy of fields, e.g. if Status is selected but not available for a certain Board
			$fields = apply_filters( 'ctdb_meta_data_fields_filter', $fields );
			// Defined meta field titles
			$field_titles = ctdb_meta_data_fields();
			// Use this filter to check legitimacy of fields, e.g. if Status is selected but not available for a certain Board
			$field_titles = apply_filters( 'ctdb_meta_data_field_titles_filter', $field_titles );
			$output = '';

			if( ! empty( $fields ) ) {

				$field_count = count( $fields );

				$output .= '<div class="ctdb-horizontal-meta ctdb-field-cols-' . $field_count . '">';

					// Add a filter here to a separate function for fields
					// Remove the filter in the pro version and use diff function

					// Filter here to add additional meta information about the topic
					$output = apply_filters( 'ctdb_meta_fields', $output, $fields, $field_titles );

					// Filter here to add additional meta information about the topic
					$output = apply_filters( 'ctdb_info_meta_wrap_after_replies', $output );

				$output .= '</div><!-- .ctdb-horizontal-meta -->';

			}

			/**
			 * Topic categories
			 * @hooked CT_BD_Pro_Categories::display_categories_classic_layout
			 * @hooked CT_DB_Pro_Follow::follow_button
			 * @hooked CT_DB_Pro_Status::status_updater
			 */
			$output .= '<div class="ctdb-actions-wrapper">';
				$output = apply_filters( 'ctdb_info_meta_close_classic_meta', $output );
			$output .= '</div><!-- .ctdb-actions-wrapper -->';

			// Topic date
			$output = apply_filters( 'ctdb_info_meta_after_classic_meta', $output );

			return $output;

		}

		/**
		 * Return the topic fields
		 * @since 1.7.0
		 */
		public function insert_meta_fields( $output, $fields, $field_titles ) {

			global $post;

			foreach( $fields as $key=>$value ) {

				if( isset( $field_titles[$key] ) ) {
					// We only output content if it's supposed to be there, i.e. the key appears in the $field_titles array
					// $field_titles is defined in functions-skins.php

					// Number of replies
					if( $key == 'replies' ) {

						$output .= '<div class="ctdb-info-title ctdb-info-cell">';
							if( $this->show_icons ) $output .= '<span class="dashicons dashicons-format-chat"></span>';
							$output .= esc_html( $field_titles[$key] );
						$output .= '</div><!-- .ctdb-info-title -->';
						$output .= '<div class="ctdb-info-meta-wrap ctdb-info-cell">';
							$output .= '<div class="ctdb-info-meta">';
								$output .= get_comments_number();
							$output .= '</div><!-- .ctdb-info-meta -->';
						$output .= '</div><!-- .ctdb-info-meta-wrap -->';

					} else if( $key == 'voices' ) {

						$output .= '<div class="ctdb-info-title ctdb-info-cell">';
							if( $this->show_icons ) $output .= '<span class="dashicons dashicons-groups"></span>';
							$output .= esc_html( $field_titles[$key] );
						$output .= '</div><!-- .ctdb-info-title -->';
						$output .= '<div class="ctdb-info-meta-wrap ctdb-info-cell">';
							$output .= '<div class="ctdb-info-meta">';
								$comments = get_comments( $post );
								$output .= $this->count_voices( $comments );
							$output .= '</div><!-- .ctdb-info-meta -->';
						$output .= '</div><!-- .ctdb-info-meta-wrap -->';

					}

				}

			}

			return $output;

		}

		/**
		 * Return the markup for the post author
		 * @since 1.7.0
		 */
		public function author_data() {

			// We don't display icons at all on the archive/standard view
			$show_icons = false;
			if( $this->single_layout == 'standard' && $this->show_icons ) {
				$show_icons = true;
			}

			$output = '';
			$output .= '<div class="ctdb-horizontal-meta ctdb-author-data">';

				$author = get_the_author();

				$output .= '<div class="ctdb-info-avatar ctdb-info-title ctdb-info-cell">';
					$output .= get_avatar( get_the_author_meta( 'ID' ), 96 );
				$output .= '</div><!-- .ctdb-info-avatar -->';

				$output .= '<div class="ctdb-info-meta-wrap ctdb-info-cell">';

					// Topic author
					$output .= '<div class="ctdb-info-meta ctdb-info-author">';
						if( $show_icons ) $output .= '<span class="dashicons dashicons-admin-users"></span>';
						$author = apply_filters( 'ctdb_author_name', $author );
						$output .= $author;
					$output .= '</div>';

					// Topic date
					$output = apply_filters( 'ctdb_info_meta_after_author', $output );

				$output .= '</div><!-- .ctdb-info-meta-wrap -->';
			$output .= '</div><!-- .ctdb-info-bar-row -->';

			$output = apply_filters( 'ctdb_info_bar_table', $output, $author );

			return $output;

		}

		/**
		 * Include the topic date
		 * @since 1.7.0
		 */
		public function topic_date_table( $output ) {

			// We don't display icons at all on the archive/standard view
			$show_icons = false;
			if( $this->single_layout == 'standard' && $this->show_icons ) {
				$show_icons = true;
			}

			// Insert the date with the author name if it's table layout
			if( $this->skin == 'table' ) {
				$output .= '<div class="ctdb-info-meta comment-metadata">';
					if( $show_icons ) $output .= '<span class="dashicons dashicons-calendar-alt"></span>';
					$output .= get_the_date() . ' ' . __( 'at', 'wp-discussion-board' ) . ' ' . get_the_time();
				$output .= '</div>';
			}

			return $output;
		}

		/**
		 * Include the topic date above author name
		 * @since 1.7.0
		 */
		public function topic_date_classic( $output ) {
			// Insert the date with the author name if it's table layout
			if( $this->skin == 'classic' ) {
				$output .= '<div class="classic-topic-date comment-metadata">';
					$output .= ctdb_topic_date_time();
				$output .= '</div><!-- .classic-topic-date -->';
			}

			return $output;
		}


		/**
		 * Count how many voices there are in a topic, i.e. poster + number of commenters
		 * Also accessed from CT_DB_Front_End return_table_layout
		 * @since 1.7.0
		 * @todo deprecate this in favour of ctdb_count_voices in functions-skins.php
		 */
		public static function count_voices( $comments ) {

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

		/**
		 * Filter our comments template
		 * @since 1.7.0
		 */
		public function comments_template( $comment_template ) {

			// Different comment templates for different layouts
			if( $this->skin == 'classic' && 'discussion-topics' == get_post_type() ) {

				if( file_exists( trailingslashit ( get_stylesheet_directory() ) . 'comments-classic-forum.php' ) ) {
					return trailingslashit( get_stylesheet_directory() ) . 'comments-classic-forum.php';

				// Check parent theme next
				} else if( file_exists( trailingslashit( get_template_directory() ) . 'comments-classic-forum.php' ) ) {
					return trailingslashit( get_template_directory() ) . 'comments-classic-forum.php';

				// Check plugin compatibility last
				} else {
					return WPDBD_PLUGIN_DIR . '/templates/comments-classic-forum.php';
				}

			}

			return $comment_template;

		}

		/**
		 * Define our skin/layout
		 * @since 1.7.0
		 */
		public function get_vars() {

			$options = get_option( 'ctdb_design_settings' );

			if( ! isset( $options['layout'] ) ) {
				// If it's not set, use old info_bar_layout setting
				if( isset( $options['info_bar_layout'] ) ) {
					$layout = $options['info_bar_layout'];
				} else {
					$layout = 'classic';
				}
			} else {
				$layout = $options['layout'];
			}
			$this->skin = $layout;

			// Single layout style
			if( ! isset( $options['info_bar_layout'] ) ) {
				$this->single_layout = 'classic';
			} else {
				$this->single_layout = esc_attr( $options['info_bar_layout'] );
			}

			// From functions-skins.php
			// Grabs the selected meta fields
			$this->meta_fields = ctdb_selected_meta_fields();

			// Use icons?
			$options = get_option( 'ctdb_design_settings' );
			$this->show_icons = isset( $options['enqueue_dashicons'] );
		}

	}

}
