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
<body <?php body_class(); ?> data-theme-uri="<?php echo esc_url( trailingslashit( get_theme_file_uri( '' ) ) ); ?>">
<?php wp_body_open(); ?>
<div class="arcade-stars" aria-hidden="true"></div>
<div class="arcade-bottom-scene" aria-hidden="true"></div>
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
		<button class="hud-quarter" type="button" aria-label="Spin coin">
			<span class="hud-quarter-label">Insert Coin</span>
			<span class="hud-quarter-face" aria-hidden="true">
				<img class="hud-coin-frame hud-coin-1" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-1.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
				<img class="hud-coin-frame hud-coin-2" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-2.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
				<img class="hud-coin-frame hud-coin-3" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-3.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
				<img class="hud-coin-frame hud-coin-4" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-4.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
				<img class="hud-coin-frame hud-coin-5" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-5.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
				<img class="hud-coin-frame hud-coin-6" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-6.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
			</span>
		</button>
	</div>
</header>
<main class="site-wrap">
