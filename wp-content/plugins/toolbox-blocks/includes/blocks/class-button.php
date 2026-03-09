<?php
/**
 * Button block render.
 *
 * @package ToolboxBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Toolbox_Block_Button extends Toolbox_Block_Base {

	const BLOCK_NAME = 'toolbox-blocks/button';

	public static function render( $attributes, $content, $block ) {
		$meta   = self::block_meta( $attributes, 'tb-button' );
		$url    = esc_url( $attributes['url'] ?? '#' );
		$text   = wp_kses_post( $attributes['text'] ?? '' );
		$target = in_array( $attributes['target'] ?? '_self', array( '_self', '_blank' ), true )
			? $attributes['target']
			: '_self';
		$rel = $attributes['rel'] ?? '';

		$target_attr = ( $target === '_blank' ) ? ' target="_blank" rel="noopener noreferrer"' : '';
		if ( $rel && $target !== '_blank' ) {
			$target_attr = ' rel="' . esc_attr( $rel ) . '"';
		}

		return sprintf(
			'%s<a href="%s" class="%s"%s%s>%s</a>',
			$meta['style_tag'],
			$url,
			$meta['class'],
			$meta['anchor'],
			$target_attr,
			$text
		);
	}
}
