<?php
/**
 * Regression test for Query block template rendering.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ );

function sanitize_html_class( $class ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
}

function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $key ) );
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function absint( $maybeint ) {
	return abs( (int) $maybeint );
}

function wp_kses_post( $content ) {
	return (string) $content;
}

function __( $text, $domain = 'default' ) {
	return $text;
}

function get_the_ID() {
	return $GLOBALS['toolbox_blocks_test_current_post_id'];
}

function get_post_type() {
	return 'post';
}

function wp_reset_postdata() {
	$GLOBALS['toolbox_blocks_test_reset_postdata'] = true;
}

class WP_Query {
	private $posts = array( 101, 102 );
	private $index = -1;

	public function __construct( $args ) {}

	public function have_posts() {
		return $this->index + 1 < count( $this->posts );
	}

	public function the_post() {
		$this->index++;
		$GLOBALS['toolbox_blocks_test_current_post_id'] = $this->posts[ $this->index ];
	}
}

class WP_Block {
	public $parsed_block;
	private $context;

	public function __construct( $parsed_block, $context = array() ) {
		if ( 'toolbox-blocks/query' === ( $parsed_block['blockName'] ?? '' ) ) {
			throw new Exception( 'Query block render recursed into itself.' );
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

$query_block = (object) array(
	'parsed_block' => array(
		'blockName'   => 'toolbox-blocks/query',
		'innerBlocks' => array(
			array( 'blockName' => 'toolbox-blocks/headline' ),
			array( 'blockName' => 'core/post-title' ),
		),
	),
);

$output = Toolbox_Block_Query::render(
	array(
		'uniqueId'     => 'query1',
		'postType'     => 'post',
		'postsPerPage' => 2,
	),
	'',
	$query_block
);

$expected = '[toolbox-blocks/headline:101][core/post-title:101][toolbox-blocks/headline:102][core/post-title:102]';
if ( false === strpos( $output, $expected ) ) {
	throw new Exception( 'Query block did not render only its inner block template per post.' );
}

if ( empty( $GLOBALS['toolbox_blocks_test_reset_postdata'] ) ) {
	throw new Exception( 'Query block did not reset post data.' );
}

echo "Query render regression passed.\n";
