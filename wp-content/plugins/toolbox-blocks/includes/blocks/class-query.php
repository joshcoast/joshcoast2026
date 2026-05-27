<?php
/**
 * Query block render – loops posts and renders inner blocks as a template per post.
 *
 * @package ToolboxBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Toolbox_Block_Query extends Toolbox_Block_Base {

	const BLOCK_NAME = 'toolbox-blocks/query';

	public static function render( $attributes, $content, $block ) {
		$meta            = self::block_meta( $attributes, 'tb-query' );
		$post_type       = self::viewable_post_type( $attributes['postType'] ?? 'post' );
		$per_page        = absint( $attributes['postsPerPage'] ?? 10 );
		$per_page        = min( 100, max( 1, $per_page ) );
		$order_raw       = strtoupper( (string) ( $attributes['order'] ?? 'DESC' ) );
		$order           = in_array( $order_raw, array( 'ASC', 'DESC' ), true )
			? $order_raw
			: 'DESC';
		$allowed_orderby = array( 'date', 'title', 'modified', 'menu_order', 'comment_count', 'rand' );
		$orderby         = sanitize_key( $attributes['orderBy'] ?? 'date' );
		$orderby         = in_array( $orderby, $allowed_orderby, true ) ? $orderby : 'date';
		$offset          = absint( $attributes['offset'] ?? 0 );
		$no_results      = wp_kses_post( $attributes['noResultsText'] ?? __( 'No posts found.', 'toolbox-blocks' ) );

		$query_args = array(
			'post_type'      => $post_type,
			'posts_per_page' => $per_page,
			'order'          => $order,
			'orderby'        => $orderby,
			'offset'         => $offset,
			'post_status'    => 'publish',
		);

		$the_query = new WP_Query( $query_args );

		if ( ! $the_query->have_posts() ) {
			return sprintf(
				'%s<div class="%s tb-query--empty"%s>%s</div>',
				$meta['style_tag'],
				$meta['class'],
				$meta['anchor'],
				$no_results
			);
		}

		$loop_html = '';
		$inner_blocks = is_array( $block->parsed_block['innerBlocks'] ?? null )
			? $block->parsed_block['innerBlocks']
			: array();
		$base_context = is_array( $block->context ?? null ) ? $block->context : array();

		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			// Render only the template children; rendering this Query block again recurses.
			$post_context = array_merge(
				$base_context,
				array(
					'postId'   => get_the_ID(),
					'postType' => $post_type,
				)
			);
			foreach ( $inner_blocks as $inner_block ) {
				$loop_html .= ( new WP_Block( $inner_block, $post_context ) )->render();
			}
		}
		wp_reset_postdata();

		return sprintf(
			'%s<div class="%s"%s>%s</div>',
			$meta['style_tag'],
			$meta['class'],
			$meta['anchor'],
			$loop_html
		);
	}

	/**
	 * Match the editor's viewable post type restrictions on the server.
	 *
	 * @param string $post_type Raw post type attribute.
	 * @return string Safe post type slug.
	 */
	private static function viewable_post_type( $post_type ) {
		$post_type = sanitize_key( $post_type );
		if ( ! $post_type || 'attachment' === $post_type ) {
			return 'post';
		}

		$post_type_object = get_post_type_object( $post_type );
		if ( ! $post_type_object ) {
			return 'post';
		}

		if ( function_exists( 'is_post_type_viewable' ) ) {
			return is_post_type_viewable( $post_type_object ) ? $post_type : 'post';
		}

		return ! empty( $post_type_object->publicly_queryable ) || ! empty( $post_type_object->public )
			? $post_type
			: 'post';
	}
}
