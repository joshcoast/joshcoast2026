<?php
/**
 * 404 template.
 *
 * @package jc_16bit_arcade
 */

get_header();
?>
<section class="arcade-panel">
	<h1 class="section-title">404: SECRET LEVEL NOT FOUND</h1>
	<p>The page vanished like a power-up grabbed by someone else.</p>
	<p><a class="btn-arcade btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">RETURN TO START SCREEN</a></p>
</section>
<?php
get_footer();
