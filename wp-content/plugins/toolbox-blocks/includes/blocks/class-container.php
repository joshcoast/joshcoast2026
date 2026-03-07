<?php
/**
 * Container block render.
 *
 * @package ToolboxBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Toolbox_Block_Container extends Toolbox_Block_Base {

	const BLOCK_NAME = 'toolbox-blocks/container';

	public static function render( $attributes, $content, $block ) {
		$tag     = tag_escape( $attributes['tagName'] ?? 'div' );
		$meta    = self::block_meta( $attributes, 'tb-container' );

		// Inline background image from Settings tab.
		$bg_style = '';
		$bg_url   = $attributes['bgImageUrl'] ?? '';
		if ( $bg_url ) {
			$bg_style = ' style="background-image:url(' . esc_url( $bg_url ) . ')"';
		}

		return sprintf(
			'%s<%s class="%s"%s%s>%s</%s>',
			$meta['style_tag'],
			$tag,
			$meta['class'],
			$meta['anchor'],
			$bg_style,
			$content,
			$tag
		);
	}
}
