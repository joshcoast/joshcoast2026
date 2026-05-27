<?php
/**
 * Standalone regression checks for critical Toolbox Blocks rendering paths.
 *
 * Run with: php wp-content/plugins/toolbox-blocks/tests/regression.php
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' );

function tb_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new Exception( $message );
	}
}

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $class ) {
		return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $value ) {
		return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $key ) );
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $value ) {
		return abs( (int) $value );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $html ) {
		return (string) $html;
	}
}

if ( ! function_exists( 'wp_reset_postdata' ) ) {
	function wp_reset_postdata() {}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return $GLOBALS['tb_test_current_post_id'] ?? 0;
	}
}

if ( ! function_exists( 'get_post_type_object' ) ) {
	function get_post_type_object( $post_type ) {
		$post_types = array(
			'post'       => (object) array(
				'name'               => 'post',
				'public'             => true,
				'publicly_queryable' => true,
			),
			'page'       => (object) array(
				'name'               => 'page',
				'public'             => true,
				'publicly_queryable' => false,
			),
			'secret'     => (object) array(
				'name'               => 'secret',
				'public'             => false,
				'publicly_queryable' => false,
			),
			'attachment' => (object) array(
				'name'               => 'attachment',
				'public'             => true,
				'publicly_queryable' => true,
			),
		);
		return $post_types[ $post_type ] ?? null;
	}
}

if ( ! function_exists( 'is_post_type_viewable' ) ) {
	function is_post_type_viewable( $post_type_object ) {
		return ! empty( $post_type_object->public ) || ! empty( $post_type_object->publicly_queryable );
	}
}

class WP_Query {
	private $posts;
	private $index = -1;

	public function __construct( $args ) {
		$GLOBALS['tb_test_query_args'] = $args;
		$this->posts                   = array( 101, 102 );
	}

	public function have_posts() {
		return $this->index + 1 < count( $this->posts );
	}

	public function the_post() {
		++$this->index;
		$GLOBALS['tb_test_current_post_id'] = $this->posts[ $this->index ];
	}
}

class WP_Block {
	public $parsed_block;
	public $context;

	public function __construct( $parsed_block, $context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $context;
	}

	public function render() {
		if ( 'toolbox-blocks/query' === ( $this->parsed_block['blockName'] ?? '' ) ) {
			throw new Exception( 'Query render recursed into itself.' );
		}

		return sprintf(
			'<span data-block="%s" data-post="%d"></span>',
			esc_attr( $this->parsed_block['blockName'] ?? 'unknown' ),
			(int) ( $this->context['postId'] ?? 0 )
		);
	}
}

require_once dirname( __DIR__ ) . '/includes/class-css-generator.php';
require_once dirname( __DIR__ ) . '/includes/class-block-base.php';
require_once dirname( __DIR__ ) . '/includes/blocks/class-query.php';

$payload = 'red;}</style><img src=x onerror=alert(1)><style>';
$css     = Toolbox_Blocks_CSS_Generator::generate_for_selector(
	'.tb-test',
	array(
		'desktop' => array(
			'color'       => '#fff',
			'boxShadow'   => $payload,
			'bad;prop'    => 'red',
			'unknownProp' => 'blue',
		),
	)
);
tb_assert( '.tb-test{color:#fff}' === $css, 'CSS sanitizer should keep safe declarations and drop unsafe ones.' );
tb_assert( false === strpos( $css, '</style>' ), 'CSS output must not contain style-tag breakout payloads.' );

$layered_css = Toolbox_Blocks_CSS_Generator::generate_for_selector(
	'.tb-test',
	array(
		'desktop' => array(
			'background'      => 'linear-gradient(180deg, #000 0%, #fff 100%)',
			'backgroundImage' => 'url("https://example.test/image.jpg")',
		),
	)
);
tb_assert(
	'.tb-test{background-image:linear-gradient(180deg, #000 0%, #fff 100%), url("https://example.test/image.jpg")}' === $layered_css,
	'Safe layered background CSS should still render.'
);

$query_block = new stdClass();
$query_block->parsed_block = array(
	'blockName'   => 'toolbox-blocks/query',
	'innerBlocks' => array(
		array( 'blockName' => 'toolbox-blocks/headline' ),
	),
);
$query_block->context      = array();

$query_html = Toolbox_Block_Query::render(
	array(
		'uniqueId'      => 'query1',
		'postType'      => 'secret',
		'postsPerPage'  => 2,
		'noResultsText' => 'No posts',
	),
	'',
	$query_block
);

tb_assert( 'post' === $GLOBALS['tb_test_query_args']['post_type'], 'Non-viewable post types should fall back to posts.' );
tb_assert( 2 === substr_count( $query_html, 'data-block="toolbox-blocks/headline"' ), 'Query should render inner template blocks for each post.' );
tb_assert( false === strpos( $query_html, 'data-block="toolbox-blocks/query"' ), 'Query should not render itself recursively.' );
tb_assert( false !== strpos( $query_html, 'data-post="101"' ), 'First post context should reach inner blocks.' );
tb_assert( false !== strpos( $query_html, 'data-post="102"' ), 'Second post context should reach inner blocks.' );

echo "Toolbox Blocks regressions passed.\n";
