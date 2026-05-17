<?php
/**
 * Regression tests for the Toolbox Blocks CSS generator.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ );

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $class ) {
		return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';

function tb_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, $message . PHP_EOL );
		exit( 1 );
	}
}

$html = Toolbox_Blocks_CSS_Generator::style_tag(
	'abc123',
	array(
		'desktop' => array(
			'color'           => 'red</style><script>alert(1)</script>',
			'backgroundImage' => 'url("hero.jpg")',
			'marginTop'       => '0',
		),
	)
);

tb_assert( 1 === substr_count( strtolower( $html ), '</style>' ), 'Style tag should only contain its own closing tag.' );
tb_assert( false === strpos( strtolower( $html ), '<script' ), 'Unsafe CSS values must not escape into HTML.' );
tb_assert( false !== strpos( $html, 'background-image:url("hero.jpg")' ), 'Safe CSS values should be preserved.' );
tb_assert( false !== strpos( $html, 'margin-top:0' ), 'Zero CSS values should be preserved.' );

$css = Toolbox_Blocks_CSS_Generator::generate(
	'hero',
	array(
		'desktop' => array(
			'background'      => 'linear-gradient(red, blue)',
			'backgroundImage' => 'url(hero.jpg)',
			'bad}prop'        => 'display:block',
			'fontSize'        => '12px;color:red',
		),
	)
);

tb_assert( '.tb-hero{background-image:linear-gradient(red, blue), url(hero.jpg)}' === $css, 'CSS generator should keep safe layered backgrounds and drop unsafe declarations.' );

echo 'CSS generator tests passed.' . PHP_EOL;
