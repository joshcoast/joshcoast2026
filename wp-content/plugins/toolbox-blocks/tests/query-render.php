<?php
/**
 * Regression coverage for the Toolbox Query block render callback.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' );

function __( $text, $domain = null ) {
	return $text;
}

function absint( $value ) {
	return abs( (int) $value );
}

function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function sanitize_html_class( $value ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $value );
}

function sanitize_key( $key ) {
	return strtolower( preg_replace( '/[^a-zA-Z0-9_\-]/', '', (string) $key ) );
}

function wp_kses_post( $value ) {
	return (string) $value;
}

function get_the_ID() {
	return WP_Query::$current_post_id;
}

function wp_reset_postdata() {
	WP_Query::$reset_postdata_called = true;
}

class WP_Query {
	public static $current_post_id        = 0;
	public static $reset_postdata_called = false;

	private $index = 0;
	private $posts = array( 101, 102 );

	public function __construct( $args ) {
	}

	public function have_posts() {
		return $this->index < count( $this->posts );
	}

	public function the_post() {
		self::$current_post_id = $this->posts[ $this->index ];
		$this->index++;
	}
}

class WP_Block {
	public static $rendered_blocks = array();

	public $parsed_block;
	public $inner_blocks = array();

	private $context;

	public function __construct( $parsed_block, $context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $context;

		foreach ( $parsed_block['innerBlocks'] ?? array() as $inner_block ) {
			$this->inner_blocks[] = new self( $inner_block, $context );
		}
	}

	public function render() {
		$name = $this->parsed_block['blockName'] ?? '';

		if ( 'toolbox-blocks/query' === $name ) {
			throw new RuntimeException( 'Query block was recursively rendered.' );
		}

		self::$rendered_blocks[] = array(
			'name'    => $name,
			'context' => $this->context,
		);

		return sprintf(
			'<span data-post="%d">%s</span>',
			$this->context['postId'] ?? 0,
			esc_attr( $name )
		);
	}
}

function toolbox_blocks_test_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';
require_once __DIR__ . '/../includes/class-block-base.php';
require_once __DIR__ . '/../includes/blocks/class-query.php';

$parsed_query_block = array(
	'blockName'   => 'toolbox-blocks/query',
	'attrs'       => array(),
	'innerBlocks' => array(
		array(
			'blockName'   => 'core/post-title',
			'attrs'       => array(),
			'innerBlocks' => array(),
		),
	),
);

$query_block               = new stdClass();
$query_block->parsed_block = $parsed_query_block;
$query_block->inner_blocks = array(
	new WP_Block( $parsed_query_block['innerBlocks'][0] ),
);

$html = Toolbox_Block_Query::render(
	array(
		'postType'     => 'post',
		'postsPerPage' => 2,
	),
	'',
	$query_block
);

toolbox_blocks_test_assert(
	2 === count( WP_Block::$rendered_blocks ),
	'Expected the inner template block to render once for each post.'
);
toolbox_blocks_test_assert(
	array( 101, 102 ) === array_map(
		function ( $rendered_block ) {
			return $rendered_block['context']['postId'] ?? null;
		},
		WP_Block::$rendered_blocks
	),
	'Expected each inner block render to receive the current post ID context.'
);
toolbox_blocks_test_assert(
	false !== strpos( $html, 'data-post="101"' ) && false !== strpos( $html, 'data-post="102"' ),
	'Expected rendered markup for both queried posts.'
);
toolbox_blocks_test_assert(
	WP_Query::$reset_postdata_called,
	'Expected query rendering to reset global post data.'
);

echo "Query render regression test passed.\n";
