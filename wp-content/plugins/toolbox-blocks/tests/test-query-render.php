<?php
/**
 * Standalone regression checks for Toolbox_Block_Query rendering.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ . '/' );

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $classname ) {
		return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $classname );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $key ) );
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ) {
		return abs( (int) $maybeint );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $data ) {
		return (string) $data;
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return $GLOBALS['toolbox_blocks_current_post_id'] ?? 0;
	}
}

if ( ! function_exists( 'wp_reset_postdata' ) ) {
	function wp_reset_postdata() {
		$GLOBALS['toolbox_blocks_reset_postdata'] = true;
	}
}

class WP_Query {
	private $posts = array();
	private $index = 0;

	public function __construct( $args ) {
		$this->posts = $GLOBALS['toolbox_blocks_query_posts'] ?? array();
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
	public static $rendered_block_names = array();

	public $parsed_block;
	public $context;

	public function __construct( $parsed_block, $available_context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $available_context;
	}

	public function render() {
		$name                         = $this->parsed_block['blockName'] ?? '';
		self::$rendered_block_names[] = $name;

		if ( 'toolbox-blocks/query' === $name ) {
			return Toolbox_Block_Query::render( $this->parsed_block['attrs'] ?? array(), '', $this );
		}

		return sprintf(
			'<span data-block="%s" data-post="%d"></span>',
			esc_attr( $name ),
			(int) ( $this->context['postId'] ?? 0 )
		);
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';
require_once __DIR__ . '/../includes/class-block-base.php';
require_once __DIR__ . '/../includes/blocks/class-query.php';

function toolbox_blocks_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, $message . PHP_EOL );
		exit( 1 );
	}
}

$GLOBALS['toolbox_blocks_query_posts']      = array( 101, 102 );
$GLOBALS['toolbox_blocks_reset_postdata']  = false;
$GLOBALS['toolbox_blocks_current_post_id'] = 0;

$parsed_query = array(
	'blockName'   => 'toolbox-blocks/query',
	'attrs'       => array(
		'postType'     => 'post',
		'postsPerPage' => 2,
	),
	'innerBlocks' => array(
		array(
			'blockName' => 'toolbox-blocks/text',
			'attrs'     => array(),
		),
		array(
			'blockName' => 'toolbox-blocks/headline',
			'attrs'     => array(),
		),
	),
);

$outer_block = new WP_Block( $parsed_query );
$output      = Toolbox_Block_Query::render(
	array(
		'postType'     => 'post',
		'postsPerPage' => 2,
	),
	'',
	$outer_block
);

toolbox_blocks_assert(
	0 === count( array_keys( WP_Block::$rendered_block_names, 'toolbox-blocks/query', true ) ),
	'Query render must not re-render the query block itself.'
);
toolbox_blocks_assert(
	2 === substr_count( $output, 'data-post="101"' ) && 2 === substr_count( $output, 'data-post="102"' ),
	'Each inner template block should render once per queried post.'
);
toolbox_blocks_assert(
	true === $GLOBALS['toolbox_blocks_reset_postdata'],
	'Query render should reset postdata after the loop.'
);

fwrite( STDOUT, "Query render regression checks passed.\n" );
