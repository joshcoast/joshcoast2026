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
<?php
$style_scheme = '';
if ( isset( $_COOKIE['jc_style_scheme'] ) ) {
	$cookie_scheme = sanitize_key( wp_unslash( $_COOKIE['jc_style_scheme'] ) );
	if ( 'neon' === $cookie_scheme ) {
		$cookie_scheme = 'stripes';
	}
	if ( in_array( $cookie_scheme, array( 'arcade', 'stripes' ), true ) ) {
		$style_scheme = $cookie_scheme;
	}
}
?>
<?php $style_attr = '' !== $style_scheme ? ' data-style-scheme="' . esc_attr( $style_scheme ) . '"' : ''; ?>
<body <?php body_class(); ?><?php echo $style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-theme-uri="<?php echo esc_url( trailingslashit( get_theme_file_uri( '' ) ) ); ?>">
<?php wp_body_open(); ?>
<div class="arcade-stars" aria-hidden="true"></div>
<div class="arcade-bottom-scene" aria-hidden="true"></div>
<header class="site-topbar">
	<div class="site-wrap site-topbar-inner">
		<div class="site-branding">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php bloginfo( 'name' ); ?>
			</a>
		</div>
		<nav class="site-nav" aria-label="Primary Menu">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'fallback_cb'    => 'jc_16bit_arcade_primary_menu_fallback',
				)
			);
			?>
		</nav>
		<div class="theme-switcher" role="group" aria-label="Choose a visual style">
			<button class="theme-switcher__button <?php echo 'arcade' === $style_scheme ? 'is-active' : ''; ?>" type="button" data-style-scheme="arcade" aria-pressed="<?php echo 'arcade' === $style_scheme ? 'true' : 'false'; ?>">Arcade</button>
			<button class="theme-switcher__button <?php echo 'stripes' === $style_scheme ? 'is-active' : ''; ?>" type="button" data-style-scheme="stripes" aria-pressed="<?php echo 'stripes' === $style_scheme ? 'true' : 'false'; ?>">Stripes</button>
		</div>
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
