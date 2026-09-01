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
		<?php endwhile; ?>
	<?php endif; ?>
</section>
<?php
get_footer();
