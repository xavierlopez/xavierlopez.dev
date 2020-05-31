<?php
/**
 * Services options
 *
 * @package Catch Sketch
 */

/**
 * Add services content options to theme options
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function catch_sketch_service_options( $wp_customize ) {
	// Add note to Jetpack Testimonial Section
    catch_sketch_register_option( $wp_customize, array(
            'name'              => 'catch_sketch_service_jetpack_note',
            'sanitize_callback' => 'sanitize_text_field',
            'custom_control'    => 'Catch_Sketch_Note_Control',
            'label'             => sprintf( esc_html__( 'For all Services Options for this Theme, go %1$shere%2$s', 'catch-sketch' ),
                '<a href="javascript:wp.customize.section( \'catch_sketch_service\' ).focus();">',
                 '</a>'
            ),
           'section'            => 'services',
            'type'              => 'description',
            'priority'          => 1,
        )
    );

    $wp_customize->add_section( 'catch_sketch_service', array(
			'title' => esc_html__( 'Services', 'catch-sketch' ),
			'panel' => 'catch_sketch_theme_options',
		)
	);

	$action = 'install-plugin';
    $slug   = 'essential-content-types';

    $install_url = wp_nonce_url(
        add_query_arg(
            array(
                'action' => $action,
                'plugin' => $slug
            ),
            admin_url( 'update.php' )
        ),
        $action . '_' . $slug
    );

    catch_sketch_register_option( $wp_customize, array(
            'name'              => 'catch_sketch_service_jetpack_note',
            'sanitize_callback' => 'sanitize_text_field',
            'custom_control'    => 'Catch_Sketch_Note_Control',
            'active_callback'   => 'catch_sketch_is_ect_services_inactive',
            /* translators: 1: <a>/link tag start, 2: </a>/link tag close. */
            'label'             => sprintf( esc_html__( 'For Services, install %1$sEssential Content Types%2$s Plugin with Service Type Enabled', 'catch-sketch' ),
                '<a target="_blank" href="' . esc_url( $install_url ) . '">',
                '</a>'

            ),
           'section'            => 'catch_sketch_service',
            'type'              => 'description',
            'priority'          => 1,
        )
    );

	// Add color scheme setting and control.
	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_service_option',
			'default'           => 'disabled',
			'active_callback'   => 'catch_sketch_is_ect_services_active',
			'sanitize_callback' => 'catch_sketch_sanitize_select',
			'choices'           => catch_sketch_section_visibility_options(),
			'label'             => esc_html__( 'Enable on', 'catch-sketch' ),
			'section'           => 'catch_sketch_service',
			'type'              => 'select',
		)
	);

    catch_sketch_register_option( $wp_customize, array(
            'name'              => 'catch_sketch_service_cpt_note',
            'sanitize_callback' => 'sanitize_text_field',
            'custom_control'    => 'Catch_Sketch_Note_Control',
            'active_callback'   => 'catch_sketch_is_services_active',
            /* translators: 1: <a>/link tag start, 2: </a>/link tag close. */
			'label'             => sprintf( esc_html__( 'For CPT heading and sub-heading, go %1$shere%2$s', 'catch-sketch' ),
                 '<a href="javascript:wp.customize.control( \'ect_service_title\' ).focus();">',
                 '</a>'
            ),
            'section'           => 'catch_sketch_service',
            'type'              => 'description',
        )
    );

	catch_sketch_register_option( $wp_customize, array(
			'name'              => 'catch_sketch_service_number',
			'default'           => 6,
			'sanitize_callback' => 'catch_sketch_sanitize_number_range',
			'active_callback'   => 'catch_sketch_is_services_active',
			'description'       => esc_html__( 'Save and refresh the page if No. of Services is changed (Max no of Services is 20)', 'catch-sketch' ),
			'input_attrs'       => array(
				'style' => 'width: 100px;',
				'min'   => 0,
			),
			'label'             => esc_html__( 'No of items', 'catch-sketch' ),
			'section'           => 'catch_sketch_service',
			'type'              => 'number',
			'transport'         => 'postMessage',
		)
	);

	$number = get_theme_mod( 'catch_sketch_service_number', 6 );

	//loop for services post content
	for ( $i = 1; $i <= $number ; $i++ ) {

		//CPT
		catch_sketch_register_option( $wp_customize, array(
				'name'              => 'catch_sketch_service_cpt_' . $i,
				'sanitize_callback' => 'catch_sketch_sanitize_post',
				'active_callback'   => 'catch_sketch_is_services_active',
				'label'             => esc_html__( 'Services', 'catch-sketch' ) . ' ' . $i ,
				'section'           => 'catch_sketch_service',
				'type'              => 'select',
                'choices'           => catch_sketch_generate_post_array( 'ect-service' ),
			)
		);
	} // End for().
}
add_action( 'customize_register', 'catch_sketch_service_options', 10 );

/** Active Callback Functions **/
if ( ! function_exists( 'catch_sketch_is_services_active' ) ) :
	/**
	* Return true if services content is active
	*
	* @since Catch Sketch 1.0
	*/
	function catch_sketch_is_services_active( $control ) {
		$enable = $control->manager->get_setting( 'catch_sketch_service_option' )->value();

		//return true only if previewed page on customizer matches the type of content option selected
		return ( catch_sketch_is_ect_services_active( $control ) &&  catch_sketch_check_section( $enable ) );
	}
endif;

if ( ! function_exists( 'catch_sketch_is_ect_services_inactive' ) ) :
    /**
    * Return true if service is active
    *
    * @since Catch Sketch 1.0
    */
    function catch_sketch_is_ect_services_inactive( $control ) {
        return ! ( class_exists( 'Essential_Content_Service' ) || class_exists( 'Essential_Content_Pro_Service' ) );
    }
endif;

if ( ! function_exists( 'catch_sketch_is_ect_services_active' ) ) :
    /**
    * Return true if service is active
    *
    * @since Catch Sketch 1.0
    */
    function catch_sketch_is_ect_services_active( $control ) {
        return ( class_exists( 'Essential_Content_Service' ) || class_exists( 'Essential_Content_Pro_Service' ) );
    }
endif;
