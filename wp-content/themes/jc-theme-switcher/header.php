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
$style_scheme = jc_16bit_arcade_get_style_scheme();
?>
<body <?php body_class(); ?> data-style-scheme="<?php echo esc_attr( $style_scheme ); ?>" data-theme-uri="<?php echo esc_url( trailingslashit( get_theme_file_uri( '' ) ) ); ?>">
<?php wp_body_open(); ?>
<div class="jc-stars" aria-hidden="true"></div>
<div class="jc-bottom-scene" aria-hidden="true"></div>
<header class="jc-topbar">
	<div class="jc-wrap jc-topbar__inner">
		<div class="jc-topbar__branding">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php bloginfo( 'name' ); ?>
			</a>
		</div>
		<nav class="jc-topbar__nav" aria-label="Primary Menu">
			<?php
			$menu_args      = array(
				'theme_location' => 'primary',
				'container'      => false,
				'fallback_cb'    => 'jc_16bit_arcade_primary_menu_fallback',
			);
			$menu_locations = get_nav_menu_locations();

			if ( empty( $menu_locations['primary'] ) ) {
				$main_menu = wp_get_nav_menu_object( 'Main Menu' );

				if ( $main_menu && ! is_wp_error( $main_menu ) ) {
					$menu_args['menu'] = $main_menu;
				}
			}

			wp_nav_menu(
				$menu_args
			);
			?>
		</nav>
		<div class="jc-theme-switcher" role="group" aria-label="Choose a visual style">
			<button class="jc-theme-switcher__button <?php echo 'arcade' === $style_scheme ? 'is-active' : ''; ?>" type="button" data-style-scheme="arcade" aria-pressed="<?php echo 'arcade' === $style_scheme ? 'true' : 'false'; ?>">Arcade</button>
			<button class="jc-theme-switcher__button <?php echo 'stripes' === $style_scheme ? 'is-active' : ''; ?>" type="button" data-style-scheme="stripes" aria-pressed="<?php echo 'stripes' === $style_scheme ? 'true' : 'false'; ?>">Stripes</button>
		</div>
		<button class="jc-coin" type="button" aria-label="Spin coin">
			<span class="jc-coin__label">Insert Coin</span>
			<span class="jc-coin__face" aria-hidden="true">
				<img class="jc-coin__frame jc-coin__frame--1" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-1.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
				<img class="jc-coin__frame jc-coin__frame--2" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-2.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
				<img class="jc-coin__frame jc-coin__frame--3" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-3.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
				<img class="jc-coin__frame jc-coin__frame--4" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-4.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
				<img class="jc-coin__frame jc-coin__frame--5" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-5.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
				<img class="jc-coin__frame jc-coin__frame--6" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/coin-6.svg' ) ); ?>" alt="" loading="lazy" decoding="async" />
			</span>
		</button>
	</div>
</header>
<main class="jc-wrap">
