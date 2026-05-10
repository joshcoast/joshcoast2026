<?php
/**
 * CSS generator for Toolbox Blocks.
 *
 * Converts the JS-side `styles` attribute object to a CSS string.
 * Styles are stored as:
 *   styles.desktop / styles.desktopHover
 *   styles.tablet  / styles.tabletHover
 *   styles.mobile  / styles.mobileHover
 *
 * Each value is an object of camelCase CSS property names.
 *
 * @package ToolboxBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Toolbox_Blocks_CSS_Generator {

	const TABLET_BREAKPOINT = 1024;
	const MOBILE_BREAKPOINT = 767;

	/**
	 * Generate full CSS for a block.
	 *
	 * @param string $unique_id Block unique ID.
	 * @param mixed  $styles    Styles attribute (array or stdClass from block JSON).
	 * @return string
	 */
	public static function generate( $unique_id, $styles ) {
		if ( empty( $unique_id ) || empty( $styles ) ) {
			return '';
		}
		$selector = '.tb-' . sanitize_html_class( $unique_id );
		return self::generate_for_selector( $selector, $styles );
	}

	/**
	 * Generate CSS for an arbitrary selector.
	 *
	 * @param string $selector CSS selector.
	 * @param mixed  $styles   Styles attribute.
	 * @return string
	 */
	public static function generate_for_selector( $selector, $styles ) {
		$styles = self::normalize( $styles );
		if ( empty( $styles ) ) {
			return '';
		}

		$css = '';

		// Desktop – no media query.
		$main = self::declarations( self::normalize( $styles['desktop'] ?? array() ) );
		if ( $main ) {
			$css .= $selector . '{' . $main . '}';
		}
		$hover = self::declarations( self::normalize( $styles['desktopHover'] ?? array() ) );
		if ( $hover ) {
			$css .= $selector . ':hover{' . $hover . '}';
		}

		// Tablet.
		$main = self::declarations( self::normalize( $styles['tablet'] ?? array() ) );
		if ( $main ) {
			$css .= sprintf( '@media(max-width:%dpx){%s{%s}}', self::TABLET_BREAKPOINT, $selector, $main );
		}
		$hover = self::declarations( self::normalize( $styles['tabletHover'] ?? array() ) );
		if ( $hover ) {
			$css .= sprintf( '@media(max-width:%dpx){%s:hover{%s}}', self::TABLET_BREAKPOINT, $selector, $hover );
		}

		// Mobile.
		$main = self::declarations( self::normalize( $styles['mobile'] ?? array() ) );
		if ( $main ) {
			$css .= sprintf( '@media(max-width:%dpx){%s{%s}}', self::MOBILE_BREAKPOINT, $selector, $main );
		}
		$hover = self::declarations( self::normalize( $styles['mobileHover'] ?? array() ) );
		if ( $hover ) {
			$css .= sprintf( '@media(max-width:%dpx){%s:hover{%s}}', self::MOBILE_BREAKPOINT, $selector, $hover );
		}

		return $css;
	}

	/**
	 * Combine gradient (`background`) and photo (`backgroundImage`) into one layered `background-image`
	 * (CSS lists first image on top, so gradient is drawn above the photo).
	 *
	 * @param array $props Style props camelCase.
	 * @return array
	 */
	private static function merge_background_image_layers( array $props ) {
		$bg  = isset( $props['background'] ) ? trim( (string) $props['background'] ) : '';
		$img = isset( $props['backgroundImage'] ) ? trim( (string) $props['backgroundImage'] ) : '';
		if ( '' === $bg || '' === $img ) {
			return $props;
		}
		if ( ! self::is_gradient_only_layer_value( $bg ) ) {
			return $props;
		}
		$out                      = $props;
		$out['backgroundImage'] = $bg . ', ' . $img;
		unset( $out['background'] );
		return $out;
	}

	/**
	 * @param string $value Trimmed `background` value.
	 * @return bool
	 */
	private static function is_gradient_only_layer_value( $value ) {
		$v = trim( (string) $value );
		if ( '' === $v ) {
			return false;
		}
		return 1 === preg_match( '/^(?:linear|radial|repeating-linear|repeating-radial)-gradient\s*\(/i', $v );
	}

	/**
	 * Convert an array of camelCase properties to CSS declarations.
	 *
	 * @param array $props Key-value pairs of camelCase props.
	 * @return string Declarations string (no braces).
	 */
	protected static function declarations( array $props ) {
		$props = self::merge_background_image_layers( $props );
		$parts = array();
		foreach ( $props as $prop => $value ) {
			if ( $value === '' || $value === null ) {
				continue;
			}
			$css_prop = self::css_property_name( $prop );
			$value    = self::css_value( $value );
			if ( '' === $css_prop || '' === $value ) {
				continue;
			}
			$parts[] = $css_prop . ':' . $value;
		}
		return implode( ';', $parts );
	}

	/**
	 * Convert a JS style property name to a safe CSS property name.
	 *
	 * @param mixed $prop Style property key.
	 * @return string
	 */
	private static function css_property_name( $prop ) {
		$prop = (string) $prop;
		if ( '' === $prop ) {
			return '';
		}

		if ( str_starts_with( $prop, '--' ) ) {
			return preg_match( '/^--[a-zA-Z0-9_-]+$/', $prop ) ? strtolower( $prop ) : '';
		}

		if ( ! preg_match( '/^[a-zA-Z][a-zA-Z0-9-]*$/', $prop ) ) {
			return '';
		}

		return strtolower( preg_replace( '/([A-Z])/', '-$1', $prop ) );
	}

	/**
	 * Escape a CSS value before it is emitted into a raw <style> tag.
	 *
	 * @param mixed $value Style value.
	 * @return string
	 */
	private static function css_value( $value ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			return '';
		}

		$value = trim( (string) $value );
		if ( '' === $value ) {
			return '';
		}

		$value = str_replace( "\0", '', $value );
		$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value );

		// Keep user-controlled values inside this declaration and inside the style element.
		return str_replace(
			array( '\\', '<', '>', ';', '{', '}' ),
			array( '\\\\', '\\3C ', '\\3E ', '\\3B ', '\\7B ', '\\7D ' ),
			$value
		);
	}

	/**
	 * Normalize a value to an associative array (handles stdClass from JSON decode).
	 *
	 * @param mixed $raw Value to normalize.
	 * @return array
	 */
	public static function normalize( $raw ) {
		if ( is_array( $raw ) ) {
			return $raw;
		}
		if ( $raw instanceof stdClass ) {
			return (array) $raw;
		}
		return array();
	}

	/**
	 * Build an inline <style> tag for a block.
	 *
	 * @param string $unique_id Block unique ID.
	 * @param mixed  $styles    Styles attribute.
	 * @return string Empty string if no CSS.
	 */
	public static function style_tag( $unique_id, $styles ) {
		$css = self::generate( $unique_id, $styles );
		if ( ! $css ) {
			return '';
		}
		return '<style class="tb-inline-css" id="tb-css-' . esc_attr( $unique_id ) . '">' . $css . '</style>';
	}
}
