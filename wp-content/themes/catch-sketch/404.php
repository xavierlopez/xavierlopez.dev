<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Catch Sketch
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<div class="singular-content-wrap">
				<section class="error-404 not-found">
					<div class="page-content">

						<?php
						$header_image = catch_sketch_featured_overall_image();

						if ( ! $header_image ) : ?>

						<header class="page-header">
							<h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'catch-sketch' ); ?></h1>
						</header><!-- .page-header -->

						<p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'catch-sketch' ); ?></p>

						<?php endif; ?>
						<?php
							get_search_form();
						?>
					</div><!-- .page-content -->
				</section><!-- .error-404 -->
			</div> <!-- .singular-content-wrap -->
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
