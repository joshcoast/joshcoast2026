<?php
/**
 * Plugin Name: Coast Blocks
 * Description: Custom blocks with shared controls (spacing, etc.) — All screens, Medium, and Small breakpoints.
 * Version: 1.0.0
 * Author: Josh Coast
 * Text Domain: coast-blocks
 *
 * @package CoastBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'COAST_BLOCKS_VERSION', '1.0.0' );
define( 'COAST_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'COAST_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

require_once COAST_BLOCKS_DIR . 'includes/class-coast-blocks-spacing.php';

add_action( 'init', 'coast_blocks_register_blocks' );

/**
 * Register all Coast Blocks (editor script registers block types in JS; we add render_callback here).
 */
function coast_blocks_register_blocks() {
	$asset = file_exists( COAST_BLOCKS_DIR . 'build/index.asset.php' )
		? require COAST_BLOCKS_DIR . 'build/index.asset.php'
		: array( 'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ), 'version' => COAST_BLOCKS_VERSION );

	wp_register_script(
		'coast-blocks-editor',
		COAST_BLOCKS_URL . 'build/index.js',
		$asset['dependencies'],
		$asset['version'] ?? COAST_BLOCKS_VERSION
	);

	if ( file_exists( COAST_BLOCKS_DIR . 'build/index.css' ) ) {
		wp_register_style(
			'coast-blocks-editor',
			COAST_BLOCKS_URL . 'build/index.css',
			array(),
			COAST_BLOCKS_VERSION
		);
	}

	register_block_type( 'coast-blocks/container', array(
		'editor_script' => 'coast-blocks-editor',
		'editor_style'  => 'coast-blocks-editor',
	) );
}

add_filter( 'render_block', 'coast_blocks_collect_container_css', 10, 3 );

/**
 * Collected block CSS (filled during render, printed in wp_footer).
 *
 * @var string
 */
$GLOBALS['coast_blocks_collected_css'] = '';

/**
 * Collect spacing CSS when a Container block is rendered (block output is saved HTML; we only inject CSS).
 *
 * @param string   $block_content Block output.
 * @param array    $block        Parsed block (unused; we use $block_instance).
 * @param WP_Block $block_instance Block instance.
 * @return string Unchanged block content.
 */
function coast_blocks_collect_container_css( $block_content, $block, $block_instance ) {
	if ( ! isset( $block_instance->block_type->name ) || $block_instance->block_type->name !== 'coast-blocks/container' ) {
		return $block_content;
	}

	$attrs = isset( $block_instance->parsed_block['attrs'] ) && is_array( $block_instance->parsed_block['attrs'] )
		? $block_instance->parsed_block['attrs']
		: array();

	$unique_id = $attrs['uniqueId'] ?? '';
	if ( $unique_id === '' && ! empty( $attrs['spacing'] ) ) {
		$unique_id = 'cb-' . substr( md5( wp_json_encode( $block_instance->parsed_block ) ), 0, 8 );
	}

	$id_attr = $unique_id ? 'cb-container-' . $unique_id : '';
	if ( $id_attr !== '' ) {
		$selector    = '#' . $id_attr;
		$spacing_css = Coast_Blocks_Spacing::get_css( $attrs, $selector );
		if ( $spacing_css !== '' ) {
			$GLOBALS['coast_blocks_collected_css'] .= $spacing_css;
		}
	}

	return $block_content;
}

/**
 * Print collected Coast Blocks CSS (only once). Called from footer or, if no footer, after content.
 */
function coast_blocks_print_collected_css() {
	static $printed = false;
	if ( $printed ) {
		return;
	}
	$css = isset( $GLOBALS['coast_blocks_collected_css'] ) ? $GLOBALS['coast_blocks_collected_css'] : '';
	if ( $css === '' ) {
		return;
	}
	$printed = true;
	$css = wp_strip_all_tags( $css );
	$css = str_replace( array( '</style>', '<style' ), array( '<\/style>', '<style' ), $css );
	echo '<style id="coast-blocks-inline-css" type="text/css">' . $css . "</style>\n";
}
add_action( 'wp_footer', 'coast_blocks_print_collected_css', 5 );
// Fallback: if theme doesn't call wp_footer, print after the content (blocks have run by then).
add_filter( 'the_content', 'coast_blocks_append_collected_css', 999 );
function coast_blocks_append_collected_css( $content ) {
	ob_start();
	coast_blocks_print_collected_css();
	$style = ob_get_clean();
	return $content . $style;
}
