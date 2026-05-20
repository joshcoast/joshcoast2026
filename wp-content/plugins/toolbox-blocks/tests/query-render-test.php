<?php
/**
 * Regression checks for the Toolbox Query block renderer.
 *
 * Run with: php tests/query-render-test.php
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ . '/' );

function sanitize_html_class( $value ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $value );
}

function sanitize_key( $value ) {
	return strtolower( preg_replace( '/[^a-z0-9_-]/i', '', (string) $value ) );
}

function absint( $value ) {
	return abs( (int) $value );
}

function wp_kses_post( $value ) {
	return (string) $value;
}

function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function __( $text, $domain = null ) {
	return $text;
}

function get_the_ID() {
	return $GLOBALS['toolbox_blocks_current_post_id'] ?? 0;
}

function wp_reset_postdata() {
	$GLOBALS['toolbox_blocks_current_post_id'] = 0;
}

class WP_Query {
	private $posts = array( 101, 202 );
	private $index = 0;

	public function __construct( $args ) {
		if ( 2 !== $args['posts_per_page'] ) {
			throw new RuntimeException( 'Expected query args to preserve posts_per_page.' );
		}
	}

	public function have_posts() {
		return $this->index < count( $this->posts );
	}

	public function the_post() {
		$GLOBALS['toolbox_blocks_current_post_id'] = $this->posts[ $this->index ];
		$this->index++;
	}
}

class WP_Block {
	public $parsed_block;
	private $context;
	public static $rendered = array();

	public function __construct( $parsed_block, $context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $context;
	}

	public function render() {
		if ( 'toolbox-blocks/query' === ( $this->parsed_block['blockName'] ?? '' ) ) {
			throw new RuntimeException( 'Query block recursively rendered itself.' );
		}

		self::$rendered[] = array(
			'name'    => $this->parsed_block['blockName'] ?? '',
			'post_id' => $this->context['postId'] ?? 0,
		);

		return sprintf(
			'<span data-block="%s" data-post="%d"></span>',
			esc_attr( $this->parsed_block['blockName'] ?? '' ),
			(int) ( $this->context['postId'] ?? 0 )
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
	),
	array()
);

$html = Toolbox_Block_Query::render(
	array(
		'uniqueId'     => 'query-test',
		'postType'     => 'post',
		'postsPerPage' => 2,
	),
	'',
	$query_block
);

$expected_rendered = array(
	array(
		'name'    => 'core/post-title',
		'post_id' => 101,
	),
	array(
		'name'    => 'core/post-excerpt',
		'post_id' => 101,
	),
	array(
		'name'    => 'core/post-title',
		'post_id' => 202,
	),
	array(
		'name'    => 'core/post-excerpt',
		'post_id' => 202,
	),
);

if ( $expected_rendered !== WP_Block::$rendered ) {
	fwrite( STDERR, 'Inner blocks were not rendered once per queried post with post context.' . PHP_EOL );
	exit( 1 );
}

if ( false === strpos( $html, 'class="tb-block tb-query tb-query-test"' ) ) {
	fwrite( STDERR, 'Query wrapper markup was not rendered as expected.' . PHP_EOL );
	exit( 1 );
}

echo 'Query render regression test passed.' . PHP_EOL;
