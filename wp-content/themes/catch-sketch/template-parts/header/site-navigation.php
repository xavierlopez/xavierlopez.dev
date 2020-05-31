<?php
/**
 * Primary Menu Template
 *
 * @package Catch Sketch
 */
?>
<div id="site-header-menu" class="site-header-menu">
	<div id="primary-menu-wrapper" class="menu-wrapper">

		<div class="header-overlay"></div>

		<div class="menu-toggle-wrapper">
			<button id="menu-toggle" class="menu-toggle" aria-controls="top-menu" aria-expanded="false">
				<div class="menu-bars">
					<div class="bars bar1"></div>
	  				<div class="bars bar2"></div>
	  				<div class="bars bar3"></div>
  				</div>
				<span class="menu-label"><?php echo esc_html_e( 'Menu', 'catch-sketch' ); ?></span>
			</button>
		</div><!-- .menu-toggle-wrapper -->

		<div class="menu-inside-wrapper">
			<?php
				if( function_exists( 'catch_sketch_header_cart' ) ) {
					catch_sketch_header_cart();
				}
				?>

				<?php get_template_part( 'template-parts/header/header', 'navigation' ); ?>

					<div class="mobile-social-search">
						<div class="search-container">
							<?php get_search_form(); ?>
						</div>
					</div><!-- .mobile-social-search -->
		</div><!-- .menu-inside-wrapper -->
	</div><!-- #primary-menu-wrapper.menu-wrapper -->

	<div class="search-social-container">
		<div id="primary-search-wrapper" class="menu-wrapper">
			<div class="menu-toggle-wrapper">
				<button id="social-search-toggle" class="menu-toggle">
					<span class="menu-label screen-reader-text"><?php echo esc_html_e( 'Search', 'catch-sketch' ); ?></span>
				</button>
			</div><!-- .menu-toggle-wrapper -->

			<div class="menu-inside-wrapper">
				<div class="search-container">
					<?php get_Search_form(); ?>
				</div>
			</div><!-- .menu-inside-wrapper -->
		</div><!-- #social-search-wrapper.menu-wrapper -->
	</div> <!-- .search-social-container -->

	<?php get_template_part( 'template-parts/header/social', 'header' ); ?>

	<?php
	if( function_exists( 'catch_sketch_header_cart' ) ) {
		catch_sketch_header_cart();
	}
	?>
</div><!-- .site-header-menu -->


