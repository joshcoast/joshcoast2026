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
		$tag       = self::allowed_tag( $attributes['tagName'] ?? 'div', array( 'div', 'section', 'ul', 'ol' ), 'div' );
		$meta      = self::block_meta( $attributes, 'tb-grid', true );
		$unique_id = sanitize_html_class( $attributes['uniqueId'] ?? '' );

		// Inner wrapper: layout styles target this element so flex/grid
		// applies directly to block content; ensures editor and frontend structure match.
		$inner_class = $unique_id ? 'tb-grid__inner tb-' . $unique_id : 'tb-grid__inner';

		return sprintf(
			'%1$s<%2$s class="%3$s"%4$s><div class="%5$s">%6$s</div></%2$s>',
			$meta['style_tag'],
			$tag,
			$meta['class'],
			$meta['anchor'],
			esc_attr( $inner_class ),
			$content,
			$tag
		);
	}
}
