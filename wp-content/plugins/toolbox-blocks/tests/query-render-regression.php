<?php
/**
 * Regression coverage for the Query block render callback.
 *
 * Run with:
 * php wp-content/plugins/toolbox-blocks/tests/query-render-regression.php
 *
 * @package ToolboxBlocks
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return strtolower( preg_replace( '/[^a-zA-Z0-9_\-]/', '', (string) $key ) );
	}
}

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $class ) {
		return preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $class );
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ) {
		return abs( (int) $maybeint );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $data ) {
		return (string) $data;
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return $GLOBALS['toolbox_blocks_current_post']['ID'] ?? 0;
	}
}

if ( ! function_exists( 'get_post_type' ) ) {
	function get_post_type() {
		return $GLOBALS['toolbox_blocks_current_post']['post_type'] ?? false;
	}
}

if ( ! function_exists( 'wp_reset_postdata' ) ) {
	function wp_reset_postdata() {
		$GLOBALS['toolbox_blocks_current_post'] = null;
	}
}

if ( ! class_exists( 'Toolbox_Blocks_CSS_Generator' ) ) {
	class Toolbox_Blocks_CSS_Generator {
		public static function style_tag( $unique_id, $styles ) {
			return '';
		}
	}
}

if ( ! class_exists( 'WP_Query' ) ) {
	class WP_Query {
		public static $posts = array();

		private $matched_posts = array();
		private $index = 0;

		public function __construct( $args ) {
			$offset     = absint( $args['offset'] ?? 0 );
			$per_page   = absint( $args['posts_per_page'] ?? count( self::$posts ) );
			$this->matched_posts = array_slice( self::$posts, $offset, $per_page );
		}

		public function have_posts() {
			return $this->index < count( $this->matched_posts );
		}

		public function the_post() {
			$GLOBALS['toolbox_blocks_current_post'] = $this->matched_posts[ $this->index ];
			++$this->index;
		}
	}
}

if ( ! class_exists( 'WP_Block' ) ) {
	class WP_Block {
		public static $rendered_blocks = array();

		private $parsed_block;
		private $context;

		public function __construct( $parsed_block, $available_context = array() ) {
			$this->parsed_block = $parsed_block;
			$this->context      = $available_context;
		}

		public function render() {
			$block_name = $this->parsed_block['blockName'] ?? '';

			if ( Toolbox_Block_Query::BLOCK_NAME === $block_name ) {
				throw new RuntimeException( 'Query block attempted to render itself recursively.' );
			}

			self::$rendered_blocks[] = array(
				'blockName' => $block_name,
				'postId'    => $this->context['postId'] ?? null,
				'postType'  => $this->context['postType'] ?? null,
			);

			return sprintf(
				'<span data-block="%s" data-post-id="%d"></span>',
				esc_attr( $block_name ),
				$this->context['postId'] ?? 0
			);
		}
	}
}

require_once dirname( __DIR__ ) . '/includes/class-block-base.php';
require_once dirname( __DIR__ ) . '/includes/blocks/class-query.php';

function toolbox_blocks_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

WP_Query::$posts = array(
	array(
		'ID'        => 101,
		'post_type' => 'post',
	),
	array(
		'ID'        => 202,
		'post_type' => 'post',
	),
);

$block = (object) array(
	'parsed_block' => array(
		'blockName'    => Toolbox_Block_Query::BLOCK_NAME,
		'attrs'        => array(),
		'innerBlocks'  => array(
			array(
				'blockName'    => 'toolbox-blocks/headline',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array(
				'blockName'    => 'toolbox-blocks/text',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
		),
		'innerHTML'    => '',
		'innerContent' => array(),
	),
);

$output = Toolbox_Block_Query::render(
	array(
		'postType'     => 'post',
		'postsPerPage' => 2,
	),
	'',
	$block
);

toolbox_blocks_assert(
	4 === count( WP_Block::$rendered_blocks ),
	'Expected both inner blocks to render once for each post.'
);

toolbox_blocks_assert(
	array( 101, 101, 202, 202 ) === array_column( WP_Block::$rendered_blocks, 'postId' ),
	'Expected post context to be passed to each rendered template block.'
);

toolbox_blocks_assert(
	false === strpos( $output, 'data-block="' . Toolbox_Block_Query::BLOCK_NAME . '"' ),
	'Query block should not render itself inside its post loop.'
);

echo "Query render regression passed.\n";
