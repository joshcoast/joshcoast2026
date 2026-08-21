<?php
/**
 * Footer template.
 *
 * @package jc_16bit_arcade
 */

$linkedin_url = apply_filters( 'jc_16bit_arcade_linkedin_url', 'https://www.linkedin.com/' );
?>
</main>
<footer class="site-footer">
	<div class="site-wrap">
		<p class="ticker"><span><button class="ticker-coin-trigger" type="button" aria-label="Insert coin to spawn aliens">INSERT COIN TO CONTINUE</button> • THANKS FOR VISITING THE PORTFOLIO ARCADE • <a href="<?php echo esc_url( $linkedin_url ); ?>" target="_blank" rel="noopener noreferrer">PRESS START TO HIRE</a> •</span></p>
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. Built with WordPress and unapologetic neon enthusiasm.</p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
