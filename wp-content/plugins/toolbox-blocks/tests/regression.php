<?php
/**
 * Standalone regression checks for critical Toolbox Blocks render paths.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ . '/../../../../' );

function tb_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

function tag_escape( $tag_name ) {
	return preg_replace( '/[^a-zA-Z0-9:_-]/', '', strtolower( (string) $tag_name ) );
}

function sanitize_html_class( $class ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
}

function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $key ) );
}

function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function esc_url( $value ) {
	return filter_var( (string) $value, FILTER_SANITIZE_URL );
}

function wp_kses_post( $value ) {
	return (string) $value;
}

function absint( $value ) {
	return abs( (int) $value );
}

function __( $text, $domain = 'default' ) {
	return $text;
}

function get_the_ID() {
	return $GLOBALS['tb_test_current_post']['ID'] ?? 0;
}

function get_post_type() {
	return $GLOBALS['tb_test_current_post']['post_type'] ?? 'post';
}

function wp_reset_postdata() {
	$GLOBALS['tb_test_current_post'] = null;
}

class WP_Query {
	public static $posts = array();

	private $index = 0;

	public function __construct( $args ) {}

	public function have_posts() {
		return $this->index < count( self::$posts );
	}

	public function the_post() {
		$GLOBALS['tb_test_current_post'] = self::$posts[ $this->index ];
		++$this->index;
	}
}

class WP_Block {
	public static $rendered_blocks = array();

	public $parsed_block;
	private $context;

	public function __construct( $parsed_block, $context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $context;
	}

	public function render() {
		$block_name = $this->parsed_block['blockName'] ?? '';
		if ( 'toolbox-blocks/query' === $block_name ) {
			throw new RuntimeException( 'Query block attempted to render itself recursively.' );
		}

		self::$rendered_blocks[] = array(
			'blockName' => $block_name,
			'context'   => $this->context,
		);

		return '[' . $block_name . ':' . ( $this->context['postId'] ?? 0 ) . ']';
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';
require_once __DIR__ . '/../includes/class-block-base.php';
require_once __DIR__ . '/../includes/blocks/class-query.php';
require_once __DIR__ . '/../includes/blocks/class-headline.php';
require_once __DIR__ . '/../includes/blocks/class-text.php';

$css = Toolbox_Blocks_CSS_Generator::style_tag(
	'abc123',
	array(
		'desktop' => array(
			'color'                  => '#fff',
			'backgroundImage'        => 'url("https://example.com/image.jpg")',
			'badProp}body{display'   => 'block',
			'borderColor'            => 'red}</style><img src=x onerror=alert(1)>',
			'paddingTop'             => '10px;body{display:none}',
		),
	)
);

tb_assert( false !== strpos( $css, 'color:#fff' ), 'safe CSS declaration should be emitted.' );
tb_assert( false !== strpos( $css, 'background-image:url("https://example.com/image.jpg")' ), 'safe CSS url should be emitted.' );
tb_assert( false === strpos( $css, '<img' ), 'CSS values must not break out of the style tag.' );
tb_assert( false === strpos( $css, 'bad-prop' ), 'Unsafe CSS property names must be rejected.' );
tb_assert( false === strpos( $css, 'padding-top' ), 'CSS declaration breakout characters must be rejected.' );

$headline = Toolbox_Block_Headline::render(
	array(
		'tagName' => 'script',
		'content' => 'alert(1)',
	),
	'',
	null
);
$text     = Toolbox_Block_Text::render(
	array(
		'tagName' => 'script',
		'content' => 'alert(1)',
	),
	'',
	null
);

tb_assert( false === strpos( $headline, '<script' ), 'Headline must reject executable tag names.' );
tb_assert( false !== strpos( $headline, '<h2' ), 'Headline should fall back to h2.' );
tb_assert( false === strpos( $text, '<script' ), 'Text must reject executable tag names.' );
tb_assert( false !== strpos( $text, '<p' ), 'Text should fall back to p.' );

WP_Query::$posts = array(
	array(
		'ID'        => 101,
		'post_type' => 'post',
	),
	array(
		'ID'        => 102,
		'post_type' => 'post',
	),
);

$query_block = (object) array(
	'parsed_block' => array(
		'blockName'   => 'toolbox-blocks/query',
		'attrs'       => array(),
		'innerBlocks' => array(
			array(
				'blockName'   => 'toolbox-blocks/headline',
				'attrs'       => array(),
				'innerBlocks' => array(),
			),
			array(
				'blockName'   => 'toolbox-blocks/text',
				'attrs'       => array(),
				'innerBlocks' => array(),
			),
		),
	),
);

$query_html = Toolbox_Block_Query::render(
	array(
		'postType'     => 'post',
		'postsPerPage' => 10,
	),
	'',
	$query_block
);

tb_assert( 4 === count( WP_Block::$rendered_blocks ), 'Query should render each inner template block for each post.' );
tb_assert( 101 === WP_Block::$rendered_blocks[0]['context']['postId'], 'First post context should be passed to inner blocks.' );
tb_assert( 102 === WP_Block::$rendered_blocks[2]['context']['postId'], 'Second post context should be passed to inner blocks.' );
tb_assert( false !== strpos( $query_html, 'data-post-id="101"' ), 'Query output should include the first post item.' );
tb_assert( false !== strpos( $query_html, 'data-post-id="102"' ), 'Query output should include the second post item.' );

echo "Toolbox Blocks regression checks passed.\n";
