<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Catch Sketch
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="hentry-inner">
		<?php if ( has_post_thumbnail() ) : ?>
		<div class="post-thumbnail">
			<a href="<?php the_permalink(); ?>" rel="bookmark">
				<?php the_post_thumbnail( 'catch-sketch-blog' ); ?>
			</a>
		</div>
		<?php endif; ?>

		<div class="entry-container">
			<?php if ( is_sticky() ) : ?>
			<span class="sticky-label"><?php esc_html_e( 'Featured', 'catch-sketch' ); ?></span>
			<?php endif; ?>

			<header class="entry-header">
				<?php
				if ( is_singular() ) :
					the_title( '<h1 class="entry-title">', '</h1>' );
				else :
					the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
				endif;

				if ( 'post' === get_post_type() ) :
					?>
					<div class="entry-meta">
						<?php catch_sketch_blog_entry_meta_left(); ?>
						<?php catch_sketch_blog_entry_meta_right(); ?>
					</div><!-- .entry-meta -->
					<?php
				endif; ?>
			</header><!-- .entry-header -->

			<div class="entry-summary"><?php the_excerpt(); ?></div><!-- .entry-summary -->
		</div> <!-- .entry-container -->
	</div> <!-- .hentry-inner -->
</article><!-- #post-<?php the_ID(); ?> -->
