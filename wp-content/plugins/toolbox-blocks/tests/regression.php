<?php
/**
 * Standalone regression checks for Toolbox Blocks server rendering.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ . '/../../../../' );

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $class ) {
		return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
	}
}

if ( ! function_exists( 'tag_escape' ) ) {
	function tag_escape( $tag ) {
		return strtolower( preg_replace( '/[^A-Za-z0-9:_-]/', '', (string) $tag ) );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return preg_match( '/^\s*(?:javascript|vbscript|data):/i', (string) $url ) ? '' : (string) $url;
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $html ) {
		return (string) $html;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ) {
		return abs( (int) $maybeint );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return strtolower( preg_replace( '/[^a-z0-9_-]/i', '', (string) $key ) );
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return $GLOBALS['toolbox_blocks_test_current_post'] ?? 0;
	}
}

if ( ! function_exists( 'get_post_type' ) ) {
	function get_post_type() {
		return 'post';
	}
}

if ( ! function_exists( 'wp_reset_postdata' ) ) {
	function wp_reset_postdata() {}
}

class WP_Query {
	private $posts = array( 101, 102 );
	private $index = -1;

	public function __construct( $args ) {
		$this->posts = array_slice( $this->posts, 0, absint( $args['posts_per_page'] ?? 2 ) );
	}

	public function have_posts() {
		return $this->index + 1 < count( $this->posts );
	}

	public function the_post() {
		++$this->index;
		$GLOBALS['toolbox_blocks_test_current_post'] = $this->posts[ $this->index ];
	}
}

class WP_Block {
	public $parsed_block;
	private $context;

	public function __construct( $parsed_block, $context = array() ) {
		if ( 'toolbox-blocks/query' === ( $parsed_block['blockName'] ?? '' ) ) {
			throw new RuntimeException( 'Query block recursively rendered itself.' );
		}

		$this->parsed_block = $parsed_block;
		$this->context      = $context;
	}

	public function render() {
		return sprintf(
			'<span data-post="%d">%s</span>',
			(int) ( $this->context['postId'] ?? 0 ),
			esc_attr( $this->parsed_block['blockName'] ?? 'unknown' )
		);
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';
require_once __DIR__ . '/../includes/class-block-base.php';
require_once __DIR__ . '/../includes/blocks/class-query.php';
require_once __DIR__ . '/../includes/blocks/class-text.php';

function toolbox_blocks_assert_true( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

function toolbox_blocks_assert_contains( $needle, $haystack, $message ) {
	toolbox_blocks_assert_true( false !== strpos( $haystack, $needle ), $message );
}

function toolbox_blocks_assert_not_contains( $needle, $haystack, $message ) {
	toolbox_blocks_assert_true( false === strpos( $haystack, $needle ), $message );
}

$safe_css = Toolbox_Blocks_CSS_Generator::generate(
	'safe',
	array(
		'desktop' => array(
			'color'      => 'var(--base-3)',
			'background' => 'linear-gradient(180deg, #fff 0%, #000 100%)',
			'fontSize'   => '16px',
		),
	)
);

toolbox_blocks_assert_contains( 'color:var(--base-3)', $safe_css, 'Safe CSS values should be preserved.' );
toolbox_blocks_assert_contains( 'linear-gradient(180deg, #fff 0%, #000 100%)', $safe_css, 'Safe gradients should be preserved.' );

$unsafe_css = Toolbox_Blocks_CSS_Generator::style_tag(
	'unsafe',
	array(
		'desktop' => array(
			'color'            => 'red</style><script>alert(1)</script><style>',
			'bad}body{color'   => 'red',
			'backgroundImage'  => 'url(javascript:alert(1))',
			'width'            => 'calc(100% - 1rem);background:red',
			'height'           => array( 'bad' ),
		),
	)
);

toolbox_blocks_assert_not_contains( '</style><script', $unsafe_css, 'CSS values must not break out of style tags.' );
toolbox_blocks_assert_not_contains( 'bad}body', $unsafe_css, 'CSS property names must not break out of rules.' );
toolbox_blocks_assert_not_contains( 'javascript:', $unsafe_css, 'Unsafe URL schemes must not be emitted in CSS.' );
toolbox_blocks_assert_not_contains( ';background:red', $unsafe_css, 'CSS values must not inject additional declarations.' );

$text_html = Toolbox_Block_Text::render(
	array(
		'tagName' => 'script',
		'content' => 'Hello',
	),
	'',
	null
);

toolbox_blocks_assert_contains( '<p class="tb-block tb-text"', $text_html, 'Invalid text tag should fall back to p.' );
toolbox_blocks_assert_not_contains( '<script', $text_html, 'Invalid tag names must not render executable elements.' );

$query_html = Toolbox_Block_Query::render(
	array(
		'postType'     => 'post',
		'postsPerPage' => 2,
		'uniqueId'     => 'query1',
	),
	'',
	(object) array(
		'parsed_block' => array(
			'blockName'    => 'toolbox-blocks/query',
			'innerBlocks'  => array(
				array( 'blockName' => 'core/post-title' ),
				array( 'blockName' => 'core/post-excerpt' ),
			),
			'innerContent' => array( '<div class="tb-query-saved-wrapper">', null, null, '</div>' ),
		),
	)
);

toolbox_blocks_assert_contains( '<span data-post="101">core/post-title</span>', $query_html, 'Query should render inner template for the first post.' );
toolbox_blocks_assert_contains( '<span data-post="102">core/post-excerpt</span>', $query_html, 'Query should render inner template for the second post.' );
toolbox_blocks_assert_not_contains( 'tb-query-saved-wrapper', $query_html, 'Query should not render its own saved wrapper as template output.' );

echo "Toolbox Blocks regression checks passed.\n";
