<?php
/**
 * Single post template.
 *
 * @package jc_16bit_arcade
 */

get_header();
?>
<section class="jc-panel jc-content jc-single-post-view">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'jc-single-post' ); ?>>
				<?php jc_16bit_arcade_render_single_post_hero(); ?>
				<div class="jc-single-post__body">
					<h1 class="jc-section-title"><?php the_title(); ?></h1>
					<p class="jc-meta"><?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?></p>
					<?php jc_16bit_arcade_render_category_icons(); ?>
					<div class="entry-content"><?php the_content(); ?></div>
				</div>
			</article>

			<?php
			$jc_nav_items = array_filter(
				array(
					'previous' => jc_16bit_arcade_get_adjacent_post( 'previous' ),
					'next'     => jc_16bit_arcade_get_adjacent_post( 'next' ),
				)
			);

			if ( $jc_nav_items ) :
				?>
				<nav class="jc-project-nav" aria-label="<?php esc_attr_e( 'More posts', 'jc-16bit-arcade' ); ?>">
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
								} else {
									$jc_image = jc_16bit_arcade_get_post_category_image( $jc_target->ID );

									echo '<img src="' . esc_url( $jc_image['url'] ) . '" alt="" loading="lazy" decoding="async"';
									foreach ( $jc_image['urls'] as $jc_scheme => $jc_url ) {
										echo ' data-style-scheme-src-' . esc_attr( $jc_scheme ) . '="' . esc_url( $jc_url ) . '"';
									}
									echo ' />';
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
										? esc_html__( 'Previous post', 'jc-16bit-arcade' )
										: esc_html__( 'Next post', 'jc-16bit-arcade' );
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
