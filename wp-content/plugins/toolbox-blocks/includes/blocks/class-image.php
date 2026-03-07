<?php
/**
 * Image block render.
 *
 * @package ToolboxBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Toolbox_Block_Image extends Toolbox_Block_Base {

	const BLOCK_NAME = 'toolbox-blocks/image';

	public static function render( $attributes, $content, $block ) {
		$meta    = self::block_meta( $attributes, 'tb-image' );
		$url     = esc_url( $attributes['url'] ?? '' );
		$alt     = esc_attr( $attributes['alt'] ?? '' );
		$caption = wp_kses_post( $attributes['caption'] ?? '' );
		$link    = esc_url( $attributes['linkUrl'] ?? '' );

		if ( ! $url ) {
			return '';
		}

		$img = sprintf( '<img src="%s" alt="%s" class="tb-image__img" />', $url, $alt );

		if ( $link ) {
			$img = sprintf( '<a href="%s" class="tb-image__link">%s</a>', $link, $img );
		}

		$caption_html = $caption
			? '<figcaption class="tb-image__caption">' . $caption . '</figcaption>'
			: '';

		return sprintf(
			'%s<figure class="%s"%s>%s%s</figure>',
			$meta['style_tag'],
			$meta['class'],
			$meta['anchor'],
			$img,
			$caption_html
		);
	}
}
