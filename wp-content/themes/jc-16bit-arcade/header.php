<?php
/**
 * Header template.
 *
 * @package jc_16bit_arcade
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="arcade-stars" aria-hidden="true"></div>
<header class="arcade-topbar">
	<div class="site-wrap arcade-topbar-inner">
		<div class="site-branding">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php bloginfo( 'name' ); ?>
			</a>
		</div>
		<nav class="arcade-nav" aria-label="Primary Menu">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'fallback_cb'    => '__return_false',
				)
			);
			?>
		</nav>
	</div>
</header>
<main class="site-wrap">
