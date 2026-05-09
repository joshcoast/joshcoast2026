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
		$slash_pos = strrpos( static::BLOCK_NAME, '/' );
		$name_tail = false !== $slash_pos ? substr( static::BLOCK_NAME, $slash_pos + 1 ) : static::BLOCK_NAME;
		if ( ! $name_tail ) {
			return;
		}

		register_block_type( static::BLOCK_NAME, array(
			'title'           => ucfirst( $name_tail ),
			'category'        => 'toolbox-blocks',
			'keywords'        => array( 'toolbox', 'toolboxblocks', $name_tail ),
			'editor_script'   => 'toolbox-blocks-editor',
			'editor_style'    => 'toolbox-blocks-editor',
			'supports'        => array(
				'anchor'           => true,
				'customClassName'  => true,
			),
			'api_version'     => 3,
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
	 * Sanitizes uniqueId, extraClasses, and htmlAnchor per AGENTS.md security standards.
	 *
	 * @param array  $attributes        Block attributes.
	 * @param string $extra_class       Additional class(es).
	 * @param bool   $layout_has_inner  When true, omit tb-{uniqueId} from outer (styles apply to inner wrapper).
	 * @return array { 'class' => string, 'style_tag' => string, 'anchor' => string }
	 */
	protected static function block_meta( $attributes, $extra_class = '', $layout_has_inner = false ) {
		$unique_id   = sanitize_html_class( $attributes['uniqueId'] ?? '' );
		$extra_raw   = trim( (string) ( $attributes['className'] ?? '' ) );
		$extra_parts = array_filter( array_map( 'trim', explode( ' ', $extra_raw ) ) );
		$extra_safe  = implode( ' ', array_map( 'sanitize_html_class', $extra_parts ) );
		$base_class  = trim( ( $extra_class ? $extra_class . ' ' : '' ) . $extra_safe );
		$anchor      = sanitize_html_class( $attributes['anchor'] ?? '' );

		$class_parts = array_filter( array(
			'tb-block',
			$base_class,
			( ! $layout_has_inner && $unique_id ) ? 'tb-' . $unique_id : '',
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
