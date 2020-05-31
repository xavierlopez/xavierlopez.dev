<div id="site-generator">
	<div class="wrapper">

		<?php get_template_part( 'template-parts/footer/social', 'footer' ); ?>

		<div class="site-info">
			<?php
		        $theme_data = wp_get_theme();

		        // @remove Remove this check when WP 5.5 is released
		        if ( function_exists( 'wp_date' ) ) {
		        	$date = wp_date( __( 'Y', 'catch-sketch' ) );
		        } else {
		        	$date = date_i18n( __( 'Y', 'catch-sketch' ) );
		        }

		        $footer_text = sprintf( _x( 'Copyright &copy; %1$s %2$s. All Rights Reserved. %3$s', '1: Year, 2: Site Title with home URL, 3: Privacy Policy Link', 'catch-sketch' ), esc_attr( $date ), '<a href="'. esc_url( home_url( '/' ) ) .'">'. esc_attr( get_bloginfo( 'name', 'display' ) ) . '</a>', get_the_privacy_policy_link() ) . ' &#124; ' .'</a>';

		        echo wp_kses_post( $footer_text );
		    ?>
		</div> <!-- .site-info -->
	</div> <!-- .wrapper -->
</div><!-- #site-generator -->
