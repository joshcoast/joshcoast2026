<?php
/**
 * Page template.
 *
 * @package jc_16bit_arcade
 */

get_header();
?>
<section class="arcade-panel arcade-content">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<h1 class="section-title"><?php the_title(); ?></h1>
				<div class="entry-content"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php endif; ?>
</section>
<?php
get_footer();
