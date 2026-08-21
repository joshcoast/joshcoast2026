<?php
/**
 * Single client template.
 *
 * @package jc_16bit_arcade
 */

get_header();
?>
<section class="site-panel site-content client-single-layout">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="client-hero-media">
					<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'decoding' => 'async' ) ); ?>
				</figure>
			<?php endif; ?>

			<article <?php post_class( 'client-case' ); ?>>
				<h1 class="section-title"><?php the_title(); ?></h1>
				<p class="meta client-meta">
					<?php esc_html_e( 'Published', 'jc-16bit-arcade' ); ?> <?php echo esc_html( get_the_date() ); ?>
					<?php if ( get_the_modified_time( 'U' ) > get_the_time( 'U' ) ) : ?>
						<span class="client-meta-divider" aria-hidden="true">·</span>
						<?php esc_html_e( 'Updated', 'jc-16bit-arcade' ); ?> <?php echo esc_html( get_the_modified_date() ); ?>
					<?php endif; ?>
				</p>
				<?php if ( has_excerpt() ) : ?>
					<p class="client-deck"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<div class="entry-content"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php endif; ?>
</section>
<?php
get_footer();
