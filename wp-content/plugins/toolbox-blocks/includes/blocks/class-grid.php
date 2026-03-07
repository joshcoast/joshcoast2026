<?php
/**
 * Grid block render.
 *
 * @package ToolboxBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Toolbox_Block_Grid extends Toolbox_Block_Base {

	const BLOCK_NAME = 'toolbox-blocks/grid';

	public static function render( $attributes, $content, $block ) {
		$tag  = tag_escape( $attributes['tagName'] ?? 'div' );
		$meta = self::block_meta( $attributes, 'tb-grid' );

		return sprintf(
			'%s<%s class="%s"%s>%s</%s>',
			$meta['style_tag'],
			$tag,
			$meta['class'],
			$meta['anchor'],
			$content,
			$tag
		);
	}
}
