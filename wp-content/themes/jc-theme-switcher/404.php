<?php
/**
 * 404 template.
 *
 * @package jc_16bit_arcade
 */

get_header();
?>
<section class="jc-panel">
	<h1 class="jc-section-title">404: SECRET LEVEL NOT FOUND</h1>
	<p>The page vanished like a power-up grabbed by someone else.</p>
	<p><a class="jc-btn jc-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">RETURN TO START SCREEN</a></p>
</section>
<?php
get_footer();
