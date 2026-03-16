<?php
/**
 * Shared SVG icon library for Toolbox blocks.
 *
 * @package ToolboxBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Toolbox_Blocks_Icon_Library {

	/**
	 * Icon definitions.
	 *
	 * @return array
	 */
	protected static function icons() {
		return array(
			'arrow-down'     => array(
				'fill'  => false,
				'paths' => array( 'M12 4v16', 'M8 14 L12 20 L16 14' ),
			),
			'arrow-right'    => array(
				'fill'  => false,
				'paths' => array( 'M4 12h16', 'M14 8 L20 12 L14 16' ),
			),
			'arrow-up-right' => array(
				'fill'  => false,
				'paths' => array( 'M7 17 L17 7', 'M12 12 L17 7 L13 11' ),
			),
			'chevron-right'  => array(
				'fill'  => false,
				'paths' => array( 'M9 6 L15 12 L9 18' ),
			),
			'download'       => array(
				'fill'  => false,
				'paths' => array( 'M12 4 v11', 'M8 12 L12 18 L16 12', 'M4 20 h16' ),
			),
			'external-link'  => array(
				'fill'  => false,
				'paths' => array( 'M14 4h6v6', 'M10 14 20 4', 'M20 13v7H4V4h7' ),
			),
			'plus'           => array(
				'fill'  => false,
				'paths' => array( 'M12 5v14', 'M5 12h14' ),
			),
			'play'           => array(
				'fill'  => true,
				'paths' => array( 'm9 7 8 5-8 5z' ),
			),
			'star'           => array(
				'fill'  => false,
				'paths' => array( 'm12 3 2.9 5.9 6.5 1-4.7 4.6 1.1 6.5L12 18l-5.8 3.1 1.1-6.5L2.6 9.9l6.5-1z' ),
			),
			'plus-circle'    => array(
				'fill'  => false,
				'paths' => array( 'M12 5v14', 'M5 12h14', 'M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z' ),
			),
		);
	}

	/**
	 * Validate icon key.
	 *
	 * @param string $icon_key Icon key.
	 * @return bool
	 */
	public static function has_icon( $icon_key ) {
		$icons = self::icons();
		return isset( $icons[ $icon_key ] );
	}

	/**
	 * Return icon SVG markup.
	 *
	 * @param string $icon_key   Icon key.
	 * @param string $class_name Class names for the svg element.
	 * @return string
	 */
	public static function get_svg( $icon_key, $class_name = 'tb-icon' ) {
		$icon_key = sanitize_key( $icon_key );
		$icons    = self::icons();
		if ( ! isset( $icons[ $icon_key ] ) ) {
			$icon_key = 'arrow-down';
		}
		$icon = $icons[ $icon_key ];

		$paths_html = '';
		foreach ( $icon['paths'] as $path ) {
			$paths_html .= '<path d="' . esc_attr( $path ) . '"></path>';
		}

		$class_parts = array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', trim( (string) $class_name ) ) ) );
		$classes     = implode( ' ', $class_parts );
		$fill        = ! empty( $icon['fill'] ) ? 'currentColor' : 'none';
		$stroke      = ! empty( $icon['fill'] ) ? 'none' : 'currentColor';

		return sprintf(
			'<svg class="%1$s" viewBox="0 0 24 24" width="1em" height="1em" fill="%2$s" stroke="%3$s" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%4$s</svg>',
			esc_attr( $classes ),
			esc_attr( $fill ),
			esc_attr( $stroke ),
			$paths_html
		);
	}
}
