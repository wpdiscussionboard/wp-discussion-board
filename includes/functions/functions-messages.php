<?php
/*
 * Functions for front end messages
 * Replaces original method which used text input fields in settings
 * This method is better suited for translations and multilingual sites
 * @since 2.2.3
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Return display_user_name setting
 * @since 2.2.3
 */
if (!function_exists('ctdb_get_archive_title')) {
	function ctdb_get_archive_title()
	{
		$archive_title = __('Discussion Topics', 'wp-discussion-board');
		$options = get_option('ctdb_options_settings');
		if (isset($options['archive_title'])) {
			// If we've entered some text in the settings for Archive Title, use it
			$archive_title = $options['archive_title'];
		} else {
			// Otherwise use the filterable default value
			$archive_title = apply_filters('ctdb_archive_title', $archive_title);
		}
		return $archive_title;
	}
}

/**
 * Return archive_description setting
 * @since 2.5.2
 */
if (!function_exists('ctdb_get_archive_description')) {
	function ctdb_get_archive_description()
	{
		$archive_description = '';
		$options = get_option('ctdb_options_settings');
		if (isset($options['archive_description']) && is_string($options['archive_description']) && !empty(trim($options['archive_description']))) {
			// If we've entered some text in the settings for Archive description, use it
			$archive_description = $options['archive_description'];
		} else {
			// Otherwise use the filterable default value
			$archive_description = apply_filters('ctdb_archive_description', $archive_description);
		}
		return $archive_description;
	}
}

/**
 * Return new_topic_message setting
 * @since 2.2.3
 */
if (!function_exists('ctdb_get_new_topic_message')) {
	function ctdb_get_new_topic_message()
	{
		$new_topic_message = __('Please enter your topic title and content below.', 'wp-discussion-board');
		$options = get_option('ctdb_options_settings');
		if (!empty($options['new_topic_message'])) {
			$new_topic_message = $options['new_topic_message'];
		} else {
			$new_topic_message = '<p>' . apply_filters('ctdb_new_topic_message', $new_topic_message) . '</p>';
		}
		return $new_topic_message;
	}
}

/**
 * Return restricted_title setting
 * @since 2.2.3
 */
if (!function_exists('ctdb_get_restricted_title')) {
	function ctdb_get_restricted_title()
	{
		$restricted_title = __('This page is not available.', 'wp-discussion-board');
		$options = get_option('ctdb_options_settings');
		if (!empty($options['restricted_title'])) {
			$restricted_title = $options['restricted_title'];
		} else {
			$restricted_title = '<p>' . apply_filters('ctdb_restricted_title', $restricted_title) . '</p>';
		}
		return $restricted_title;
	}
}

/**
 * Return restricted_title setting
 * @since 2.2.3
 */
if (!function_exists('ctdb_get_restricted_message')) {
	function ctdb_get_restricted_message()
	{
		$restricted_message = __('You\'re not currently permitted to view this content. Please log-in or register in order to view the page.', 'wp-discussion-board');
		$options = get_option('ctdb_options_settings');
		if (!empty($options['restricted_message'])) {
			$restricted_message = $options['restricted_message'];
		} else {
			$restricted_message = '<p>' . apply_filters('ctdb_restricted_message', $restricted_message) . '</p>';
		}
		return $restricted_message;
	}
}

/**
 * Return message if user is logged in but doesn't have sufficient access rights
 * @since 2.2.3
 */
if (!function_exists('ctdb_get_restricted_user_message')) {
	function ctdb_get_restricted_user_message()
	{
		$restricted_message = __('You\'re logged in but you don\'t seem to have permission to view the content. Please contact the site administrator for further information.', 'wp-discussion-board');
		$restricted_message = '<p>' . apply_filters('ctdb_restricted_user_message', $restricted_message) . '</p>';
		return $restricted_message;
	}
}
