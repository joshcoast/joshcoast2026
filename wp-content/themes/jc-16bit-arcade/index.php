<?php
/**
 * Index template.
 *
 * @package jc_16bit_arcade
 */

get_header();
?>
<section class="arcade-panel">
	<h1 class="section-title"><?php bloginfo( 'name' ); ?> FEED</h1>
	<div class="card-list">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'card-item' ); ?>>
					<?php jc_16bit_arcade_render_post_card_image(); ?>
					<?php jc_16bit_arcade_render_category_icons(); ?>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p class="meta"><?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?></p>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>
			<div class="arcade-pagination"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p>No content found. The cartridge may need a dramatic blow into it.</p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
