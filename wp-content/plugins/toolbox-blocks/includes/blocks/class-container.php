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
		$tag      = self::allowed_tag(
			$attributes['tagName'] ?? 'div',
			'div',
			array( 'div', 'section', 'article', 'aside', 'header', 'footer', 'main', 'nav', 'span' )
		);
		$meta     = self::block_meta( $attributes, 'tb-container', true );
		$unique_id = sanitize_html_class( $attributes['uniqueId'] ?? '' );

		// Inline background image from Settings tab.
		$bg_style = '';
		$bg_url   = $attributes['bgImageUrl'] ?? '';
		if ( $bg_url ) {
			$bg_style = ' style="background-image:url(' . esc_url( $bg_url ) . ')"';
		}

		// Inner wrapper: layout styles target this element so flex/grid
		// applies directly to block content; ensures editor and frontend structure match.
		$inner_class = $unique_id ? 'tb-container__inner tb-' . $unique_id : 'tb-container__inner';

		return sprintf(
			'%1$s<%2$s class="%3$s"%4$s%5$s><div class="%6$s">%7$s</div></%2$s>',
			$meta['style_tag'],
			$tag,
			$meta['class'],
			$meta['anchor'],
			$bg_style,
			esc_attr( $inner_class ),
			$content,
			$tag
		);
	}
}
