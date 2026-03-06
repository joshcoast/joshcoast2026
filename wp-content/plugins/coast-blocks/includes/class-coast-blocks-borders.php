<?php
/**
 * Shared border CSS output for Coast Blocks.
 * Outputs border width/style/color and border-radius for desktop, tablet, and mobile.
 *
 * @package CoastBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Coast_Blocks_Borders
 */
class Coast_Blocks_Borders {

	const BREAKPOINT_TABLET = 1024;
	const BREAKPOINT_MOBILE = 768;

	const BORDER_WIDTH_KEYS  = array( 'borderTopWidth', 'borderRightWidth', 'borderBottomWidth', 'borderLeftWidth' );
	const BORDER_STYLE_KEYS  = array( 'borderTopStyle', 'borderRightStyle', 'borderBottomStyle', 'borderLeftStyle' );
	const BORDER_COLOR_KEYS  = array( 'borderTopColor', 'borderRightColor', 'borderBottomColor', 'borderLeftColor' );
	const BORDER_RADIUS_KEYS = array( 'borderTopLeftRadius', 'borderTopRightRadius', 'borderBottomRightRadius', 'borderBottomLeftRadius' );

	/**
	 * Get border CSS for a block (desktop + tablet + mobile).
	 *
	 * @param array  $attributes Block attributes (must include 'borders' object).
	 * @param string $selector   CSS selector for the block.
	 * @return string CSS string, or empty if no borders set.
	 */
	public static function get_css( $attributes, $selector ) {
		$raw     = isset( $attributes['borders'] ) ? $attributes['borders'] : null;
		$borders = self::normalize_borders( $raw );
		if ( empty( $borders ) ) {
			return '';
		}

		$css = '';

		$desktop = self::get_device_css( $borders, $selector, '' );
		if ( $desktop ) {
			$css .= $desktop;
		}

		$tablet = self::get_device_css( $borders, $selector, 'Tablet' );
		if ( $tablet ) {
			$css .= sprintf( '@media (max-width: %dpx) { %s }', self::BREAKPOINT_TABLET, $tablet );
		}

		$mobile = self::get_device_css( $borders, $selector, 'Mobile' );
		if ( $mobile ) {
			$css .= sprintf( '@media (max-width: %dpx) { %s }', self::BREAKPOINT_MOBILE, $mobile );
		}

		return $css;
	}

	/**
	 * Get CSS for one device.
	 *
	 * @param array  $borders  Normalized borders array.
	 * @param string $selector CSS selector.
	 * @param string $suffix   '' | 'Tablet' | 'Mobile'.
	 * @return string
	 */
	protected static function get_device_css( $borders, $selector, $suffix ) {
		$rules = array();

		foreach ( self::BORDER_WIDTH_KEYS as $key ) {
			$k       = $key . $suffix;
			$v       = self::get_value( $borders, $k );
			$css_val = self::format_length( $v );
			if ( $css_val !== '' ) {
				$rules[] = self::camel_to_kebab( $key ) . ':' . $css_val . ';';
			}
		}

		foreach ( self::BORDER_STYLE_KEYS as $key ) {
			$k = $key . $suffix;
			$v = self::get_value( $borders, $k );
			// Default to 'solid' if a border width exists but style is not explicitly set
			$width_key = str_replace( 'Style', 'Width', $key );
			if ( $v === '' && self::get_value( $borders, $width_key . $suffix ) !== '' ) {
				$v = 'solid';
			}
			if ( $v !== '' && $v !== 'none' ) {
				$rules[] = self::camel_to_kebab( $key ) . ':' . $v . ';';
			}
		}

		foreach ( self::BORDER_COLOR_KEYS as $key ) {
			$k = $key . $suffix;
			$v = self::get_value( $borders, $k );
			if ( $v !== '' ) {
				$rules[] = self::camel_to_kebab( $key ) . ':' . $v . ';';
			}
		}

		foreach ( self::BORDER_RADIUS_KEYS as $key ) {
			$k       = $key . $suffix;
			$v       = self::get_value( $borders, $k );
			$css_val = self::format_length( $v );
			if ( $css_val !== '' ) {
				$rules[] = self::camel_to_kebab( $key ) . ':' . $css_val . ';';
			}
		}

		if ( empty( $rules ) ) {
			return '';
		}

		return $selector . '{' . implode( '', $rules ) . '}';
	}

	protected static function normalize_borders( $raw ) {
		if ( $raw === null || $raw === '' ) {
			return array();
		}
		if ( is_array( $raw ) ) {
			return $raw;
		}
		if ( is_object( $raw ) ) {
			return (array) $raw;
		}
		return array();
	}

	protected static function get_value( $borders, $key ) {
		return isset( $borders[ $key ] ) ? $borders[ $key ] : '';
	}

	protected static function camel_to_kebab( $str ) {
		return strtolower( preg_replace( '/([A-Z])/', '-$1', $str ) );
	}

	protected static function format_length( $value ) {
		if ( $value === '' || $value === null ) {
			return '';
		}
		if ( is_numeric( $value ) ) {
			return $value . 'px';
		}
		return (string) $value;
	}

	/**
	 * Default border attributes for block registration.
	 *
	 * @return array
	 */
	public static function get_default_border_attributes() {
		$keys = array();
		foreach ( array_merge( self::BORDER_WIDTH_KEYS, self::BORDER_STYLE_KEYS, self::BORDER_COLOR_KEYS, self::BORDER_RADIUS_KEYS ) as $key ) {
			$keys[] = $key;
			$keys[] = $key . 'Tablet';
			$keys[] = $key . 'Mobile';
		}
		$defaults = array();
		foreach ( $keys as $key ) {
			$defaults[ $key ] = '';
		}
		return $defaults;
	}
}
