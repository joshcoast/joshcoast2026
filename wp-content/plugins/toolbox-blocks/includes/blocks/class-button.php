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

	const BLOCK_NAME   = 'toolbox-blocks/button';
	const DEFAULT_ICON = 'arrow-down';

	/**
	 * Default button styles for server-side rendering fallback.
	 *
	 * This is used for legacy content that has no `styles` attribute saved yet.
	 *
	 * @return array
	 */
	protected static function default_styles() {
		return array(
			'desktop'      => array(
				'paddingTop'      => '8px',
				'paddingRight'    => '16px',
				'paddingBottom'   => '8px',
				'paddingLeft'     => '16px',
				'borderWidth'     => '2px',
				'borderStyle'     => 'solid',
				'borderColor'     => 'var(--base-3)',
				'fontSize'        => '16px',
				'letterSpacing'   => '0.1em',
				'color'           => 'var(--base-3)',
				'backgroundColor' => 'transparent',
				'borderRadius'    => '0px',
				'display'         => 'inline-flex',
				'alignItems'      => 'center',
				'justifyContent'  => 'center',
				'gap'             => '0.5em',
				'textDecoration'  => 'none',
			),
			'desktopHover' => array(
				'borderColor' => 'var(--global-color-6)',
			),
		);
	}

	public static function render( $attributes, $content, $block ) {
		if ( ! array_key_exists( 'styles', $attributes ) ) {
			$attributes['styles'] = self::default_styles();
		}

		$meta              = self::block_meta( $attributes, 'tb-button tb-button__link' );
		$url               = esc_url( $attributes['url'] ?? '#' );
		$text              = wp_kses_post( $attributes['text'] ?? '' );
		$show_icon         = ! empty( $attributes['showIcon'] );
		$icon_raw          = sanitize_key( $attributes['icon'] ?? self::DEFAULT_ICON );
		$icon              = Toolbox_Blocks_Icon_Library::has_icon( $icon_raw ) ? $icon_raw : self::DEFAULT_ICON;
		$icon_position_raw = $attributes['iconPosition'] ?? 'right';
		$icon_position     = in_array( $icon_position_raw, array( 'left', 'right' ), true )
			? $icon_position_raw
			: 'right';
		$target_raw        = $attributes['target'] ?? '_self';
		$target            = in_array( $target_raw, array( '_self', '_blank' ), true )
			? $target_raw
			: '_self';
		$rel               = $attributes['rel'] ?? '';

		$target_attr = ( $target === '_blank' ) ? ' target="_blank" rel="noopener noreferrer"' : '';
		if ( $rel && $target !== '_blank' ) {
			$target_attr = ' rel="' . esc_attr( $rel ) . '"';
		}

		$icon_markup = '';
		if ( $show_icon ) {
			$icon_markup = sprintf(
				'<span class="tb-button__icon" aria-hidden="true">%s</span>',
				Toolbox_Blocks_Icon_Library::get_svg( $icon, 'tb-button__icon-svg' )
			);
		}

		$button_content = '<span class="tb-button__text">' . $text . '</span>';
		if ( $show_icon && 'left' === $icon_position ) {
			$button_content = $icon_markup . $button_content;
		} elseif ( $show_icon ) {
			$button_content .= $icon_markup;
		}

		return sprintf(
			'%s<a href="%s" class="%s"%s%s>%s</a>',
			$meta['style_tag'],
			$url,
			$meta['class'],
			$meta['anchor'],
			$target_attr,
			$button_content
		);
	}
}
