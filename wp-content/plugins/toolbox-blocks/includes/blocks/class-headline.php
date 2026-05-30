<?php
/**
 * Headline block render.
 *
 * @package ToolboxBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Toolbox_Block_Headline extends Toolbox_Block_Base {

	const BLOCK_NAME = 'toolbox-blocks/headline';

	public static function render( $attributes, $content, $block ) {
		$tag   = self::allowed_tag(
			$attributes['tagName'] ?? 'h2',
			'h2',
			array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' )
		);
		$meta  = self::block_meta( $attributes, 'tb-headline' );
		$inner = wp_kses_post( $attributes['content'] ?? '' );

		return sprintf(
			'%s<%s class="%s"%s>%s</%s>',
			$meta['style_tag'],
			$tag,
			$meta['class'],
			$meta['anchor'],
			$inner,
			$tag
		);
	}
}
