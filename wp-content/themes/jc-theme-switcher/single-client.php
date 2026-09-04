<?php
/**
 * Single client template.
 *
 * @package jc_16bit_arcade
 */

get_header();
?>
<section class="jc-panel jc-content jc-case-study-layout">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="jc-case-study__media">
					<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'decoding' => 'async' ) ); ?>
				</figure>
			<?php endif; ?>

			<article <?php post_class( 'jc-case-study' ); ?>>
				<h1 class="jc-section-title"><?php the_title(); ?></h1>
				<p class="jc-meta jc-case-study__meta">
					<?php esc_html_e( 'Published', 'jc-16bit-arcade' ); ?> <?php echo esc_html( get_the_date() ); ?>
					<?php if ( get_the_modified_time( 'U' ) > get_the_time( 'U' ) ) : ?>
						<span class="jc-case-study__divider" aria-hidden="true">·</span>
						<?php esc_html_e( 'Updated', 'jc-16bit-arcade' ); ?> <?php echo esc_html( get_the_modified_date() ); ?>
					<?php endif; ?>
				</p>
				<?php if ( has_excerpt() ) : ?>
					<p class="jc-case-study__deck"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<div class="entry-content"><?php the_content(); ?></div>
			</article>

			<?php
			$jc_nav_items = array_filter(
				array(
					'previous' => jc_16bit_arcade_get_adjacent_client( 'previous' ),
					'next'     => jc_16bit_arcade_get_adjacent_client( 'next' ),
				)
			);

			if ( $jc_nav_items ) :
				?>
				<nav class="jc-project-nav" aria-label="<?php esc_attr_e( 'More client projects', 'jc-16bit-arcade' ); ?>">
					<?php foreach ( $jc_nav_items as $jc_dir => $jc_target ) : ?>
						<a class="jc-project-nav__item jc-project-nav__item--<?php echo esc_attr( $jc_dir ); ?>" href="<?php echo esc_url( get_permalink( $jc_target ) ); ?>" rel="<?php echo 'previous' === $jc_dir ? 'prev' : 'next'; ?>">
							<span class="jc-project-nav__media" aria-hidden="true">
								<?php
								if ( has_post_thumbnail( $jc_target ) ) {
									echo get_the_post_thumbnail(
										$jc_target,
										'medium',
										array(
											'loading'  => 'lazy',
											'decoding' => 'async',
											'alt'      => '',
										)
									);
								}
								?>
							</span>
							<span class="jc-project-nav__copy">
								<span class="jc-project-nav__eyebrow">
									<span class="jc-project-nav__arrow" aria-hidden="true">
										<svg viewBox="0 0 16 16" focusable="false"><path d="M2 8h11M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
									</span>
									<?php
									echo 'previous' === $jc_dir
										? esc_html__( 'Previous project', 'jc-16bit-arcade' )
										: esc_html__( 'Next project', 'jc-16bit-arcade' );
									?>
								</span>
								<span class="jc-project-nav__title"><?php echo esc_html( get_the_title( $jc_target ) ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php endif; ?>
</section>
<?php
get_footer();
