<?php
/**
 * The main template file - Child theme override
 *
 * @package GeneratePress Child
 */

get_header(); ?>

	<div <?php generate_do_attr( 'content' ); ?>>
		<main <?php generate_do_attr( 'main' ); ?>>
			<?php
			/**
			 * generate_before_main_content hook.
			 */
			do_action( 'generate_before_main_content' );

			if ( generate_has_default_loop() ) {
				if ( have_posts() ) :

					/**
					 * generate_before_loop hook.
					 */
					do_action( 'generate_before_loop', 'index' );

					while ( have_posts() ) :

						the_post();

						// Use our custom content template
						get_template_part( 'content', get_post_format() );

					endwhile;

					/**
					 * generate_after_loop hook.
					 */
					do_action( 'generate_after_loop', 'index' );

				else :

					get_template_part( 'no-results', 'index' );

				endif;
			}

			/**
			 * generate_after_main_content hook.
			 */
			do_action( 'generate_after_main_content' );
			?>
		</main>
	</div>

	<?php
	/**
	 * generate_after_primary_content_area hook.
	 */
	do_action( 'generate_after_primary_content_area' );

	get_footer();