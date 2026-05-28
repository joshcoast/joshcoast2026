<?php
/**
 * Standalone regression checks for critical Toolbox Blocks render bugs.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ . '/../../../../' );

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

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $value ) {
		return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $value ) {
		return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $value );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $value ) {
		return preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $value ) );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $value ) {
		return (string) $value;
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	function wp_strip_all_tags( $value ) {
		return strip_tags( (string) $value );
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return $GLOBALS['tb_test_post']['ID'] ?? 0;
	}
}

if ( ! function_exists( 'get_post_type' ) ) {
	function get_post_type() {
		return $GLOBALS['tb_test_post']['post_type'] ?? 'post';
	}
}

if ( ! function_exists( 'wp_reset_postdata' ) ) {
	function wp_reset_postdata() {
		$GLOBALS['tb_test_post'] = null;
	}
}

class WP_Query {
	public static $posts = array();
	private $index       = -1;

	public function __construct( $args ) {}

	public function have_posts() {
		return ( $this->index + 1 ) < count( self::$posts );
	}

	public function the_post() {
		$this->index++;
		$GLOBALS['tb_test_post'] = self::$posts[ $this->index ];
	}
}

class WP_Block {
	public static $rendered_blocks = array();

	public $parsed_block;
	public $context;

	public function __construct( $parsed_block, $context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $context;
	}

	public function render() {
		$block_name              = $this->parsed_block['blockName'] ?? '';
		self::$rendered_blocks[] = $block_name;

		if ( 'toolbox-blocks/query' === $block_name ) {
			throw new RuntimeException( 'Query block rendered itself recursively.' );
		}

		return sprintf(
			'<span data-block="%s" data-post="%d"></span>',
			esc_attr( $block_name ),
			(int) ( $this->context['postId'] ?? 0 )
		);
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';
require_once __DIR__ . '/../includes/class-block-base.php';
require_once __DIR__ . '/../includes/blocks/class-query.php';

function tb_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

function tb_test_css_generator_rejects_style_breakout() {
	$output = Toolbox_Blocks_CSS_Generator::style_tag(
		'safe-id',
		array(
			'desktop' => array(
				'color'                    => 'red',
				'width'                    => 'calc(100% - 1rem)',
				'boxShadow'                => '0 0 red</style><img src=x onerror=alert(1)>',
				'background'               => 'red;background:url(javascript:alert(1))',
				'color}body{background'    => 'black',
			),
		)
	);

	tb_assert( false !== strpos( $output, 'color:red' ), 'Expected safe color declaration.' );
	tb_assert( false !== strpos( $output, 'width:calc(100% - 1rem)' ), 'Expected safe calc declaration.' );
	tb_assert( 1 === substr_count( strtolower( $output ), '</style>' ), 'Expected only the wrapper closing style tag.' );
	tb_assert( false === stripos( $output, '<img' ), 'Unsafe markup must not escape the style tag.' );
	tb_assert( false === strpos( $output, 'javascript:' ), 'Unsafe javascript URL must be removed.' );
	tb_assert( false === strpos( $output, 'color}body' ), 'Unsafe property name must be removed.' );
}

function tb_test_query_renders_only_inner_blocks() {
	WP_Query::$posts           = array(
		array(
			'ID'        => 101,
			'post_type' => 'post',
		),
		array(
			'ID'        => 102,
			'post_type' => 'post',
		),
	);
	WP_Block::$rendered_blocks = array();

	$query_block = new WP_Block(
		array(
			'blockName'   => 'toolbox-blocks/query',
			'innerBlocks' => array(
				array( 'blockName' => 'toolbox-blocks/headline' ),
				array( 'blockName' => 'toolbox-blocks/text' ),
			),
		),
		array( 'postType' => 'page' )
	);

	$output = Toolbox_Block_Query::render(
		array(
			'uniqueId'     => 'query123',
			'postType'     => 'post',
			'postsPerPage' => 2,
		),
		'',
		$query_block
	);

	tb_assert( 2 === substr_count( $output, 'data-block="toolbox-blocks/headline"' ), 'Expected headline template once per post.' );
	tb_assert( 2 === substr_count( $output, 'data-block="toolbox-blocks/text"' ), 'Expected text template once per post.' );
	tb_assert( false === in_array( 'toolbox-blocks/query', WP_Block::$rendered_blocks, true ), 'Query block must not render itself.' );
	tb_assert( false !== strpos( $output, 'data-post="101"' ), 'Expected first post context.' );
	tb_assert( false !== strpos( $output, 'data-post="102"' ), 'Expected second post context.' );
}

$tests = array(
	'tb_test_css_generator_rejects_style_breakout',
	'tb_test_query_renders_only_inner_blocks',
);

foreach ( $tests as $test ) {
	$test();
	echo $test . " passed\n";
}

echo "All regression tests passed\n";
