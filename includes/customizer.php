<?php
/*
 * Customizer
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Array of all settings to be included in Customizer
 * Some of these settings also appear in Discussion Board > Settings, e.g. info_bar_layout
 * However, the style settings are only to be found here
 * 
 * @returns Array
 * @since 2.1.0
 */
function ctdb_customizer_settings() {
	$settings = array(
		'archive_layout' 	=> array(
			'id'			=> 'archive_layout',
			'label' 		=> __( 'Archive layout', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_archive',
			'control'		=> 'select',
			'choices'		=> array(
				'standard'		=> __( 'Archive', 'wp-discussion-board' ),
				'classic'		=> __( 'Classic Forum', 'wp-discussion-board' ),
				'table'			=> __( 'Table', 'wp-discussion-board' )
			),
		),
		'table_border_color' 	=> array(
			'id'			=> 'table_border_color',
			'label' 		=> __( 'Border Color', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_archive',
			'control'		=> 'color',
			'declaration' 	=> 'border-color',
			'element'		=> '.ctdb-archive-layout-classic ul.ctdb-topic-table',
			'callback'		=> 'ctdb_is_classic_archive'
		),
		'header_bg_color' 	=> array(
			'id'			=> 'header_bg_color',
			'label' 		=> __( 'Heading Background Color', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_archive',
			'control'		=> 'color',
			'declaration' 	=> 'background-color',
			'element'		=> '.ctdb-archive-layout-classic .ctdb-topic-table-header ul.ctdb-topic-table-header-row li',
			'callback'		=> 'ctdb_is_classic_archive'
		),
		'header_text_color' 	=> array(
			'id'			=> 'header_text_color',
			'label' 		=> __( 'Heading Text Color', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_archive',
			'control'		=> 'color',
			'declaration' 	=> 'color',
			'element'		=> '.ctdb-archive-layout-classic .ctdb-topic-table-header',
			'callback'		=> 'ctdb_is_classic_archive'
		),
		'row_bg_color' 	=> array(
			'id'			=> 'row_bg_color',
			'label' 		=> __( 'Row Background Color', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_archive',
			'control'		=> 'color',
			'declaration' 	=> 'background-color',
			'element'		=> 'ul.ctdb-topic-table-row li',
			'callback'		=> 'ctdb_is_classic_archive'
		),
		'row_bg_color_alt' 	=> array(
			'id'			=> 'row_bg_color_alt',
			'label' 		=> __( 'Row Background Color (Alt)', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_archive',
			'control'		=> 'color',
			'declaration' 	=> 'background-color',
			'element'		=> 'ul.ctdb-topic-table-row:nth-of-type(even) li',
			'callback'		=> 'ctdb_is_classic_archive'
		),
		'row_border_color' 	=> array(
			'id'			=> 'row_border_color',
			'label' 		=> __( 'Row Border Color', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_archive',
			'control'		=> 'color',
			'declaration' 	=> 'border-color',
			'element'		=> 'ul.ctdb-topic-table-row li',
			'callback'		=> 'ctdb_is_classic_archive'
		),
		'row_text_color' 	=> array(
			'id'			=> 'row_text_color',
			'label' 		=> __( 'Row Text Color', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_archive',
			'control'		=> 'color',
			'declaration' 	=> 'color',
			'element'		=> 'ul.ctdb-topic-table-row li, ul.ctdb-topic-table-row li a',
			'callback'		=> 'ctdb_is_classic_archive'
		),
		'info_bar_layout' 	=> array(
			'id'			=> 'info_bar_layout',
			'label' 		=> __( 'Single topic layout', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_single',
			'control'		=> 'select',
			'choices'		=> array(
				'standard'		=> __( 'Archive', 'wp-discussion-board' ),
				'classic'		=> __( 'Classic Forum', 'wp-discussion-board' ),
				'table'			=> __( 'Table', 'wp-discussion-board' )
			),
		),
		'single_table_border_color' 	=> array(
			'id'			=> 'single_table_border_color',
			'label' 		=> __( 'Border Color', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_single',
			'control'		=> 'color',
			'declaration' 	=> 'border-color',
			'element'		=> '.ctdb-single-layout-classic .ctdb-horizontal-meta',
			'callback'		=> 'ctdb_is_single_archive'
		),
		'single_table_divider_color' 	=> array(
			'id'			=> 'single_table_divider_color',
			'label' 		=> __( 'Divider Color', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_single',
			'control'		=> 'color',
			'declaration' 	=> 'border-color',
			'element'		=> '.ctdb-info-cell',
			'callback'		=> 'ctdb_is_single_archive'
		),
		'single_row_bg_color' 	=> array(
			'id'			=> 'single_row_bg_color',
			'label' 		=> __( 'Row Background Color', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_single',
			'control'		=> 'color',
			'declaration' 	=> 'background-color',
			'element'		=> '.ctdb-single-layout-classic .ctdb-info-cell.ctdb-info-title',
			'callback'		=> 'ctdb_is_single_archive'
		),
		'single_row_bg_color_alt' 	=> array(
			'id'			=> 'single_row_bg_color_alt',
			'label' 		=> __( 'Row Background Color (Alt)', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_single',
			'control'		=> 'color',
			'declaration' 	=> 'background-color',
			'element'		=> '.ctdb-info-cell',
			'callback'		=> 'ctdb_is_single_archive'
		),
		'single_text_color' 	=> array(
			'id'			=> 'single_text_color',
			'label' 		=> __( 'Text Color', 'wp-discussion-board' ),
			'section' 		=> 'ctdb_single',
			'control'		=> 'color',
			'declaration' 	=> 'color',
			'element'		=> '.ctdb-horizontal-meta',
			'callback'		=> 'ctdb_is_single_archive'
		),
	);
	return $settings;
}


function ctdb_customize_register( $wp_customize ) {

	$wp_customize -> add_panel( 'ctdb', array (
		'title'		=>	__( 'Discussion Board', 'wp-discussion-board' ),
		'priority'	=> 	999
	) );
	
	$wp_customize -> add_section( 'ctdb_archive', array (
		'title'		=>	__( 'Archive', 'wp-discussion-board' ),
		'panel'		=> 'ctdb',
		'priority'	=> 	10
	) );
	
	$wp_customize -> add_section( 'ctdb_single', array (
		'title'		=>	__( 'Single', 'wp-discussion-board' ),
		'panel'		=> 'ctdb',
		'priority'	=> 	20
	) );
	
	$settings = ctdb_customizer_settings();
	if( ! empty( $settings ) ) {
		foreach( $settings as $setting ) {
			// Layout settings
			$wp_customize -> add_setting( 'ctdb_design_settings[' . $setting['id'] . ']', array(
				'type' 					=> 'option', // or 'option'
				'capability' 			=> 'edit_theme_options',
				'theme_supports' 		=> '', // Rarely needed.
				'default' 				=> '',
				'transport'				=> 'refresh', // or postMessage
				'sanitize_callback' 	=> '',
				'sanitize_js_callback' 	=> '', // Basically to_json.
			) );
			$control_args =	array(
				'type' 					=> $setting['control'],
			//	'priority' 				=> 5, // Within the section.
				'section' 				=> $setting['section'], // Required, core or custom.
				'label' 				=> $setting['label'],
				'choices'				=> array(
					'standard'		=> __( 'Archive', 'wp-discussion-board' ),
					'classic'		=> __( 'Classic Forum', 'wp-discussion-board' ),
					'table'			=> __( 'Table', 'wp-discussion-board' )
				)
			);
			if( isset( $setting['choices'] ) ) {
				$control_args['choices'] = $setting['choices'];
			}
			if( isset( $setting['callback'] ) ) {
				$control_args['active_callback'] = $setting['callback'];
			}
			$wp_customize -> add_control( 'ctdb_design_settings[' . $setting['id'] . ']', $control_args );
		}
	}
}
add_action( 'customize_register', 'ctdb_customize_register' );

function ctdb_is_classic_archive() {
	$options = get_option( 'ctdb_design_settings' );
	// Only return true if we have classic set
	if( isset( $options['archive_layout'] )  && $options['archive_layout'] == 'classic' ) {
		return true;
	}
	return false;
}

function ctdb_is_single_archive() {
	$options = get_option( 'ctdb_design_settings' );
	// Only return true if we have classic set
	if( isset( $options['info_bar_layout'] )  && $options['info_bar_layout'] == 'classic' ) {
		return true;
	}
	return false;
}