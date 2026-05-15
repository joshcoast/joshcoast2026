<?php
/**
 * Regression coverage for server-side CSS generation sanitization.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' );

function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function sanitize_html_class( $value ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $value );
}

function toolbox_blocks_test_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';

$style_tag = Toolbox_Blocks_CSS_Generator::style_tag(
	'example',
	array(
		'desktop' => array(
			'color'          => 'red',
			'background'     => 'linear-gradient(45deg, red, blue)',
			'backgroundImage' => 'url("https://example.test/image.jpg")',
			'bad;color'      => 'green',
			'marginTop'      => '1px}</style><script>alert(1)</script>',
			'paddingTop'     => array( '1rem' ),
		),
	)
);

toolbox_blocks_test_assert(
	false !== strpos( $style_tag, 'color:red' ),
	'Expected safe declarations to be preserved.'
);
toolbox_blocks_test_assert(
	false !== strpos( $style_tag, 'background-image:linear-gradient(45deg, red, blue), url("https://example.test/image.jpg")' ),
	'Expected safe layered background declarations to be preserved.'
);
toolbox_blocks_test_assert(
	false === strpos( $style_tag, 'bad;color' ),
	'Expected malformed property names to be rejected.'
);
toolbox_blocks_test_assert(
	false === strpos( $style_tag, '</style><script>' ),
	'Expected style tag breakout values to be rejected.'
);
toolbox_blocks_test_assert(
	false === strpos( $style_tag, 'Array' ),
	'Expected non-scalar declaration values to be rejected.'
);

echo "CSS generator regression test passed.\n";
