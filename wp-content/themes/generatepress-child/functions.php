<?php
/**
 * GeneratePress Child Theme Functions
 */

// Enqueue child theme styles
function generatepress_child_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'parent-style' ) );
}
add_action( 'wp_enqueue_scripts', 'generatepress_child_enqueue_styles' );

/**
 * Block editor only: load overrides so they apply after Lottie (and fix CodePro conflict).
 */
function generatepress_child_enqueue_editor_overrides() {
	wp_enqueue_style(
		'generatepress-child-editor-overrides',
		get_stylesheet_directory_uri() . '/editor-overrides.css',
		array( 'lottiefiles-style-css' ), // Load after Lottie so overrides win
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'enqueue_block_editor_assets', 'generatepress_child_enqueue_editor_overrides', 20 );

/**
 * Add global color 7 to the block editor palette and define the CSS variable.
 */
function generatepress_child_add_global_color_7() {
	$palette = get_theme_support( 'editor-color-palette' );
	if ( ! empty( $palette[0] ) && is_array( $palette[0] ) ) {
		$palette[0][] = array(
			'name'  => __( 'Global 7', 'generatepress-child' ),
			'slug'  => 'global-color-7',
			'color' => 'var(--global-color-7)',
		);
		remove_theme_support( 'editor-color-palette' );
		add_theme_support( 'editor-color-palette', $palette[0] );
	}
}
add_action( 'after_setup_theme', 'generatepress_child_add_global_color_7', 11 );

function generatepress_child_output_global_color_7() {
	$css = ':root { --global-color-7: #17e8e8; }';
	wp_add_inline_style( 'generatepress-child-editor-overrides', $css );
}
add_action( 'enqueue_block_editor_assets', 'generatepress_child_output_global_color_7', 21 );

// Note: WP Blog Category Filter plugin must be activated separately
// The theme will automatically use the plugin's functions when available
