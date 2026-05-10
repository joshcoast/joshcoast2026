<?php
/**
 * Regression test for inline CSS generation.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ );

function sanitize_html_class( $class ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

require_once __DIR__ . '/../includes/class-css-generator.php';

$style_tag = Toolbox_Blocks_CSS_Generator::style_tag(
	'abc123',
	array(
		'desktop' => array(
			'color'           => 'red</style><script>alert(1)</script>',
			'background'      => 'linear-gradient(180deg, #000, #fff)',
			'backgroundImage' => 'url("https://example.com/bg.png")',
			'bad;property'    => 'display:block',
		),
	)
);

if ( 1 !== preg_match_all( '#</style>#i', $style_tag ) ) {
	throw new Exception( 'Generated CSS allowed an injected style closing tag.' );
}

if ( false !== stripos( $style_tag, '<script' ) ) {
	throw new Exception( 'Generated CSS allowed a script tag breakout.' );
}

if ( false !== strpos( $style_tag, 'bad;property' ) ) {
	throw new Exception( 'Generated CSS allowed an invalid property name.' );
}

if ( false === strpos( $style_tag, 'background-image:linear-gradient(180deg, #000, #fff), url("https://example.com/bg.png")' ) ) {
	throw new Exception( 'Generated CSS did not preserve layered background output.' );
}

echo "CSS generator regression passed.\n";
