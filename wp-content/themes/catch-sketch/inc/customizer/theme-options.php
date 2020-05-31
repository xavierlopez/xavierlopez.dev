<?php
/**
 * Theme Options
 *
 * @package Catch Sketch
 */

/**
 * Add theme options
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function catch_sketch_theme_options( $wp_customize ) {
	$wp_customize->add_panel( 'catch_sketch_theme_options', array(
		'title'    => esc_html__( 'Theme Options', 'catch-sketch' ),
		'priority' => 130,
	) );

	// Layout Options
	$wp_customize->add_section( 'catch_sketch_layout_options', array(
		'title' => esc_html__( 'Layout Options', 'catch-sketch' ),
		'panel' => 'catch_sketch_theme_options',
		)
	);

	/* Default Layout */
	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_default_layout',
			'default'           => 'right-sidebar',
			'sanitize_callback' => 'catch_sketch_sanitize_select',
			'label'             => esc_html__( 'Default Layout', 'catch-sketch' ),
			'section'           => 'catch_sketch_layout_options',
			'type'              => 'select',
			'choices'           => array(
				'right-sidebar'         => esc_html__( 'Right Sidebar ( Content, Primary Sidebar )', 'catch-sketch' ),
				'no-sidebar'            => esc_html__( 'No Sidebar', 'catch-sketch' ),
			),
		)
	);

	/* Homepage Layout */
	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_homepage_layout',
			'default'           => 'right-sidebar',
			'sanitize_callback' => 'catch_sketch_sanitize_select',
			'label'             => esc_html__( 'Homepage Layout', 'catch-sketch' ),
			'section'           => 'catch_sketch_layout_options',
			'type'              => 'select',
			'choices'           => array(
				'right-sidebar'         => esc_html__( 'Right Sidebar ( Content, Primary Sidebar )', 'catch-sketch' ),
				'no-sidebar'            => esc_html__( 'No Sidebar', 'catch-sketch' ),
			),
		)
	);

	/* Blog/Archive Layout */
	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_archive_layout',
			'default'           => 'right-sidebar',
			'sanitize_callback' => 'catch_sketch_sanitize_select',
			'label'             => esc_html__( 'Blog/Archive Layout', 'catch-sketch' ),
			'section'           => 'catch_sketch_layout_options',
			'type'              => 'select',
			'choices'           => array(
				'right-sidebar'         => esc_html__( 'Right Sidebar ( Content, Primary Sidebar )', 'catch-sketch' ),
				'no-sidebar'            => esc_html__( 'No Sidebar', 'catch-sketch' ),
			),
		)
	);

	// Single Page/Post Image
	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_single_layout',
			'default'           => 'disabled',
			'sanitize_callback' => 'catch_sketch_sanitize_select',
			'label'             => esc_html__( 'Single Page/Post Image', 'catch-sketch' ),
			'section'           => 'catch_sketch_layout_options',
			'type'              => 'select',
			'choices'           => array(
				'disabled'            => esc_html__( 'Disabled', 'catch-sketch' ),
				'post-thumbnail'      => esc_html__( 'Post Thumbnail (1060x596)', 'catch-sketch' ),
			),
		)
	);

	// Excerpt Options.
	$wp_customize->add_section( 'catch_sketch_excerpt_options', array(
		'panel' => 'catch_sketch_theme_options',
		'title' => esc_html__( 'Excerpt Options', 'catch-sketch' ),
	) );

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_excerpt_length',
			'default'           => '20',
			'sanitize_callback' => 'absint',
			'input_attrs' => array(
				'min'   => 10,
				'max'   => 200,
				'step'  => 5,
				'style' => 'width: 60px;',
			),
			'label'    => esc_html__( 'Excerpt Length (words)', 'catch-sketch' ),
			'section'  => 'catch_sketch_excerpt_options',
			'type'     => 'number',
		)
	);

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_excerpt_more_text',
			'default'           => esc_html__( 'Continue reading', 'catch-sketch' ),
			'sanitize_callback' => 'sanitize_text_field',
			'label'             => esc_html__( 'Read More Text', 'catch-sketch' ),
			'section'           => 'catch_sketch_excerpt_options',
			'type'              => 'text',
		)
	);

	// Excerpt Options.
	$wp_customize->add_section( 'catch_sketch_search_options', array(
		'panel'     => 'catch_sketch_theme_options',
		'title'     => esc_html__( 'Search Options', 'catch-sketch' ),
	) );

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_search_text',
			'default'           => esc_html__( 'Search ...', 'catch-sketch' ),
			'sanitize_callback' => 'wp_kses_data',
			'label'             => esc_html__( 'Search Text', 'catch-sketch' ),
			'section'           => 'catch_sketch_search_options',
			'type'              => 'text',
		)
	);

	// Homepage / Frontpage Options.
	$wp_customize->add_section( 'catch_sketch_homepage_options', array(
		'description' => esc_html__( 'Only posts that belong to the categories selected here will be displayed on the front page', 'catch-sketch' ),
		'panel'       => 'catch_sketch_theme_options',
		'title'       => esc_html__( 'Homepage / Frontpage Options', 'catch-sketch' ),
	) );

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_recent_posts_heading',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => esc_html__( 'Blog', 'catch-sketch' ),
			'label'             => esc_html__( 'Recent Posts Heading', 'catch-sketch' ),
			'section'           => 'catch_sketch_homepage_options',
		)
	);

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_front_page_category',
			'sanitize_callback' => 'catch_sketch_sanitize_category_list',
			'custom_control'    => 'Catch_Sketch_Multi_Cat',
			'label'             => esc_html__( 'Categories', 'catch-sketch' ),
			'section'           => 'catch_sketch_homepage_options',
			'type'              => 'dropdown-categories',
		)
	);

	// Pagination Options.
	$wp_customize->add_section( 'catch_sketch_pagination_options', array(
		'panel'       => 'catch_sketch_theme_options',
		'title'       => esc_html__( 'Pagination Options', 'catch-sketch' ),
	) );

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_pagination_type',
			'default'           => 'default',
			'sanitize_callback' => 'catch_sketch_sanitize_select',
			'choices'           => catch_sketch_get_pagination_types(),
			'label'             => esc_html__( 'Pagination type', 'catch-sketch' ),
			'section'           => 'catch_sketch_pagination_options',
			'type'              => 'select',
		)
	);

	/* Scrollup Options */
	$wp_customize->add_section( 'catch_sketch_scrollup', array(
		'panel'    => 'catch_sketch_theme_options',
		'title'    => esc_html__( 'Scrollup Options', 'catch-sketch' ),
	) );

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_display_scrollup',
			'sanitize_callback' => 'catch_sketch_sanitize_checkbox',
			'default'           => 1,
			'label'             => esc_html__( 'Display Scroll Up', 'catch-sketch' ),
			'section'           => 'catch_sketch_scrollup',
			'custom_control'    => 'Catch_Sketch_Toggle_Control',
		)
	);
}
add_action( 'customize_register', 'catch_sketch_theme_options' );
