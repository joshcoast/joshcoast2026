<?php
/**
 * Regression tests for the Toolbox Blocks query renderer.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ );

if ( ! function_exists( '__' ) ) {
	function __( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $value ) {
		return abs( (int) $value );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $key ) );
	}
}

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

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $text ) {
		return (string) $text;
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return $GLOBALS['tb_current_post_id'] ?? 0;
	}
}

if ( ! function_exists( 'wp_reset_postdata' ) ) {
	function wp_reset_postdata() {
		$GLOBALS['tb_postdata_reset'] = true;
	}
}

class WP_Query {
	private $posts = array( 101, 102 );
	private $index = 0;

	public function __construct( $args ) {}

	public function have_posts() {
		return $this->index < count( $this->posts );
	}

	public function the_post() {
		$GLOBALS['tb_current_post_id'] = $this->posts[ $this->index ];
		$this->index++;
	}
}

class WP_Block {
	public static $rendered_block_names = array();

	public $parsed_block;
	public $inner_blocks = array();
	public $context      = array();

	public function __construct( $parsed_block, $available_context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $available_context;

		foreach ( $parsed_block['innerBlocks'] ?? array() as $inner_block ) {
			$this->inner_blocks[] = new WP_Block( $inner_block, $available_context );
		}
	}

	public function render() {
		$name = $this->parsed_block['blockName'] ?? '';
		self::$rendered_block_names[] = $name;

		if ( 'toolbox-blocks/query' === $name ) {
			throw new RuntimeException( 'Query renderer re-entered itself.' );
		}

		return sprintf(
			'<span data-block="%s" data-post="%s" data-inherited="%s"></span>',
			esc_attr( $name ),
			esc_attr( $this->context['postId'] ?? '' ),
			esc_attr( $this->context['inherited'] ?? '' )
		);
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';
require_once __DIR__ . '/../includes/class-block-base.php';
require_once __DIR__ . '/../includes/blocks/class-query.php';

function tb_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, $message . PHP_EOL );
		exit( 1 );
	}
}

$parsed_query = array(
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

$output = Toolbox_Block_Query::render(
	array(
		'postType'     => 'post',
		'postsPerPage' => 2,
	),
	'',
	new WP_Block( $parsed_query, array( 'inherited' => 'parent-context' ) )
);

tb_assert( false !== strpos( $output, 'data-post="101"' ), 'First queried post should render the inner template.' );
tb_assert( false !== strpos( $output, 'data-post="102"' ), 'Second queried post should render the inner template.' );
tb_assert( false !== strpos( $output, 'data-inherited="parent-context"' ), 'Existing block context should be preserved.' );
tb_assert( array( 'core/post-title', 'core/post-title' ) === WP_Block::$rendered_block_names, 'Query renderer should render only inner blocks.' );
tb_assert( true === ( $GLOBALS['tb_postdata_reset'] ?? false ), 'Query renderer should reset post data after rendering.' );

echo 'Query render tests passed.' . PHP_EOL;
