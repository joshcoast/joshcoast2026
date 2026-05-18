<?php
/**
 * Regression checks for high-impact Toolbox Blocks render bugs.
 *
 * Run with: php tests/test-critical-regressions.php
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ );

function __($text, $domain = null) {
	return $text;
}

function sanitize_html_class( $class ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
}

function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $key ) );
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function wp_kses_post( $text ) {
	return (string) $text;
}

function absint( $value ) {
	return abs( (int) $value );
}

function get_the_ID() {
	return $GLOBALS['tb_current_post']['ID'] ?? 0;
}

function get_post_type() {
	return $GLOBALS['tb_current_post']['post_type'] ?? 'post';
}

function wp_reset_postdata() {
	$GLOBALS['tb_current_post'] = null;
}

class WP_Query {
	private $posts = array();
	private $index = 0;

	public function __construct( $args ) {
		$posts_per_page = $args['posts_per_page'] ?? 10;
		$post_type      = $args['post_type'] ?? 'post';
		$this->posts    = array_slice(
			array(
				array( 'ID' => 101, 'post_type' => $post_type ),
				array( 'ID' => 202, 'post_type' => $post_type ),
			),
			0,
			$posts_per_page
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
	private $parsed_block;
	private $context;

	public function __construct( $parsed_block, $context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $context;
	}

	public function render() {
		if ( 'toolbox-blocks/query' === ( $this->parsed_block['blockName'] ?? '' ) ) {
			throw new RuntimeException( 'Query block recursively rendered itself.' );
		}

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

function tb_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

$safe_css = Toolbox_Blocks_CSS_Generator::style_tag(
	'abc123',
	array(
		'desktop' => array(
			'color'           => 'red',
			'backgroundImage' => 'url("https://example.com/image.png")',
		),
	)
);
tb_assert( false !== strpos( $safe_css, '.tb-abc123{color:red;background-image:url("https://example.com/image.png")}' ), 'safe CSS declarations should still render' );

$unsafe_css = Toolbox_Blocks_CSS_Generator::style_tag(
	'abc123',
	array(
		'desktop' => array(
			'color'             => 'red</style><script>alert(1)</script>',
			'backgroundImage'   => 'url(javascript:alert(1))',
			'fontSize}body{top' => '12px',
			'marginTop'         => array( '12px' ),
		),
	)
);
tb_assert( '' === $unsafe_css, 'unsafe CSS values and property names should be skipped' );

$query_html = Toolbox_Block_Query::render(
	array(
		'uniqueId'     => 'query123',
		'postType'     => 'post',
		'postsPerPage' => 2,
	),
	'',
	(object) array(
		'parsed_block' => array(
			'blockName'   => 'toolbox-blocks/query',
			'innerBlocks' => array(
				array( 'blockName' => 'core/post-title' ),
				array( 'blockName' => 'toolbox-blocks/text' ),
			),
		),
	)
);

tb_assert( false !== strpos( $query_html, '[core/post-title:101][toolbox-blocks/text:101][core/post-title:202][toolbox-blocks/text:202]' ), 'query block should render inner template once per post' );
tb_assert( null === $GLOBALS['tb_current_post'], 'query render should reset global post data' );

fwrite( STDOUT, "All critical regression tests passed.\n" );
