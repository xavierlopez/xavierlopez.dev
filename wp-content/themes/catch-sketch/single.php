<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Catch Sketch
 */

get_header(); ?>
	<div id="primary" class="content-area">
			<main id="main" class="site-main">
				<div class="singular-content-wrap">
					<?php
						while ( have_posts() ) : the_post();

						get_template_part( 'template-parts/content/content', 'single' );

						the_post_navigation( array(
				            'prev_text' => '<span class="nav-subtitle">' . __( 'Previous', 'catch-sketch' ) . '</span>' . '<span class="nav-title">'. '%title' .'</span>',
				            'next_text' => '<span class="nav-subtitle">' . __( 'Next', 'catch-sketch' ) . '</span>' . '<span class="nav-title">'.  '%title' .'</span>',
				        ) );

						get_template_part( 'template-parts/content/content', 'comment' );

						endwhile; // End of the loop.
					?>
				</div> <!--  .singular-content-wrap -->
			</main><!-- #main -->
	</div><!-- #primary -->
	<div id="fijo">
		<?php echo do_shortcode( '[contact-form-7 id="4" title="Contact form 1"]' ); ?>
	</div>



<script>
	
document.addEventListener("DOMContentLoaded", function(){

	function adios() {
		document.querySelector('#fijo').style.visibility ="visible";
		cuadro.innerHTML = "Enviado. ¡Gracias!";
	}

	function gracias () {
	
		document.querySelector('#fijo').style.visibility ="hidden";
		setTimeout(adios,500);
	}
	const b = document.querySelector(".wpcf7-submit");
	
	b.addEventListener("click", gracias);

	const cuadro =document.querySelector('#fijo');
	
});
</script>

<?php
get_sidebar();
get_footer();
