<?php
/**
 * The template for displaying portfolio items
 *
 * @package Catch_Sketch
 */
?>

<?php
$number = get_theme_mod( 'catch_sketch_portfolio_number', 6 );

if ( ! $number ) {
	// If number is 0, then this section is disabled
	return;
}

$args = array(
	'orderby'             => 'post__in',
	'ignore_sticky_posts' => 1 // ignore sticky posts
);

$post_list  = array();// list of valid post/page ids

$catch_sketch_type = 'jetpack-portfolio';


$args['post_type'] = $catch_sketch_type;

for ( $i = 1; $i <= $number; $i++ ) {
	$catch_sketch_post_id = '';

	$catch_sketch_post_id =  get_theme_mod( 'catch_sketch_portfolio_cpt_' . $i );
	

	if ( $catch_sketch_post_id && '' !== $catch_sketch_post_id ) {
		// Polylang Support.
		if ( class_exists( 'Polylang' ) ) {
			$catch_sketch_post_id = pll_get_post( $catch_sketch_post_id, pll_current_language() );
		}

		$post_list = array_merge( $post_list, array( $catch_sketch_post_id ) );

	}
}

$args['post__in'] = $post_list;

$args['posts_per_page'] = $number;
$loop     = new WP_Query( $args );

$slider_select = get_theme_mod( 'catch_sketch_portfolio_slider', 1 );

if ( $loop -> have_posts() ) :
	while ( $loop -> have_posts() ) :
		$loop -> the_post();

		$show_content = get_theme_mod( 'catch_sketch_portfolio_content_show', 'hide-content' );

		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'grid-item' ); ?>>
			<div class="hentry-inner">
				<?php
				$thumbnail = 'catch-sketch-portfolio';
				?>
				<div class="post-thumbnail">
					<a href="<?php the_permalink(); ?>">
				            <?php
							// Output the featured image.
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'catch-sketch-portfolio' );
							} else {
								echo '<img src="' .  trailingslashit( esc_url( get_template_directory_uri() ) ) . 'assets/images/no-thumb-640x640.jpg"/>';
							}
							?>
					</a>
				</div>

				<div class="entry-container">
					<div class="inner-wrap">
						<header class="entry-header portfolio-entry-header">
							<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>

							<?php 
								echo catch_sketch_entry_category_date();
							 ?>

						</header>
					</div><!-- .inner-wrap -->
				</div><!-- .entry-container -->
			</div><!-- .hentry-inner -->
		</article>
	<?php
	endwhile;
	wp_reset_postdata();
endif;
