<?php
/**
 * Standalone regression checks for Toolbox Blocks server-side rendering.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ . '/../../../../' );

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
	function __( $text, $domain = null ) {
		return $text;
	}
}

if ( ! function_exists( 'wp_reset_postdata' ) ) {
	function wp_reset_postdata() {
		$GLOBALS['toolbox_blocks_test_reset_postdata'] = true;
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return $GLOBALS['toolbox_blocks_test_current_post_id'] ?? 0;
	}
}

class WP_Query {
	private $posts = array( 101, 102 );
	private $index = -1;

	public function __construct( $args ) {
		$GLOBALS['toolbox_blocks_test_query_args'] = $args;
	}

	public function have_posts() {
		return ( $this->index + 1 ) < count( $this->posts );
	}

	public function the_post() {
		$this->index++;
		$GLOBALS['toolbox_blocks_test_current_post_id'] = $this->posts[ $this->index ];
	}
}

class WP_Block {
	public $parsed_block;
	private $context;

	public function __construct( $parsed_block, $available_context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $available_context;
		$GLOBALS['toolbox_blocks_test_rendered_block_names'][] = $parsed_block['blockName'] ?? '';
	}

	public function render() {
		if ( 'toolbox-blocks/query' === ( $this->parsed_block['blockName'] ?? '' ) ) {
			throw new Exception( 'Query block rendered itself recursively.' );
		}

		return sprintf(
			'<article data-post-id="%d">%s</article>',
			(int) ( $this->context['postId'] ?? 0 ),
			$this->parsed_block['innerHTML'] ?? ''
		);
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';
require_once __DIR__ . '/../includes/class-block-base.php';
require_once __DIR__ . '/../includes/blocks/class-query.php';

function toolbox_blocks_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

$style_tag = Toolbox_Blocks_CSS_Generator::style_tag(
	'abc123',
	array(
		'desktop' => array(
			'color'           => 'var(--base-3)',
			'fontFamily'      => '"Inter", sans-serif',
			'background'      => 'linear-gradient(180deg, rgba(0,0,0,.4), rgba(0,0,0,.1))',
			'backgroundImage' => 'url("https://example.com/photo.jpg")',
			'bad;prop'        => 'display:block',
			'borderColor'     => '</style><script>alert(1)</script>',
			'boxShadow'       => '0 0 0 1px red;position:fixed',
		),
	)
);

toolbox_blocks_assert( str_contains( $style_tag, 'color:var(--base-3)' ), 'safe CSS values should render.' );
toolbox_blocks_assert( str_contains( $style_tag, 'font-family:"Inter", sans-serif' ), 'quoted CSS values should render.' );
toolbox_blocks_assert( str_contains( $style_tag, 'background-image:linear-gradient' ), 'layered backgrounds should still render.' );
toolbox_blocks_assert( ! str_contains( $style_tag, '<script' ), 'style tag should not contain script markup.' );
toolbox_blocks_assert( ! str_contains( $style_tag, 'bad;prop' ), 'unsafe CSS property names should be dropped.' );
toolbox_blocks_assert( ! str_contains( $style_tag, 'position:fixed' ), 'declaration breakout values should be dropped.' );

$GLOBALS['toolbox_blocks_test_rendered_block_names'] = array();
$query_block = new WP_Block(
	array(
		'blockName'    => 'toolbox-blocks/query',
		'attrs'        => array(),
		'innerBlocks'  => array(
			array(
				'blockName' => 'core/post-title',
				'attrs'     => array(),
				'innerHTML' => 'Title',
			),
		),
		'innerHTML'    => '',
		'innerContent' => array(),
	)
);

$rendered = Toolbox_Block_Query::render(
	array(
		'uniqueId'     => 'query123',
		'postType'     => 'post',
		'postsPerPage' => 2,
	),
	'',
	$query_block
);

toolbox_blocks_assert( str_contains( $rendered, 'data-post-id="101"' ), 'first queried post should render.' );
toolbox_blocks_assert( str_contains( $rendered, 'data-post-id="102"' ), 'second queried post should render.' );
toolbox_blocks_assert(
	2 === count( array_keys( $GLOBALS['toolbox_blocks_test_rendered_block_names'], 'core/post-title', true ) ),
	'inner template block should render once for each queried post.'
);
toolbox_blocks_assert(
	1 === count( array_keys( $GLOBALS['toolbox_blocks_test_rendered_block_names'], 'toolbox-blocks/query', true ) ),
	'query block should not render itself inside the loop.'
);
toolbox_blocks_assert( ! empty( $GLOBALS['toolbox_blocks_test_reset_postdata'] ), 'post data should be reset after loop.' );

echo "All Toolbox Blocks regression checks passed.\n";
