<?php
/**
 * Featured Slider Options
 *
 * @package Catch Sketch
 */

/**
 * Add hero content options to theme options
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function catch_sketch_slider_options( $wp_customize ) {
	$wp_customize->add_section( 'catch_sketch_featured_slider', array(
			'panel' => 'catch_sketch_theme_options',
			'title' => esc_html__( 'Featured Slider', 'catch-sketch' ),
		)
	);

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_slider_option',
			'default'           => 'disabled',
			'sanitize_callback' => 'catch_sketch_sanitize_select',
			'choices'           => catch_sketch_section_visibility_options(),
			'label'             => esc_html__( 'Enable on', 'catch-sketch' ),
			'section'           => 'catch_sketch_featured_slider',
			'type'              => 'select',
		)
	);

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_slider_number',
			'default'           => '4',
			'sanitize_callback' => 'catch_sketch_sanitize_number_range',

			'active_callback'   => 'catch_sketch_is_slider_active',
			'description'       => esc_html__( 'Save and refresh the page if No. of Slides is changed (Max no of slides is 20)', 'catch-sketch' ),
			'input_attrs'       => array(
				'style' => 'width: 45px;',
				'min'   => 0,
				'max'   => 20,
				'step'  => 1,
			),
			'label'             => esc_html__( 'No of items', 'catch-sketch' ),
			'section'           => 'catch_sketch_featured_slider',
			'type'              => 'number',
			'transport'         => 'postMessage',
		)
	);

	$slider_number = get_theme_mod( 'catch_sketch_slider_number', 4 );

	for ( $i = 1; $i <= $slider_number ; $i++ ) {

		// Page Sliders
		catch_sketch_register_option( $wp_customize, array(
				'name'              => 'catch_sketch_slider_page_' . $i,
				'sanitize_callback' => 'catch_sketch_sanitize_post',
				'active_callback'   => 'catch_sketch_is_slider_active',
				'label'             => esc_html__( 'Page', 'catch-sketch' ) . ' # ' . $i,
				'section'           => 'catch_sketch_featured_slider',
				'type'              => 'dropdown-pages',
				'allow_addition'    => true,
			)
		);
	} // End for().
}
add_action( 'customize_register', 'catch_sketch_slider_options' );

/** Active Callback Functions */
if( ! function_exists( 'catch_sketch_is_slider_active' ) ) :
	/**
	* Return true if slider is active
	*
	* @since Catch Sketch 1.0
	*/
	function catch_sketch_is_slider_active( $control ) {
		$enable = $control->manager->get_setting( 'catch_sketch_slider_option' )->value();

		//return true only if previewed page on customizer matches the type of slider option selected
		return ( catch_sketch_check_section( $enable ) );
	}
endif;
