<?php
/**
 * Shared spacing CSS output for Coast Blocks.
 * Outputs margin/padding for desktop, tablet (Medium), and mobile (Small).
 *
 * @package CoastBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Coast_Blocks_Spacing
 */
class Coast_Blocks_Spacing {

	/**
	 * Default breakpoints (max-width in px).
	 */
	const BREAKPOINT_TABLET = 1024;
	const BREAKPOINT_MOBILE = 768;

	/**
	 * Spacing attribute keys (no suffix = desktop).
	 */
	const PADDING_KEYS = array( 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft' );
	const MARGIN_KEYS  = array( 'marginTop', 'marginRight', 'marginBottom', 'marginLeft' );

	/**
	 * Get spacing CSS for a block (desktop + tablet + mobile).
	 *
	 * @param array  $attributes Block attributes (must include 'spacing' object).
	 * @param string $selector   CSS selector for the block (e.g. .cb-container-abc123).
	 * @return string CSS string, or empty if no spacing set.
	 */
	public static function get_css( $attributes, $selector ) {
		$raw = isset( $attributes['spacing'] ) ? $attributes['spacing'] : null;
		// Block attributes from saved content can be stdClass (from JSON), not array.
		$spacing = self::normalize_spacing( $raw );
		if ( empty( $spacing ) ) {
			return '';
		}

		$css = '';

		// Desktop (all screens).
		$desktop = self::get_device_css( $spacing, $selector, '' );
		if ( $desktop ) {
			$css .= $desktop;
		}

		// Tablet (Medium).
		$tablet = self::get_device_css( $spacing, $selector, 'Tablet' );
		if ( $tablet ) {
			$css .= sprintf(
				'@media (max-width: %dpx) { %s }',
				self::BREAKPOINT_TABLET,
				$tablet
			);
		}

		// Mobile (Small).
		$mobile = self::get_device_css( $spacing, $selector, 'Mobile' );
		if ( $mobile ) {
			$css .= sprintf(
				'@media (max-width: %dpx) { %s }',
				self::BREAKPOINT_MOBILE,
				$mobile
			);
		}

		return $css;
	}

	/**
	 * Get CSS for one device (desktop, tablet, or mobile).
	 *
	 * @param array  $spacing Full spacing attributes.
	 * @param string $selector CSS selector.
	 * @param string $suffix   '' | 'Tablet' | 'Mobile'.
	 * @return string CSS for that device.
	 */
	/**
	 * Normalize spacing to associative array (handles stdClass from block JSON).
	 *
	 * @param mixed $raw Attributes 'spacing' value.
	 * @return array
	 */
	protected static function normalize_spacing( $raw ) {
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

	/**
	 * Get a spacing value by key (works for array or array-like).
	 *
	 * @param array  $spacing Normalized spacing array.
	 * @param string $key     Key (e.g. paddingTop).
	 * @return mixed
	 */
	protected static function get_spacing_value( $spacing, $key ) {
		return isset( $spacing[ $key ] ) ? $spacing[ $key ] : '';
	}

	protected static function get_device_css( $spacing, $selector, $suffix ) {
		$rules = array();

		foreach ( self::PADDING_KEYS as $key ) {
			$k = $key . $suffix;
			$v = self::get_spacing_value( $spacing, $k );
			$css_val = self::format_value( $v );
			if ( $css_val !== '' ) {
				$css_prop = self::camel_to_kebab( $key );
				$rules[]  = $css_prop . ':' . $css_val . ';';
			}
		}

		foreach ( self::MARGIN_KEYS as $key ) {
			$k = $key . $suffix;
			$v = self::get_spacing_value( $spacing, $k );
			$css_val = self::format_value( $v );
			if ( $css_val !== '' ) {
				$css_prop = self::camel_to_kebab( $key );
				$rules[]  = $css_prop . ':' . $css_val . ';';
			}
		}

		if ( empty( $rules ) ) {
			return '';
		}

		return $selector . '{' . implode( '', $rules ) . '}';
	}

	/**
	 * Format a single value (number + unit). Default unit px.
	 *
	 * @param mixed $value Number, "10px", or empty.
	 * @return string CSS value (e.g. "10px" or "").
	 */
	/**
	 * Convert camelCase to kebab-case (e.g. paddingTop -> padding-top).
	 *
	 * @param string $str CamelCase string.
	 * @return string
	 */
	protected static function camel_to_kebab( $str ) {
		return strtolower( preg_replace( '/([A-Z])/', '-$1', $str ) );
	}

	protected static function format_value( $value ) {
		if ( $value === '' || $value === null ) {
			return '';
		}
		if ( is_numeric( $value ) ) {
			return $value . 'px';
		}
		return (string) $value;
	}

	/**
	 * Default spacing attributes for block registration (shared by blocks that opt in).
	 *
	 * @return array Defaults for spacing object.
	 */
	public static function get_default_spacing_attributes() {
		$keys = array_merge(
			array_map( function ( $k ) { return $k; }, self::PADDING_KEYS ),
			array_map( function ( $k ) { return $k; }, self::MARGIN_KEYS ),
			array_map( function ( $k ) { return $k . 'Tablet'; }, self::PADDING_KEYS ),
			array_map( function ( $k ) { return $k . 'Tablet'; }, self::MARGIN_KEYS ),
			array_map( function ( $k ) { return $k . 'Mobile'; }, self::PADDING_KEYS ),
			array_map( function ( $k ) { return $k . 'Mobile'; }, self::MARGIN_KEYS )
		);
		$defaults = array();
		foreach ( $keys as $key ) {
			$defaults[ $key ] = '';
		}
		return $defaults;
	}
}
