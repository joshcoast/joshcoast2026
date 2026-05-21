<?php
/**
 * Standalone regressions for critical Toolbox Blocks render bugs.
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' );

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $class ) {
		return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
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

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $value ) {
		return (string) $value;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text ) {
		return $text;
	}
}

$toolbox_blocks_test_current_post_id   = 0;
$toolbox_blocks_test_current_post_type = 'post';

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		global $toolbox_blocks_test_current_post_id;
		return $toolbox_blocks_test_current_post_id;
	}
}

if ( ! function_exists( 'get_post_type' ) ) {
	function get_post_type() {
		global $toolbox_blocks_test_current_post_type;
		return $toolbox_blocks_test_current_post_type;
	}
}

if ( ! function_exists( 'wp_reset_postdata' ) ) {
	function wp_reset_postdata() {
	}
}

if ( ! class_exists( 'WP_Query' ) ) {
	class WP_Query {
		private $posts;
		private $current_post = -1;

		public function __construct( $args ) {
			$post_type   = $args['post_type'] ?? 'post';
			$this->posts = array(
				array(
					'id'        => 123,
					'post_type' => $post_type,
				),
			);
		}

		public function have_posts() {
			return $this->current_post + 1 < count( $this->posts );
		}

		public function the_post() {
			global $toolbox_blocks_test_current_post_id, $toolbox_blocks_test_current_post_type;

			++$this->current_post;
			$current                                = $this->posts[ $this->current_post ];
			$toolbox_blocks_test_current_post_id   = $current['id'];
			$toolbox_blocks_test_current_post_type = $current['post_type'];
		}
	}
}

if ( ! class_exists( 'WP_Block' ) ) {
	class WP_Block {
		public $parsed_block;
		public $context;

		public function __construct( $parsed_block, $context = array() ) {
			$this->parsed_block = $parsed_block;
			$this->context      = $context;
		}

		public function render() {
			$block_name = $this->parsed_block['blockName'] ?? '';
			if ( 'toolbox-blocks/query' === $block_name ) {
				throw new RuntimeException( 'Query block rendered itself recursively.' );
			}

			return sprintf(
				'<span class="rendered-child" data-block="%s" data-post-id="%s" data-post-type="%s"></span>',
				esc_attr( $block_name ),
				esc_attr( $this->context['postId'] ?? '' ),
				esc_attr( $this->context['postType'] ?? '' )
			);
		}
	}
}

require_once dirname( __DIR__ ) . '/includes/class-css-generator.php';
require_once dirname( __DIR__ ) . '/includes/class-block-base.php';
require_once dirname( __DIR__ ) . '/includes/blocks/class-query.php';

function toolbox_blocks_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$tests = array(
	'css generator rejects style tag breakout payloads' => function () {
		$tag = Toolbox_Blocks_CSS_Generator::style_tag(
			'abc123',
			array(
				'desktop'      => array(
					'color'                => 'red;} </style><script>alert(1)</script><style>',
					'backgroundColor'      => '#fff',
					'fontFamily'           => 'Inter, sans-serif',
					'bad}</style><script>' => 'blue',
				),
				'desktopHover' => array(
					'backgroundImage' => 'url("javascript:alert(1)")',
				),
			)
		);

		toolbox_blocks_assert( false !== strpos( $tag, 'background-color:#fff' ), 'Expected safe declarations to remain.' );
		toolbox_blocks_assert( false !== strpos( $tag, 'font-family:Inter, sans-serif' ), 'Expected safe font family to remain.' );
		toolbox_blocks_assert( 1 === substr_count( strtolower( $tag ), '</style>' ), 'Payload must not add extra closing style tags.' );
		toolbox_blocks_assert( false === stripos( $tag, '<script' ), 'Script tag payload must be removed.' );
		toolbox_blocks_assert( false === stripos( $tag, 'javascript:' ), 'javascript: CSS URLs must be removed.' );
	},

	'css generator keeps valid layered backgrounds' => function () {
		$css = Toolbox_Blocks_CSS_Generator::generate(
			'hero',
			array(
				'desktop' => array(
					'background'      => 'linear-gradient(90deg, rgba(0,0,0,0.5), transparent)',
					'backgroundImage' => 'url("https://example.test/image.jpg")',
				),
			)
		);

		toolbox_blocks_assert( false !== strpos( $css, '.tb-hero{' ), 'Expected scoped selector.' );
		toolbox_blocks_assert( false !== strpos( $css, 'background-image:linear-gradient(90deg, rgba(0,0,0,0.5), transparent), url("https://example.test/image.jpg")' ), 'Expected safe layered background image.' );
	},

	'query renderer renders only child template blocks' => function () {
		$parent_block = new WP_Block(
			array(
				'blockName'   => 'toolbox-blocks/query',
				'innerBlocks' => array(
					array( 'blockName' => 'toolbox-blocks/headline' ),
					array( 'blockName' => 'toolbox-blocks/text' ),
				),
			),
			array( 'source' => 'outer' )
		);

		$html = Toolbox_Block_Query::render(
			array(
				'postType'     => 'post',
				'postsPerPage' => 1,
			),
			'',
			$parent_block
		);

		toolbox_blocks_assert( false !== strpos( $html, 'class="tb-block tb-query"' ), 'Expected query wrapper.' );
		toolbox_blocks_assert( 2 === substr_count( $html, 'class="rendered-child"' ), 'Expected only inner blocks to render once per post.' );
		toolbox_blocks_assert( false !== strpos( $html, 'data-post-id="123"' ), 'Expected current post context.' );
		toolbox_blocks_assert( false !== strpos( $html, 'data-post-type="post"' ), 'Expected current post type context.' );
	},
);

foreach ( $tests as $name => $test ) {
	$test();
	echo '[PASS] ' . $name . PHP_EOL;
}

echo 'Critical regression tests passed.' . PHP_EOL;
