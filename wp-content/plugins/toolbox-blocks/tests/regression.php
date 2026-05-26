<?php
/**
 * Standalone regression tests for critical Toolbox Blocks render paths.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ );

function tb_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

function sanitize_html_class( $class ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function sanitize_key( $key ) {
	return strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $key ) );
}

function absint( $value ) {
	return abs( intval( $value ) );
}

function __( $text, $domain = 'default' ) {
	return $text;
}

function wp_kses_post( $content ) {
	return $content;
}

function get_the_ID() {
	return $GLOBALS['tb_current_post']['ID'] ?? 0;
}

function get_post_type() {
	return $GLOBALS['tb_current_post']['post_type'] ?? 'post';
}

function wp_reset_postdata() {
	$GLOBALS['tb_postdata_was_reset'] = true;
}

class WP_Query {
	private $posts = array();
	private $index = 0;

	public function __construct( $args ) {
		$this->posts = array(
			array(
				'ID'        => 101,
				'post_type' => $args['post_type'] ?? 'post',
			),
			array(
				'ID'        => 102,
				'post_type' => $args['post_type'] ?? 'post',
			),
		);
	}

	public function have_posts() {
		return $this->index < count( $this->posts );
	}

	public function the_post() {
		$GLOBALS['tb_current_post'] = $this->posts[ $this->index ];
		$this->index++;
	}
}

class WP_Block {
	public $parsed_block;
	public $inner_blocks = array();
	public $context;

	public function __construct( $parsed_block, $context = array() ) {
		if ( 'toolbox-blocks/query' === ( $parsed_block['blockName'] ?? '' ) ) {
			throw new RuntimeException( 'Query block recursively rendered itself.' );
		}

		$this->parsed_block = $parsed_block;
		$this->context      = $context;
		foreach ( $parsed_block['innerBlocks'] ?? array() as $inner_block ) {
			$this->inner_blocks[] = new self( $inner_block, $context );
		}
	}

	public function render() {
		return sprintf(
			'[%s:%d:%s]',
			$this->parsed_block['blockName'] ?? 'unknown',
			$this->context['postId'] ?? 0,
			$this->context['postType'] ?? ''
		);
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';
require_once __DIR__ . '/../includes/class-block-base.php';
require_once __DIR__ . '/../includes/blocks/class-query.php';

$unsafe_style = Toolbox_Blocks_CSS_Generator::style_tag(
	'abc123',
	array(
		'desktop' => array(
			'color'           => 'red</style><script>alert(1)</script><style>',
			'backgroundImage' => 'url("https://example.com/image.png")',
			'boxShadow'       => '0 1px 2px rgba(0,0,0,.2)',
			'bad}property'    => 'blue',
			'borderColor'     => 'red;background:url(https://example.com/x)',
		),
	)
);

tb_assert( 1 === substr_count( $unsafe_style, '</style>' ), 'CSS values must not inject extra style tags.' );
tb_assert( false === strpos( $unsafe_style, '<script>' ), 'CSS values must not inject script tags.' );
tb_assert( false === strpos( $unsafe_style, 'bad}property' ), 'Invalid CSS property names must be skipped.' );
tb_assert( false === strpos( $unsafe_style, 'red;background' ), 'Declaration-breaking CSS values must be skipped.' );
tb_assert( false !== strpos( $unsafe_style, 'background-image:url("https://example.com/image.png")' ), 'Safe quoted url() values must still render.' );
tb_assert( false !== strpos( $unsafe_style, 'box-shadow:0 1px 2px rgba(0,0,0,.2)' ), 'Safe functional CSS values must still render.' );

$query_block = (object) array(
	'parsed_block' => array(
		'blockName'   => 'toolbox-blocks/query',
		'innerBlocks' => array(
			array(
				'blockName' => 'core/post-title',
			),
		),
	),
	'inner_blocks' => array(
		(object) array(
			'parsed_block' => array(
				'blockName' => 'core/post-title',
			),
		),
	),
);

$rendered_query = Toolbox_Block_Query::render(
	array(
		'postType'     => 'post',
		'uniqueId'     => 'query1',
		'noResultsText' => 'No posts.',
	),
	'',
	$query_block
);

tb_assert( false !== strpos( $rendered_query, '[core/post-title:101:post][core/post-title:102:post]' ), 'Query block must render inner template once per result.' );
tb_assert( ! empty( $GLOBALS['tb_postdata_was_reset'] ), 'Query block must reset post data after rendering.' );

echo "Toolbox Blocks regression tests passed.\n";
