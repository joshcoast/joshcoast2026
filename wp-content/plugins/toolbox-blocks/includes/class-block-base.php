<?php
/**
 * Base class for Toolbox Blocks render callbacks.
 *
 * @package ToolboxBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Toolbox_Block_Base {

	/**
	 * Block name (e.g. 'toolbox-blocks/container').
	 *
	 * @var string
	 */
	const BLOCK_NAME = '';

	/**
	 * Register this block type.
	 */
	public static function register() {
		if ( ! static::BLOCK_NAME ) {
			return;
		}
		register_block_type( static::BLOCK_NAME, array(
			'render_callback' => array( static::class, 'render' ),
		) );
	}

	/**
	 * Render callback. Each subclass implements this.
	 *
	 * @param array    $attributes Block attributes.
	 * @param string   $content    Inner blocks HTML.
	 * @param WP_Block $block      Block instance.
	 * @return string
	 */
	abstract public static function render( $attributes, $content, $block );

	/**
	 * Helper: build the CSS class string and inline style tag.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $extra_class Additional class(es).
	 * @return array { 'class' => string, 'style_tag' => string }
	 */
	protected static function block_meta( $attributes, $extra_class = '' ) {
		$unique_id   = $attributes['uniqueId'] ?? '';
		$extra_class = trim( ( $extra_class ? $extra_class . ' ' : '' ) . ( $attributes['extraClasses'] ?? '' ) );
		$anchor      = $attributes['htmlAnchor'] ?? '';

		$class_parts = array_filter( array(
			'tb-block',
			$extra_class,
			$unique_id ? 'tb-' . $unique_id : '',
		) );
		$classes = implode( ' ', $class_parts );

		$styles    = $attributes['styles'] ?? array();
		$style_tag = $unique_id ? Toolbox_Blocks_CSS_Generator::style_tag( $unique_id, $styles ) : '';

		return array(
			'class'     => esc_attr( $classes ),
			'style_tag' => $style_tag,
			'anchor'    => $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '',
		);
	}
}
