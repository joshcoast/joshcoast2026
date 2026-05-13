<?php
/**
 * Regression test for Query block template rendering.
 *
 * Run with: php wp-content/plugins/toolbox-blocks/tests/query-render-recursion.php
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ );

$toolbox_test_posts           = array( 101, 102 );
$toolbox_test_current_post_id = 0;
$toolbox_test_reset_called    = false;

function sanitize_html_class( $value ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $value );
}

function sanitize_key( $value ) {
	return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) );
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
	global $toolbox_test_current_post_id;
	return $toolbox_test_current_post_id;
}

function wp_reset_postdata() {
	global $toolbox_test_reset_called;
	$toolbox_test_reset_called = true;
}

class WP_Query {
	private $index = 0;

	public function __construct( $args ) {}

	public function have_posts() {
		global $toolbox_test_posts;
		return $this->index < count( $toolbox_test_posts );
	}

	public function the_post() {
		global $toolbox_test_posts, $toolbox_test_current_post_id;
		$toolbox_test_current_post_id = $toolbox_test_posts[ $this->index ];
		$this->index++;
	}
}

class WP_Block {
	public $parsed_block;
	public $context;

	public function __construct( $parsed_block, $available_context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $available_context;
	}

	public function render() {
		if ( 'toolbox-blocks/query' === ( $this->parsed_block['blockName'] ?? '' ) ) {
			throw new RuntimeException( 'Query block rendered itself recursively.' );
		}

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

$query_block = new WP_Block(
	array(
		'blockName'   => 'toolbox-blocks/query',
		'attrs'       => array(),
		'innerBlocks' => array(
			array( 'blockName' => 'core/post-title' ),
			array( 'blockName' => 'core/post-excerpt' ),
		),
	)
);

$output = Toolbox_Block_Query::render(
	array(
		'uniqueId'     => 'query-test',
		'postType'     => 'post',
		'postsPerPage' => 2,
	),
	'',
	$query_block
);

$expected = '<div class="tb-block tb-query tb-query-test">[core/post-title:101:post][core/post-excerpt:101:post][core/post-title:102:post][core/post-excerpt:102:post]</div>';

if ( $expected !== $output ) {
	fwrite( STDERR, "Unexpected query render output.\nExpected: $expected\nActual:   $output\n" );
	exit( 1 );
}

if ( ! $toolbox_test_reset_called ) {
	fwrite( STDERR, "Expected wp_reset_postdata() to be called.\n" );
	exit( 1 );
}

echo "Query render recursion regression passed.\n";
