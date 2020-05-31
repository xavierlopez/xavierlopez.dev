<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Catch Sketch
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function catch_sketch_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}
	
	$classes[] = 'navigation-default';
	$classes[] = 'fluid-layout';

	// Adds a class with respect to layout selected.
	$layout  = catch_sketch_get_theme_layout();
	$sidebar = catch_sketch_get_sidebar_id();

	if ( 'no-sidebar' === $layout ) {
		$classes[] = 'no-sidebar content-width-layout';
	}
	elseif ( 'no-sidebar-full-width' === $layout ) {
		$classes[] = 'no-sidebar full-width-layout';
	} elseif ( 'left-sidebar' === $layout ) {
		if ( '' !== $sidebar ) {
			$classes[] = 'two-columns-layout content-right';
		}
	} elseif ( 'right-sidebar' === $layout ) {
		if ( '' !== $sidebar ) {
			$classes[] = 'two-columns-layout content-left';
		}
	}

	$header_media_title    = get_theme_mod( 'catch_sketch_header_media_title' );
	$header_media_subtitle = get_theme_mod( 'catch_sketch_header_media_subtitle' );
	$header_media_text     = get_theme_mod( 'catch_sketch_header_media_text' );
	$header_media_url      = get_theme_mod( 'catch_sketch_header_media_url', '' );
	$header_media_url_text = get_theme_mod( 'catch_sketch_header_media_url_text' );

	$header_image = catch_sketch_featured_overall_image();

	if ( '' == $header_image ) {
		$classes[] = 'no-header-media-image';
	}

	$header_text_enabled = catch_sketch_has_header_media_text();

	if ( ! $header_text_enabled ) {
		$classes[] = 'no-header-media-text';
	}

	$enable_slider = catch_sketch_check_section( get_theme_mod( 'catch_sketch_slider_option', 'disabled' ) );

	if ( ! $enable_slider ) {
		$classes[] = 'no-featured-slider';
	}

	if ( '' == $header_image && ! $header_text_enabled && ! $enable_slider ) {
		$classes[] = 'content-has-padding-top';
	}

	// Add Color Scheme to Body Class.
	$classes[] = esc_attr( 'color-scheme-' . get_theme_mod( 'color_scheme', 'default' ) );

	return $classes;
}
add_filter( 'body_class', 'catch_sketch_body_classes' );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function catch_sketch_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
add_action( 'wp_head', 'catch_sketch_pingback_header' );

/**
 * Adds testimonial background CSS
 */
function catch_sketch_testimonial_bg_css() {
	$background = get_theme_mod( 'catch_sketch_testimonial_bg_image', trailingslashit( esc_url( get_template_directory_uri() ) ) . 'assets/images/testimonial-bg.png' );

	$css = '';

	if ( $background ) {
		$css = '.testimonials-content-wrapper {
			background-image: url("' . esc_url( $background ) . '");
			background-attachment: scroll;
			background-repeat: no-repeat;
			background-size: cover;
			background-position: center center;
		}';
	}


	if ( '' !== $css ) {
		wp_add_inline_style( 'catch-sketch-style', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'catch_sketch_testimonial_bg_css', 11 );

/**
 * Adds header image overlay for each section
 */
function catch_sketch_header_image_overlay_css() {
	$css = '';

	$overlay = get_theme_mod( 'catch_sketch_header_media_opacity', 0 );

	$overlay_bg = $overlay / 100;

	if ( '' !== $overlay ) {
		$css = '.custom-header:after { background-color: rgba(0, 0, 0, ' . esc_attr( $overlay_bg ) . '); } '; // Dividing by 100 as the option is shown as % for user
	}

	wp_add_inline_style( 'catch-sketch-style', $css );
}
add_action( 'wp_enqueue_scripts', 'catch_sketch_header_image_overlay_css', 11 );

/**
 * Remove first post from blog as it is already show via recent post template
 */
function catch_sketch_alter_home( $query ) {
	if ( $query->is_home() && $query->is_main_query() ) {
		$cats = get_theme_mod( 'catch_sketch_front_page_category' );

		if ( is_array( $cats ) && ! in_array( '0', $cats ) ) {
			$query->query_vars['category__in'] = $cats;
		}
	}
}
add_action( 'pre_get_posts', 'catch_sketch_alter_home' );

/**
 * Function to add Scroll Up icon
 */
function catch_sketch_scrollup() {
	$disable_scrollup = get_theme_mod( 'catch_sketch_display_scrollup', 1 );

	if ( ! $disable_scrollup ) {
		return;
	}

	echo '
		<div class="scrollup">
			<a href="#masthead" id="scrollup" class="fa fa-sort-asc" aria-hidden="true"><span class="screen-reader-text">' . esc_html__( 'Scroll Up', 'catch-sketch' ) . '</span></a>
		</div>' ;
}
add_action( 'wp_footer', 'catch_sketch_scrollup', 1 );

if ( ! function_exists( 'catch_sketch_content_nav' ) ) :
	/**
	 * Display navigation/pagination when applicable
	 *
	 * @since Catch Sketch 1.0
	 */
	function catch_sketch_content_nav() {
		global $wp_query;

		// Don't print empty markup in archives if there's only one page.
		if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) ) {
			return;
		}

		$pagination_type = get_theme_mod( 'catch_sketch_pagination_type', 'default' );

		/**
		 * Check if navigation type is Jetpack Infinite Scroll and if it is enabled, else goto default pagination
		 * if it's active then disable pagination
		 */
		if ( ( 'infinite-scroll' === $pagination_type ) && class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'infinite-scroll' ) ) {
			return false;
		}

		if ( 'numeric' === $pagination_type && function_exists( 'the_posts_pagination' ) ) {
			the_posts_pagination( array(
				'prev_text'          => esc_html__( 'Previous', 'catch-sketch' ),
				'next_text'          => esc_html__( 'Next', 'catch-sketch' ),
				'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Page', 'catch-sketch' ) . ' </span>',
			) );
		} else {
			the_posts_navigation();
		}
	}
endif; // catch_sketch_content_nav

/**
 * Check if a section is enabled or not based on the $value parameter
 * @param  string $value Value of the section that is to be checked
 * @return boolean return true if section is enabled otherwise false
 */
function catch_sketch_check_section( $value ) {
	global $wp_query;

	// Get Page ID outside Loop
	$page_id = absint( $wp_query->get_queried_object_id() );

	// Front page displays in Reading Settings
	$page_for_posts = absint( get_option( 'page_for_posts' ) );

	return ( 'entire-site' == $value  || ( ( is_front_page() || ( is_home() && $page_for_posts !== $page_id ) ) && 'homepage' == $value ) );
}

/**
 * Return the first image in a post. Works inside a loop.
 * @param [integer] $post_id [Post or page id]
 * @param [string/array] $size Image size. Either a string keyword (thumbnail, medium, large or full) or a 2-item array representing width and height in pixels, e.g. array(32,32).
 * @param [string/array] $attr Query string or array of attributes.
 * @return [string] image html
 *
 * @since Catch Sketch 1.0
 */

function catch_sketch_get_first_image( $postID, $size, $attr, $src = false ) {
	ob_start();

	ob_end_clean();

	$image 	= '';

	$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', get_post_field( 'post_content', $postID ) , $matches );

	if( isset( $matches[1][0] ) ) {
		//Get first image
		$first_img = $matches[1][0];

		if ( $src ) {
			//Return url of src is true
			return $first_img;
		}

		return '<img class="pngfix wp-post-image" src="' . $first_img . '">';
	}

	return false;
}

function catch_sketch_get_theme_layout() {
	$layout = '';

	if ( is_page_template( 'templates/no-sidebar.php' ) ) {
		$layout = 'no-sidebar';
	} elseif ( is_page_template( 'templates/right-sidebar.php' ) ) {
		$layout = 'right-sidebar';
	} else {
		$layout = get_theme_mod( 'catch_sketch_default_layout', 'right-sidebar' );

		if ( is_front_page() ) {
			$layout = get_theme_mod( 'catch_sketch_homepage_layout', 'right-sidebar' );
		} elseif ( is_home() || is_archive() || is_search() ) {
			$layout = get_theme_mod( 'catch_sketch_archive_layout', 'right-sidebar' );
		}
	}

	return $layout;
}

function catch_sketch_get_sidebar_id() {
	$sidebar = '';

	$layout = catch_sketch_get_theme_layout();

	$sidebaroptions = '';

	if ( 'no-sidebar-full-width' === $layout || 'no-sidebar' === $layout ) {
		return $sidebar;
	}
		global $post, $wp_query;

		// Front page displays in Reading Settings.
		$page_on_front  = get_option( 'page_on_front' );
		$page_for_posts = get_option( 'page_for_posts' );

		// Get Page ID outside Loop.
		$page_id = $wp_query->get_queried_object_id();

		// Blog Page or Front Page setting in Reading Settings.
		if ( $page_id == $page_for_posts || $page_id == $page_on_front ) {
			$sidebaroptions = get_post_meta( $page_id, 'catch-sketch-sidebar-option', true );
		} elseif ( is_singular() ) {
			if ( is_attachment() ) {
				$parent 		= $post->post_parent;
				$sidebaroptions = get_post_meta( $parent, 'catch-sketch-sidebar-option', true );

			} else {
				$sidebaroptions = get_post_meta( $post->ID, 'catch-sketch-sidebar-option', true );
			}
		}

	if ( is_active_sidebar( 'sidebar-1' ) ) {
		$sidebar = 'sidebar-1'; // Primary Sidebar.
	}

	return $sidebar;
}

if ( ! function_exists( 'catch_sketch_get_no_thumb_image' ) ) :
	/**
	 * $image_size post thumbnail size
	 * $type image, src
	 */
	function catch_sketch_get_no_thumb_image( $image_size = 'post-thumbnail', $type = 'image' ) {
		$image = $image_url = '';

		global $_wp_additional_image_sizes;

		$size = $_wp_additional_image_sizes['post-thumbnail'];

		if ( isset( $_wp_additional_image_sizes[ $image_size ] ) ) {
			$size = $_wp_additional_image_sizes[ $image_size ];
		}

		$image_url  = trailingslashit( get_template_directory_uri() ) . 'assets/images/no-thumb.jpg';

		if ( 'post-thumbnail' !== $image_size ) {
			$image_url  = trailingslashit( get_template_directory_uri() ) . 'assets/images/no-thumb-' . $size['width'] . 'x' . $size['height'] . '.jpg';
		}

		if ( 'src' === $type ) {
			return $image_url;
		}

		return '<img class="no-thumb ' . esc_attr( $image_size ) . '" src="' . esc_url( $image_url ) . '" />';
	}
endif;

/**
 * Featured content posts
 */
function catch_sketch_get_featured_posts() {
	$number = get_theme_mod( 'catch_sketch_featured_content_number', 3 );

	$post_list    = array();

	$args = array(
		'posts_per_page'      => $number,
		'post_type'           => 'post',
		'ignore_sticky_posts' => 1, // ignore sticky posts.
	);

	// Get valid number of posts.

	$args['post_type'] = 'featured-content';

	for ( $i = 1; $i <= $number; $i++ ) {
		$post_id = '';

		$post_id = get_theme_mod( 'catch_sketch_featured_content_cpt_' . $i );
		

		if ( $post_id && '' !== $post_id ) {
			$post_list = array_merge( $post_list, array( $post_id ) );
		}
	}

	$args['post__in'] = $post_list;
	$args['orderby']  = 'post__in';
	

	$featured_posts = get_posts( $args );

	return $featured_posts;
}


/**
 * Services content posts
 */
function catch_sketch_get_services_posts() {
	$type = 'ect-service';

	$number = get_theme_mod( 'catch_sketch_service_number', 6 );

	$post_list    = array();

	$args = array(
		'posts_per_page'      => $number,
		'post_type'           => 'post',
		'ignore_sticky_posts' => 1, // ignore sticky posts.
	);

	// Get valid number of posts.

	$args['post_type'] = $type;

	for ( $i = 1; $i <= $number; $i++ ) {
		$post_id = '';

		$post_id = get_theme_mod( 'catch_sketch_service_cpt_' . $i );
		

		if ( $post_id && '' !== $post_id ) {
			$post_list = array_merge( $post_list, array( $post_id ) );
		}
	}

	$args['post__in'] = $post_list;
	$args['orderby']  = 'post__in';
	

	$services_posts = get_posts( $args );

	return $services_posts;
}

if ( ! function_exists( 'catch_sketch_sections' ) ) :
	/**
	 * Display Sections on header
	 */
	function catch_sketch_sections( $selector = 'header' ) {
		get_template_part( 'template-parts/header/header', 'media' );
		get_template_part( 'template-parts/slider/content', 'display' );
		get_template_part( 'template-parts/hero-content/content','hero' );
		get_template_part( 'template-parts/services/display', 'services' );
		get_template_part( 'template-parts/portfolio/display', 'portfolio' );
		get_template_part( 'template-parts/testimonials/display', 'testimonial' );
		get_template_part( 'template-parts/featured-content/display', 'featured' );
	}
endif;
