<?php
/**
 * Standalone regression tests for critical Toolbox Blocks render paths.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', __DIR__ . '/' );

function __( $text, $domain = 'default' ) {
	return $text;
}

function absint( $value ) {
	return abs( (int) $value );
}

function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
}

function sanitize_html_class( $class ) {
	return preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $class );
}

function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function wp_kses_post( $value ) {
	return (string) $value;
}

function get_the_ID() {
	return $GLOBALS['toolbox_blocks_current_post_id'] ?? 0;
}

function wp_reset_postdata() {
	$GLOBALS['toolbox_blocks_current_post_id'] = 0;
}

class WP_Query {
	private $posts = array();
	private $index = -1;

	public function __construct( $query_args ) {
		$this->posts = $GLOBALS['toolbox_blocks_query_posts'] ?? array();
	}

	public function have_posts() {
		return $this->index + 1 < count( $this->posts );
	}

	public function the_post() {
		$this->index++;
		$GLOBALS['toolbox_blocks_current_post_id'] = $this->posts[ $this->index ];
	}
}

class WP_Block {
	public static $query_root_render_count = 0;
	public $parsed_block;
	public $context;

	public function __construct( $parsed_block, $available_context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $available_context;
	}

	public function render() {
		if ( 'toolbox-blocks/query' === ( $this->parsed_block['blockName'] ?? '' ) ) {
			self::$query_root_render_count++;
			return '<!-- recursive query render -->';
		}

		return sprintf(
			'<span data-block="%s" data-post-id="%s"></span>',
			esc_attr( $this->parsed_block['blockName'] ?? '' ),
			esc_attr( $this->context['postId'] ?? '' )
		);
	}
}

require_once __DIR__ . '/../includes/class-css-generator.php';
require_once __DIR__ . '/../includes/class-block-base.php';
require_once __DIR__ . '/../includes/blocks/class-query.php';

function toolbox_blocks_assert_true( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

$GLOBALS['toolbox_blocks_query_posts'] = array( 101, 102 );
$query_block                         = (object) array(
	'context'      => array( 'postType' => 'post' ),
	'parsed_block' => array(
		'blockName'   => 'toolbox-blocks/query',
		'attrs'       => array(),
		'innerBlocks' => array(
			array(
				'blockName'   => 'core/post-title',
				'attrs'       => array(),
				'innerBlocks' => array(),
			),
		),
	),
);

$query_html = Toolbox_Block_Query::render(
	array(
		'uniqueId'     => 'query123',
		'postType'     => 'post',
		'postsPerPage' => 2,
	),
	'',
	$query_block
);

toolbox_blocks_assert_true(
	0 === WP_Block::$query_root_render_count,
	'Query render must not re-render the query block root.'
);
toolbox_blocks_assert_true(
	false !== strpos( $query_html, 'data-post-id="101"' ) && false !== strpos( $query_html, 'data-post-id="102"' ),
	'Query render should render the inner template once per queried post.'
);

$style_html = Toolbox_Blocks_CSS_Generator::style_tag(
	'safe123',
	array(
		'desktop' => array(
			'fontFamily'      => 'Inter, sans-serif',
			'backgroundImage' => 'url("https://example.com/image.jpg")',
			'color'           => 'red;}.owned{display:block',
			'bad}property'    => 'blue',
			'background'      => '</style><script>alert(1)</script>',
		),
	)
);

toolbox_blocks_assert_true(
	false !== strpos( $style_html, 'font-family:Inter, sans-serif' ) && false !== strpos( $style_html, 'background-image:url("https://example.com/image.jpg")' ),
	'CSS generator should preserve valid style declarations.'
);
toolbox_blocks_assert_true(
	1 === substr_count( strtolower( $style_html ), '</style>' ) && false === stripos( $style_html, '<script' ) && false === strpos( $style_html, '.owned' ),
	'CSS generator should reject declarations that can break out of the style tag.'
);

echo "All render regression tests passed.\n";
