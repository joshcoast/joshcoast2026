<?php
/**
 * Plugin Name: Toolbox Blocks
 * Description: A suite of blocks (Container, Grid, Text, Headline, Button, Image, Query) with full responsive style controls.
 * Version: 1.0.0
 * Author: JoshCoast
 * Text Domain: toolbox-blocks
 * Requires at least: 6.5
 * Requires PHP: 8.0
 *
 * @package ToolboxBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TOOLBOX_BLOCKS_VERSION', '1.0.0' );
define( 'TOOLBOX_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'TOOLBOX_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

// Core infrastructure.
require_once TOOLBOX_BLOCKS_DIR . 'includes/class-css-generator.php';
require_once TOOLBOX_BLOCKS_DIR . 'includes/class-block-base.php';

// Block render classes.
require_once TOOLBOX_BLOCKS_DIR . 'includes/blocks/class-container.php';
require_once TOOLBOX_BLOCKS_DIR . 'includes/blocks/class-grid.php';
require_once TOOLBOX_BLOCKS_DIR . 'includes/blocks/class-text.php';
require_once TOOLBOX_BLOCKS_DIR . 'includes/blocks/class-headline.php';
require_once TOOLBOX_BLOCKS_DIR . 'includes/blocks/class-button.php';
require_once TOOLBOX_BLOCKS_DIR . 'includes/blocks/class-image.php';
require_once TOOLBOX_BLOCKS_DIR . 'includes/blocks/class-query.php';

add_action( 'init', 'toolbox_blocks_register' );

/**
 * Register the editor script/style and all block types.
 */
function toolbox_blocks_register() {
	$asset_file = TOOLBOX_BLOCKS_DIR . 'build/index.asset.php';
	$asset = file_exists( $asset_file )
		? require $asset_file
		: array(
			'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data' ),
			'version'      => TOOLBOX_BLOCKS_VERSION,
		);

	wp_register_script(
		'toolbox-blocks-editor',
		TOOLBOX_BLOCKS_URL . 'build/index.js',
		$asset['dependencies'],
		$asset['version'] ?? TOOLBOX_BLOCKS_VERSION
	);

	if ( file_exists( TOOLBOX_BLOCKS_DIR . 'build/index.css' ) ) {
		wp_register_style(
			'toolbox-blocks-editor',
			TOOLBOX_BLOCKS_URL . 'build/index.css',
			array(),
			TOOLBOX_BLOCKS_VERSION
		);
	}

	// Register block category.
	// (Block categories filter is separate; handled below.)

	// Register each block type (render_callback for dynamic blocks).
	Toolbox_Block_Container::register();
	Toolbox_Block_Grid::register();
	Toolbox_Block_Text::register();
	Toolbox_Block_Headline::register();
	Toolbox_Block_Button::register();
	Toolbox_Block_Image::register();
	Toolbox_Block_Query::register();

	// Attach the editor script/style to every registered block.
	$blocks = array(
		'toolbox-blocks/container',
		'toolbox-blocks/grid',
		'toolbox-blocks/text',
		'toolbox-blocks/headline',
		'toolbox-blocks/button',
		'toolbox-blocks/image',
		'toolbox-blocks/query',
	);
	foreach ( $blocks as $block_name ) {
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
		if ( $block_type ) {
			$block_type->editor_script_handles = array( 'toolbox-blocks-editor' );
			$block_type->editor_style_handles  = array( 'toolbox-blocks-editor' );
		}
	}
}

add_filter( 'block_categories_all', 'toolbox_blocks_add_category', 10, 2 );

/**
 * Add a "Toolbox Blocks" category to the block inserter.
 *
 * @param array $categories Existing categories.
 * @return array
 */
function toolbox_blocks_add_category( $categories ) {
	return array_merge(
		array(
			array(
				'slug'  => 'toolbox-blocks',
				'title' => __( 'Toolbox Blocks', 'toolbox-blocks' ),
				'icon'  => 'waves',
			),
		),
		$categories
	);
}
