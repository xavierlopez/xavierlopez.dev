<?php
/**
 * Hero Content Options
 *
 * @package Catch Sketch
 */

/**
 * Add hero content options to theme options
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function catch_sketch_hero_content_options( $wp_customize ) {
	$wp_customize->add_section( 'catch_sketch_hero_content_options', array(
			'title' => esc_html__( 'Hero Content', 'catch-sketch' ),
			'panel' => 'catch_sketch_theme_options',
		)
	);

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_hero_content_visibility',
			'default'           => 'disabled',
			'sanitize_callback' => 'catch_sketch_sanitize_select',
			'choices'           => catch_sketch_section_visibility_options(),
			'label'             => esc_html__( 'Enable on', 'catch-sketch' ),
			'section'           => 'catch_sketch_hero_content_options',
			'type'              => 'select',
		)
	);

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_hero_content',
			'default'           => '0',
			'sanitize_callback' => 'catch_sketch_sanitize_post',
			'active_callback'   => 'catch_sketch_is_hero_content_active',
			'label'             => esc_html__( 'Page', 'catch-sketch' ),
			'section'           => 'catch_sketch_hero_content_options',
			'type'              => 'dropdown-pages',
			'allow_addition'    => true,
		)
	);
}
add_action( 'customize_register', 'catch_sketch_hero_content_options' );

/** Active Callback Functions **/
if ( ! function_exists( 'catch_sketch_is_hero_content_active' ) ) :
	/**
	* Return true if hero content is active
	*
	* @since Catch Sketch 1.0
	*/
	function catch_sketch_is_hero_content_active( $control ) {
		$enable = $control->manager->get_setting( 'catch_sketch_hero_content_visibility' )->value();

		//return true only if previewed page on customizer matches the type of content option selected
		return ( catch_sketch_check_section( $enable ) );
	}
endif;
