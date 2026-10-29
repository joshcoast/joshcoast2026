<?php
/**
 * Footer template.
 *
 * @package jc_16bit_arcade
 */

$linkedin_url = apply_filters( 'jc_16bit_arcade_linkedin_url', 'https://www.linkedin.com/' );
?>
</main>
<footer class="jc-footer">
	<div class="jc-wrap">
		<p class="jc-ticker"><span><button class="jc-ticker__coin" type="button" aria-label="Insert coin to spawn aliens">INSERT COIN TO CONTINUE</button> • THANKS FOR VISITING THE PORTFOLIO ARCADE • <a href="<?php echo esc_url( $linkedin_url ); ?>" target="_blank" rel="noopener noreferrer">PRESS START TO HIRE</a> •</span></p>
		<p class="jc-footer__tagline">
			<span class="jc-footer__tagline-copy jc-footer__tagline-copy--arcade">© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. Built with WordPress and unapologetic neon enthusiasm.</span>
			<span class="jc-footer__tagline-copy jc-footer__tagline-copy--stripes">© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. Built with WordPress, vintage shapes, and a suspicious amount of earth-tone confidence.</span>
			<span class="jc-footer__tagline-copy jc-footer__tagline-copy--editorial">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Josh Coast. Designed and built in WordPress.</span>
		</p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
