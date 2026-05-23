<?php
/**
 * Standalone regression tests for critical Toolbox Blocks render paths.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ . '/' );

function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function sanitize_html_class( $value ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $value );
}

function sanitize_key( $value ) {
	return strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $value ) );
}

function absint( $value ) {
	return abs( (int) $value );
}

function wp_kses_post( $value ) {
	return (string) $value;
}

function __( $text, $domain = 'default' ) {
	return $text;
}

function get_the_ID() {
	return $GLOBALS['toolbox_blocks_test_current_post_id'] ?? 0;
}

function wp_reset_postdata() {
	$GLOBALS['toolbox_blocks_test_postdata_reset'] = true;
}

class WP_Query {
	private $posts = array();
	private $index = -1;

	public function __construct( $args ) {
		$this->posts = $GLOBALS['toolbox_blocks_test_posts'] ?? array();
	}

	public function have_posts() {
		return $this->index + 1 < count( $this->posts );
	}

	public function the_post() {
		$this->index++;
		$GLOBALS['toolbox_blocks_test_current_post_id'] = $this->posts[ $this->index ];
	}
}

class WP_Block {
	public static $rendered_blocks = array();

	private $parsed_block;
	private $available_context;

	public function __construct( $parsed_block, $available_context = array() ) {
		$this->parsed_block       = $parsed_block;
		$this->available_context  = $available_context;
	}

	public function render() {
		$block_name = $this->parsed_block['blockName'] ?? '';
		if ( 'toolbox-blocks/query' === $block_name ) {
			throw new RuntimeException( 'Query block attempted to render itself recursively.' );
		}

		self::$rendered_blocks[] = array(
			'blockName' => $block_name,
			'context'   => $this->available_context,
		);

		return sprintf(
			'<span data-block="%s" data-post="%d"></span>',
			esc_attr( $block_name ),
			(int) ( $this->available_context['postId'] ?? 0 )
		);
	}
}

require_once dirname( __DIR__ ) . '/includes/class-css-generator.php';
require_once dirname( __DIR__ ) . '/includes/class-block-base.php';
require_once dirname( __DIR__ ) . '/includes/blocks/class-query.php';

function assert_true( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

function assert_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException(
			$message . ' Expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) . '.'
		);
	}
}

function assert_contains( $needle, $haystack, $message ) {
	assert_true( false !== strpos( $haystack, $needle ), $message );
}

function assert_not_contains( $needle, $haystack, $message ) {
	assert_true( false === strpos( $haystack, $needle ), $message );
}

function test_css_generator_rejects_style_breakout_values() {
	$css = Toolbox_Blocks_CSS_Generator::generate(
		'safe-id',
		array(
			'desktop' => array(
				'color'           => 'red',
				'background'      => 'linear-gradient(180deg, #000 0%, rgba(255,255,255,0.5) 100%)',
				'backgroundImage' => 'url("https://example.com/image.png")',
				'backgroundColor' => 'red</style><script>alert(1)</script><style>',
				'borderColor'     => 'red}.evil{color:red',
				'fontSize'        => '16px;background:url("https://evil.test/x")',
				'bad;Property'    => 'blue',
			),
		)
	);

	assert_contains( 'color:red', $css, 'Safe declarations should still render.' );
	assert_contains( 'background-image:linear-gradient(180deg, #000 0%, rgba(255,255,255,0.5) 100%), url("https://example.com/image.png")', $css, 'Safe layered background declarations should still render.' );
	assert_not_contains( '</style', $css, 'CSS output must not allow style tag breakout.' );
	assert_not_contains( '<script', $css, 'CSS output must not include script markup.' );
	assert_not_contains( '.evil', $css, 'CSS output must not allow rule breakout.' );
	assert_not_contains( 'font-size', $css, 'CSS values must not inject additional declarations.' );
	assert_not_contains( 'bad;-property', $css, 'CSS property names must be validated.' );
}

function test_query_block_renders_only_inner_blocks_per_post() {
	$GLOBALS['toolbox_blocks_test_posts']          = array( 101, 202 );
	$GLOBALS['toolbox_blocks_test_postdata_reset'] = false;
	WP_Block::$rendered_blocks                     = array();

	$block = (object) array(
		'parsed_block' => array(
			'blockName'   => 'toolbox-blocks/query',
			'innerBlocks' => array(
				array( 'blockName' => 'core/post-title' ),
				array( 'blockName' => 'toolbox-blocks/text' ),
			),
		),
	);

	$html = Toolbox_Block_Query::render(
		array(
			'uniqueId'     => 'query-one',
			'postType'     => 'post',
			'postsPerPage' => 2,
		),
		'',
		$block
	);

	assert_contains( 'data-post="101"', $html, 'First loop post should render inner blocks.' );
	assert_contains( 'data-post="202"', $html, 'Second loop post should render inner blocks.' );
	assert_same( 4, count( WP_Block::$rendered_blocks ), 'Each saved inner block should render once per post.' );
	assert_same( true, $GLOBALS['toolbox_blocks_test_postdata_reset'], 'Query render should reset global post data.' );

	$contexts = array_map(
		static function ( $entry ) {
			return $entry['context'];
		},
		WP_Block::$rendered_blocks
	);
	assert_same(
		array(
			array( 'postId' => 101, 'postType' => 'post' ),
			array( 'postId' => 101, 'postType' => 'post' ),
			array( 'postId' => 202, 'postType' => 'post' ),
			array( 'postId' => 202, 'postType' => 'post' ),
		),
		$contexts,
		'Inner blocks should receive the current loop post context.'
	);
}

test_css_generator_rejects_style_breakout_values();
test_query_block_renders_only_inner_blocks_per_post();

echo "Critical render regression tests passed.\n";
