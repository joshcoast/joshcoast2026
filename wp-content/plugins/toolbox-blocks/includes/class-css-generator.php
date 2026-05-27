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
	const ALLOWED_PROPERTIES = array(
		'align-items',
		'backdrop-filter',
		'background',
		'background-attachment',
		'background-blend-mode',
		'background-color',
		'background-image',
		'background-position',
		'background-repeat',
		'background-size',
		'border-bottom-color',
		'border-bottom-left-radius',
		'border-bottom-right-radius',
		'border-bottom-style',
		'border-bottom-width',
		'border-color',
		'border-left-color',
		'border-left-style',
		'border-left-width',
		'border-radius',
		'border-right-color',
		'border-right-style',
		'border-right-width',
		'border-style',
		'border-top-color',
		'border-top-left-radius',
		'border-top-right-radius',
		'border-top-style',
		'border-top-width',
		'border-width',
		'bottom',
		'box-shadow',
		'color',
		'column-gap',
		'cursor',
		'display',
		'fill',
		'filter',
		'flex-direction',
		'flex-wrap',
		'float',
		'font-family',
		'font-size',
		'font-style',
		'font-weight',
		'gap',
		'height',
		'justify-content',
		'left',
		'letter-spacing',
		'line-height',
		'list-style-position',
		'list-style-type',
		'margin',
		'margin-bottom',
		'margin-left',
		'margin-right',
		'margin-top',
		'max-height',
		'max-width',
		'min-height',
		'min-width',
		'mix-blend-mode',
		'object-fit',
		'object-position',
		'opacity',
		'outline-color',
		'outline-offset',
		'outline-style',
		'outline-width',
		'overflow-x',
		'overflow-y',
		'padding',
		'padding-bottom',
		'padding-left',
		'padding-right',
		'padding-top',
		'pointer-events',
		'position',
		'right',
		'row-gap',
		'stroke',
		'stroke-width',
		'text-align',
		'text-decoration',
		'text-transform',
		'top',
		'transform',
		'transition',
		'white-space',
		'width',
		'word-break',
		'z-index',
	);

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
			if ( ! $css_prop ) {
				continue;
			}
			$value = self::css_declaration_value( $value );
			if ( null === $value ) {
				continue;
			}
			$parts[] = $css_prop . ':' . $value;
		}
		return implode( ';', $parts );
	}

	/**
	 * Convert a stored style key to a safe CSS property name.
	 *
	 * @param mixed $prop Stored property key.
	 * @return string Empty string when unsupported.
	 */
	private static function css_property_name( $prop ) {
		$css_prop = strtolower( preg_replace( '/([A-Z])/', '-$1', (string) $prop ) );
		$css_prop = trim( $css_prop );
		if ( ! preg_match( '/^[a-z][a-z0-9-]*$/', $css_prop ) ) {
			return '';
		}
		return in_array( $css_prop, self::ALLOWED_PROPERTIES, true ) ? $css_prop : '';
	}

	/**
	 * Sanitize a CSS declaration value for inline <style> output.
	 *
	 * Values that can terminate declarations/rules or break out of the style tag
	 * are dropped instead of escaped so stored block attributes cannot inject CSS
	 * rules or HTML.
	 *
	 * @param mixed $value Stored CSS declaration value.
	 * @return string|null Safe value, or null when unsafe.
	 */
	private static function css_declaration_value( $value ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			return null;
		}
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return null;
		}
		if ( preg_match( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value ) ) {
			return null;
		}
		if ( preg_match( '/[{};<>]/', $value ) ) {
			return null;
		}
		if ( preg_match( '#/\*|\*/#', $value ) ) {
			return null;
		}
		if ( preg_match( '/(?:@import|expression\s*\(|javascript\s*:|data\s*:)/i', $value ) ) {
			return null;
		}
		return $value;
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
