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
		$post_type       = sanitize_key( $attributes['postType'] ?? 'post' );
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
		$template_blocks = isset( $block->parsed_block['innerBlocks'] ) && is_array( $block->parsed_block['innerBlocks'] )
			? $block->parsed_block['innerBlocks']
			: array();

		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$post_id   = get_the_ID();
			$post_type = get_post_type( $post_id ) ?: $post_type;
			$context   = array(
				'postId'   => $post_id,
				'postType' => $post_type,
			);

			foreach ( $template_blocks as $template_block ) {
				$loop_html .= ( new WP_Block( $template_block, $context ) )->render();
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
}
