<?php
/**
 * Standalone regression checks for Toolbox_Blocks_CSS_Generator.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ . '/' );

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $classname ) {
		return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $classname );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';

function toolbox_blocks_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, $message . PHP_EOL );
		exit( 1 );
	}
}

$safe_css = Toolbox_Blocks_CSS_Generator::generate(
	'safe-id',
	array(
		'desktop' => array(
			'backgroundColor' => '#fff',
			'paddingTop'      => '12px',
		),
	)
);

toolbox_blocks_assert(
	false !== strpos( $safe_css, '.tb-safe-id{background-color:#fff;padding-top:12px}' ),
	'Expected safe camelCase declarations to be generated.'
);

$malicious_style = Toolbox_Blocks_CSS_Generator::style_tag(
	'bad-id',
	array(
		'desktop' => array(
			'color'                  => 'red',
			'backgroundImage'        => 'url("</style><script>alert(1)</script>")',
			'bad}</style><script>x' => 'block',
		),
	)
);

toolbox_blocks_assert(
	false === strpos( $malicious_style, '<script' ),
	'Style output must not include script tags from CSS values or property names.'
);
toolbox_blocks_assert(
	1 === substr_count( $malicious_style, '</style>' ),
	'Style output must contain only its own closing style tag.'
);
toolbox_blocks_assert(
	false !== strpos( $malicious_style, '.tb-bad-id{color:red}' ),
	'Unsafe declarations should be skipped without dropping safe declarations.'
);

fwrite( STDOUT, "CSS generator regression checks passed.\n" );
