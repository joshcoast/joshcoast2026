<?php
/**
 * Standalone regressions for high-impact Toolbox Blocks render paths.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ . '/../../../../' );

function sanitize_html_class( $class ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
}

function sanitize_key( $key ) {
	return strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $key ) );
}

function absint( $value ) {
	return abs( (int) $value );
}

function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function wp_kses_post( $value ) {
	return (string) $value;
}

function __( $text, $domain = 'default' ) {
	return $text;
}

function get_the_ID() {
	return 42;
}

function get_post_type() {
	return 'post';
}

function wp_reset_postdata() {}

class WP_Query {
	private $remaining = 1;

	public function __construct( $query_args ) {}

	public function have_posts() {
		return $this->remaining > 0;
	}

	public function the_post() {
		--$this->remaining;
	}
}

class WP_Block {
	public $parsed_block;
	public $context;

	public function __construct( $parsed_block, $context = array() ) {
		if ( 'toolbox-blocks/query' === ( $parsed_block['blockName'] ?? '' ) ) {
			throw new RuntimeException( 'Query render attempted to render itself.' );
		}
		$this->parsed_block = $parsed_block;
		$this->context      = $context;
	}

	public function render() {
		return sprintf(
			'[%s:%d]',
			$this->parsed_block['blockName'] ?? 'unknown',
			$this->context['postId'] ?? 0
		);
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';
require_once __DIR__ . '/../includes/class-block-base.php';
require_once __DIR__ . '/../includes/blocks/class-query.php';

function toolbox_blocks_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$style_tag = Toolbox_Blocks_CSS_Generator::style_tag(
	'regression',
	array(
		'desktop' => array(
			'color'            => 'red',
			'bad}prop'         => 'blue',
			'backgroundImage'  => 'url("/safe.png")',
			'width'            => 'calc(100% - 1rem)',
			'height'           => '1px;}</style><script>alert(1)</script><style>',
			'backgroundColor'  => '<script>alert(1)</script>',
		),
	)
);

toolbox_blocks_assert( str_contains( $style_tag, 'color:red' ), 'Expected safe color declaration.' );
toolbox_blocks_assert( str_contains( $style_tag, 'background-image:url("/safe.png")' ), 'Expected safe background image declaration.' );
toolbox_blocks_assert( str_contains( $style_tag, 'width:calc(100% - 1rem)' ), 'Expected safe calc declaration.' );
toolbox_blocks_assert( ! str_contains( $style_tag, 'script' ), 'Unsafe script markup leaked into style tag.' );
toolbox_blocks_assert( 1 === substr_count( $style_tag, '</style>' ), 'Extra closing style tag leaked into style tag body.' );
toolbox_blocks_assert( ! str_contains( $style_tag, 'bad}prop' ), 'Unsafe CSS property leaked into style tag.' );

$query_block = (object) array(
	'parsed_block' => array(
		'blockName'   => 'toolbox-blocks/query',
		'innerBlocks' => array(
			array(
				'blockName'    => 'toolbox-blocks/text',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
		),
	),
);

$query_output = Toolbox_Block_Query::render(
	array(
		'uniqueId' => 'query-regression',
	),
	'',
	$query_block
);

toolbox_blocks_assert( str_contains( $query_output, '[toolbox-blocks/text:42]' ), 'Query did not render inner block with post context.' );

echo "Toolbox Blocks regressions passed.\n";
