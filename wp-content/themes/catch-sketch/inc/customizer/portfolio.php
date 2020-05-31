<?php
/**
 * Add Portfolio Settings in Customizer
 *
 * @package Catch_Sketch
 */

/**
 * Add portfolio options to theme options
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function catch_sketch_portfolio_options( $wp_customize ) {
    // Add note to Jetpack Portfolio Section
    catch_sketch_register_option( $wp_customize, array(
            'name'              => 'catch_sketch_jetpack_portfolio_cpt_note',
            'sanitize_callback' => 'sanitize_text_field',
            'custom_control'    => 'Catch_Sketch_Note_Control',
            'label'             => sprintf( esc_html__( 'For Portfolio Options for Catch Sketch Theme, go %1$shere%2$s', 'catch-sketch' ),
                 '<a href="javascript:wp.customize.section( \'catch_sketch_portfolio\' ).focus();">',
                 '</a>'
            ),
            'section'           => 'jetpack_portfolio',
            'type'              => 'description',
            'priority'          => 1,
        )
    );

    $wp_customize->add_section( 'catch_sketch_portfolio', array(
            'panel'    => 'catch_sketch_theme_options',
            'title'    => esc_html__( 'Portfolio', 'catch-sketch' ),
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
            'name'              => 'catch_sketch_portfolio_jetpack_note',
            'sanitize_callback' => 'sanitize_text_field',
            'custom_control'    => 'Catch_Sketch_Note_Control',
            'active_callback'   => 'catch_sketch_is_ect_portfolio_inactive',
            /* translators: 1: <a>/link tag start, 2: </a>/link tag close. */
            'label'             => sprintf( esc_html__( 'For Portfolio, install %1$sEssential Content Types%2$s Plugin with Portfolio Type Enabled', 'catch-sketch' ),
                '<a target="_blank" href="' . esc_url( $install_url ) . '">',
                '</a>'

            ),
           'section'            => 'catch_sketch_portfolio',
            'type'              => 'description',
            'priority'          => 1,
        )
    );

    catch_sketch_register_option( $wp_customize, array(
            'name'              => 'catch_sketch_portfolio_option',
            'default'           => 'disabled',
            'active_callback'   => 'catch_sketch_is_ect_portfolio_active',
            'sanitize_callback' => 'catch_sketch_sanitize_select',
            'choices'           => catch_sketch_section_visibility_options(),
            'label'             => esc_html__( 'Enable on', 'catch-sketch' ),
            'section'           => 'catch_sketch_portfolio',
            'type'              => 'select',
        )
    );

    catch_sketch_register_option( $wp_customize, array(
            'name'              => 'catch_sketch_portfolio_cpt_note',
            'sanitize_callback' => 'sanitize_text_field',
            'custom_control'    => 'Catch_Sketch_Note_Control',
            'active_callback'   => 'catch_sketch_is_portfolio_active',
            /* translators: 1: <a>/link tag start, 2: </a>/link tag close. */
            'label'             => sprintf( esc_html__( 'For CPT heading and sub-heading, go %1$shere%2$s', 'catch-sketch' ),
                 '<a href="javascript:wp.customize.control( \'jetpack_portfolio_title\' ).focus();">',
                 '</a>'
            ),
            'section'           => 'catch_sketch_portfolio',
            'type'              => 'description',
        )
    );

    catch_sketch_register_option( $wp_customize, array(
            'name'              => 'catch_sketch_portfolio_number',
            'default'           => '6',
            'sanitize_callback' => 'catch_sketch_sanitize_number_range',
            'active_callback'   => 'catch_sketch_is_portfolio_active',
            'label'             => esc_html__( 'Number of items to show', 'catch-sketch' ),
            'section'           => 'catch_sketch_portfolio',
            'type'              => 'number',
            'input_attrs'       => array(
                'style'             => 'width: 100px;',
                'min'               => 0,
            ),
        )
    );

    $number = get_theme_mod( 'catch_sketch_portfolio_number', 6 );

    for ( $i = 1; $i <= $number ; $i++ ) {

        //for CPT
        catch_sketch_register_option( $wp_customize, array(
                'name'              => 'catch_sketch_portfolio_cpt_' . $i,
                'sanitize_callback' => 'catch_sketch_sanitize_post',
                'active_callback'   => 'catch_sketch_is_portfolio_active',
                'label'             => esc_html__( 'Portfolio', 'catch-sketch' ) . ' ' . $i ,
                'section'           => 'catch_sketch_portfolio',
                'type'              => 'select',
                'choices'           => catch_sketch_generate_post_array( 'jetpack-portfolio' ),
            )
        );
    } // End for().
}
add_action( 'customize_register', 'catch_sketch_portfolio_options' );

/**
 * Active Callback Functions
 */
if ( ! function_exists( 'catch_sketch_is_portfolio_active' ) ) :
    /**
    * Return true if portfolio is active
    *
    * @since Catch Sketch 1.0
    */
    function catch_sketch_is_portfolio_active( $control ) {
        $enable = $control->manager->get_setting( 'catch_sketch_portfolio_option' )->value();

        //return true only if previwed page on customizer matches the type of content option selected
        return ( catch_sketch_is_ect_portfolio_active( $control ) &&  catch_sketch_check_section( $enable ) );
    }
endif;

if ( ! function_exists( 'catch_sketch_is_ect_portfolio_inactive' ) ) :
    /**
    *
    * @since Catch Sketch 1.0
    */
    function catch_sketch_is_ect_portfolio_inactive( $control ) {
        return ! ( class_exists( 'Essential_Content_Jetpack_Portfolio' ) || class_exists( 'Essential_Content_Pro_Jetpack_Portfolio' ) );
    }
endif;

if ( ! function_exists( 'catch_sketch_is_ect_portfolio_active' ) ) :
    /**
    *
    * @since Catch Sketch 1.0
    */
    function catch_sketch_is_ect_portfolio_active( $control ) {
        return ( class_exists( 'Essential_Content_Jetpack_Portfolio' ) || class_exists( 'Essential_Content_Pro_Jetpack_Portfolio' ) );
    }
endif;
