<?php
/**
 * Customizer settings.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Array of all settings to be included in Customizer.
 * Some of these settings also appear in Discussion Board > Settings, e.g. info_bar_layout.
 * However, the style settings are only to be found here.
 *
 * @returns array
 *
 * @since 2.1.0
 */
function wpdbd_customizer_settings() {
	return array(
		'archive_layout'             => array(
			'id'      => 'archive_layout',
			'label'   => __( 'Archive layout', 'wp-discussion-board' ),
			'section' => 'wpdbd_archive',
			'control' => 'select',
			'choices' => array(
				'standard' => __( 'Archive', 'wp-discussion-board' ),
				'classic'  => __( 'Classic Forum', 'wp-discussion-board' ),
				'table'    => __( 'Table', 'wp-discussion-board' ),
			),
		),
		'table_border_color'         => array(
			'id'          => 'table_border_color',
			'label'       => __( 'Border Color', 'wp-discussion-board' ),
			'section'     => 'wpdbd_archive',
			'control'     => 'color',
			'declaration' => 'border-color',
			'element'     => '.ctdb-archive-layout-classic ul.ctdb-topic-table',
			'callback'    => 'wpdbd_is_classic_archive',
		),
		'header_bg_color'            => array(
			'id'          => 'header_bg_color',
			'label'       => __( 'Heading Background Color', 'wp-discussion-board' ),
			'section'     => 'wpdbd_archive',
			'control'     => 'color',
			'declaration' => 'background-color',
			'element'     => '.ctdb-archive-layout-classic .ctdb-topic-table-header ul.ctdb-topic-table-header-row li',
			'callback'    => 'wpdbd_is_classic_archive',
		),
		'header_text_color'          => array(
			'id'          => 'header_text_color',
			'label'       => __( 'Heading Text Color', 'wp-discussion-board' ),
			'section'     => 'wpdbd_archive',
			'control'     => 'color',
			'declaration' => 'color',
			'element'     => '.ctdb-archive-layout-classic .ctdb-topic-table-header',
			'callback'    => 'wpdbd_is_classic_archive',
		),
		'row_bg_color'               => array(
			'id'          => 'row_bg_color',
			'label'       => __( 'Row Background Color', 'wp-discussion-board' ),
			'section'     => 'wpdbd_archive',
			'control'     => 'color',
			'declaration' => 'background-color',
			'element'     => 'ul.ctdb-topic-table-row li',
			'callback'    => 'wpdbd_is_classic_archive',
		),
		'row_bg_color_alt'           => array(
			'id'          => 'row_bg_color_alt',
			'label'       => __( 'Row Background Color (Alt)', 'wp-discussion-board' ),
			'section'     => 'wpdbd_archive',
			'control'     => 'color',
			'declaration' => 'background-color',
			'element'     => 'ul.ctdb-topic-table-row:nth-of-type(even) li',
			'callback'    => 'wpdbd_is_classic_archive',
		),
		'row_border_color'           => array(
			'id'          => 'row_border_color',
			'label'       => __( 'Row Border Color', 'wp-discussion-board' ),
			'section'     => 'wpdbd_archive',
			'control'     => 'color',
			'declaration' => 'border-color',
			'element'     => 'ul.ctdb-topic-table-row li',
			'callback'    => 'wpdbd_is_classic_archive',
		),
		'row_text_color'             => array(
			'id'          => 'row_text_color',
			'label'       => __( 'Row Text Color', 'wp-discussion-board' ),
			'section'     => 'wpdbd_archive',
			'control'     => 'color',
			'declaration' => 'color',
			'element'     => 'ul.ctdb-topic-table-row li, ul.ctdb-topic-table-row li a',
			'callback'    => 'wpdbd_is_classic_archive',
		),
		'info_bar_layout'            => array(
			'id'      => 'info_bar_layout',
			'label'   => __( 'Single topic layout', 'wp-discussion-board' ),
			'section' => 'wpdbd_single',
			'control' => 'select',
			'choices' => array(
				'standard' => __( 'Archive', 'wp-discussion-board' ),
				'classic'  => __( 'Classic Forum', 'wp-discussion-board' ),
				'table'    => __( 'Table', 'wp-discussion-board' ),
			),
		),
		'single_table_border_color'  => array(
			'id'          => 'single_table_border_color',
			'label'       => __( 'Border Color', 'wp-discussion-board' ),
			'section'     => 'wpdbd_single',
			'control'     => 'color',
			'declaration' => 'border-color',
			'element'     => '.ctdb-single-layout-classic .ctdb-horizontal-meta',
			'callback'    => 'wpdbd_is_single_archive',
		),
		'single_table_divider_color' => array(
			'id'          => 'single_table_divider_color',
			'label'       => __( 'Divider Color', 'wp-discussion-board' ),
			'section'     => 'wpdbd_single',
			'control'     => 'color',
			'declaration' => 'border-color',
			'element'     => '.ctdb-info-cell',
			'callback'    => 'wpdbd_is_single_archive',
		),
		'single_row_bg_color'        => array(
			'id'          => 'single_row_bg_color',
			'label'       => __( 'Row Background Color', 'wp-discussion-board' ),
			'section'     => 'wpdbd_single',
			'control'     => 'color',
			'declaration' => 'background-color',
			'element'     => '.ctdb-single-layout-classic .ctdb-info-cell.ctdb-info-title',
			'callback'    => 'wpdbd_is_single_archive',
		),
		'single_row_bg_color_alt'    => array(
			'id'          => 'single_row_bg_color_alt',
			'label'       => __( 'Row Background Color (Alt)', 'wp-discussion-board' ),
			'section'     => 'wpdbd_single',
			'control'     => 'color',
			'declaration' => 'background-color',
			'element'     => '.ctdb-info-cell',
			'callback'    => 'wpdbd_is_single_archive',
		),
		'single_text_color'          => array(
			'id'          => 'single_text_color',
			'label'       => __( 'Text Color', 'wp-discussion-board' ),
			'section'     => 'wpdbd_single',
			'control'     => 'color',
			'declaration' => 'color',
			'element'     => '.ctdb-horizontal-meta',
			'callback'    => 'wpdbd_is_single_archive',
		),
	);
}

/**
 * Register the customizer settings.
 *
 * @param object $wp_customize The customizer object.
 */
function wpdbd_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'wpdbd',
		array(
			'title'    => __( 'Discussion Board', 'wp-discussion-board' ),
			'priority' => 999,
		)
	);

	$wp_customize->add_section(
		'wpdbd_archive',
		array(
			'title'    => __( 'Archive', 'wp-discussion-board' ),
			'panel'    => 'wpdbd',
			'priority' => 10,
		)
	);

	$wp_customize->add_section(
		'wpdbd_single',
		array(
			'title'    => __( 'Single', 'wp-discussion-board' ),
			'panel'    => 'wpdbd',
			'priority' => 20,
		)
	);

	$settings = wpdbd_customizer_settings();

	if ( ! empty( $settings ) ) {
		foreach ( $settings as $setting ) {
			// Layout settings
			$wp_customize->add_setting(
				'ctdb_design_settings[' . $setting['id'] . ']',
				array(
					'type'                 => 'option', // or 'option'
					'capability'           => 'edit_theme_options',
					'theme_supports'       => '', // Rarely needed.
					'default'              => '',
					'transport'            => 'refresh', // or postMessage
					'sanitize_callback'    => '',
					'sanitize_js_callback' => '', // Basically to_json.
				)
			);

			$control_args = array(
				'type'    => $setting['control'],
				'section' => $setting['section'], // Required, core or custom.
				'label'   => $setting['label'],
				'choices' => array(
					'standard' => __( 'Archive', 'wp-discussion-board' ),
					'classic'  => __( 'Classic Forum', 'wp-discussion-board' ),
					'table'    => __( 'Table', 'wp-discussion-board' ),
				),
			);

			if ( isset( $setting['choices'] ) ) {
				$control_args['choices'] = $setting['choices'];
			}

			if ( isset( $setting['callback'] ) ) {
				$control_args['active_callback'] = $setting['callback'];
			}

			$wp_customize->add_control( 'ctdb_design_settings[' . $setting['id'] . ']', $control_args );
		}
	}
}
add_action( 'customize_register', 'wpdbd_customize_register' );

/**
 * Is the classic archive template enabled?
 *
 * @return bool
 */
function wpdbd_is_classic_archive() {
	$options = get_option( 'ctdb_design_settings' );

	// Only return true if we have classic set
	if ( isset( $options['archive_layout'] ) && 'classic' === $options['archive_layout'] ) {
		return true;
	}

	return false;
}

/**
 * Is the single archive template enabled?
 *
 * @return bool
 */
function wpdbd_is_single_archive() {
	$options = get_option( 'ctdb_design_settings' );

	// Only return true if we have classic set
	if ( isset( $options['info_bar_layout'] ) && 'classic' === $options['info_bar_layout'] ) {
		return true;
	}

	return false;
}

/**
 * Enqueue the color picker scripts in case they are not already enqueued.
 *
 * @since 2.4.1
 */
function wpdbd_enqueue_color_picker() {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
}
add_action( 'customize_controls_enqueue_scripts', 'wpdbd_enqueue_color_picker' );
