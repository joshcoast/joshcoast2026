<?php
/**
 * Standalone regressions for critical Toolbox Blocks render paths.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' );

function __( $text, $domain = 'default' ) {
	return $text;
}

function sanitize_html_class( $class ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function wp_kses_post( $html ) {
	return (string) $html;
}

function sanitize_key( $key ) {
	return strtolower( preg_replace( '/[^a-z0-9_-]/', '', (string) $key ) );
}

function absint( $value ) {
	return abs( (int) $value );
}

function get_post_type( $post = null ) {
	return 'post';
}

function get_the_ID() {
	return $GLOBALS['tb_test_current_post_id'] ?? 0;
}

function wp_reset_postdata() {
	$GLOBALS['tb_test_current_post_id'] = null;
}

class WP_Query {
	private $posts;
	private $index = 0;

	public function __construct( $args ) {
		$this->posts = array( 101, 102 );
		if ( 'post' !== ( $args['post_type'] ?? 'post' ) ) {
			$this->posts = array();
		}
	}

	public function have_posts() {
		return $this->index < count( $this->posts );
	}

	public function the_post() {
		$GLOBALS['tb_test_current_post_id'] = $this->posts[ $this->index ];
		$this->index++;
	}
}

class WP_Block {
	public $parsed_block;
	private $context;

	public function __construct( $parsed_block, $context = array() ) {
		$this->parsed_block = $parsed_block;
		$this->context      = $context;
	}

	public function render() {
		if ( Toolbox_Block_Query::BLOCK_NAME === ( $this->parsed_block['blockName'] ?? '' ) ) {
			throw new RuntimeException( 'Query block recursively rendered itself.' );
		}

		return sprintf(
			'<span data-block="%s" data-post="%d"></span>',
			esc_attr( $this->parsed_block['blockName'] ?? '' ),
			(int) ( $this->context['postId'] ?? 0 )
		);
	}
}

require_once dirname( __DIR__ ) . '/includes/class-css-generator.php';
require_once dirname( __DIR__ ) . '/includes/class-block-base.php';
require_once dirname( __DIR__ ) . '/includes/blocks/class-query.php';

function tb_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$style_tag = Toolbox_Blocks_CSS_Generator::style_tag(
	'abc123',
	array(
		'desktop' => array(
			'backgroundColor' => '#fff',
			'boxShadow'       => '0 2px 8px rgba(0,0,0,.2)',
			'color'           => 'red;} </style><img src=x onerror=alert(1)>',
			'bad;}<svg'       => 'blue',
		),
	)
);

tb_assert( false !== strpos( $style_tag, 'background-color:#fff' ), 'Expected safe background-color declaration.' );
tb_assert( false !== strpos( $style_tag, 'box-shadow:0 2px 8px rgba(0,0,0,.2)' ), 'Expected safe box-shadow declaration.' );
tb_assert( 1 === substr_count( strtolower( $style_tag ), '</style>' ), 'Style tag breakout payload was not removed.' );
tb_assert( false === strpos( $style_tag, '<img' ), 'Markup payload leaked into style tag.' );
tb_assert( false === strpos( $style_tag, '<svg' ), 'Malicious property name leaked into style tag.' );

$query_block = new WP_Block(
	array(
		'blockName'    => Toolbox_Block_Query::BLOCK_NAME,
		'attrs'        => array(),
		'innerBlocks'  => array(
			array(
				'blockName'    => 'core/post-title',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
		),
		'innerHTML'    => '',
		'innerContent' => array(),
	)
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

tb_assert( false !== strpos( $query_html, 'class="tb-block tb-query tb-query123"' ), 'Expected query wrapper class.' );
tb_assert( false !== strpos( $query_html, 'data-post="101"' ), 'Expected first queried post context.' );
tb_assert( false !== strpos( $query_html, 'data-post="102"' ), 'Expected second queried post context.' );

echo "Toolbox Blocks regressions passed.\n";
