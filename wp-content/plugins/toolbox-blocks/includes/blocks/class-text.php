<?php
/**
 * Text block render.
 *
 * @package ToolboxBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Toolbox_Block_Text extends Toolbox_Block_Base {

	const BLOCK_NAME = 'toolbox-blocks/text';

	public static function render( $attributes, $content, $block ) {
		$tag      = self::allowed_tag(
			$attributes['tagName'] ?? 'p',
			'p',
			array( 'p', 'div', 'span', 'li' )
		);
		$meta     = self::block_meta( $attributes, 'tb-text' );
		$inner    = wp_kses_post( $attributes['content'] ?? '' );

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
