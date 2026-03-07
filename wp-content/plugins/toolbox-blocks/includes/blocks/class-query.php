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
		$meta        = self::block_meta( $attributes, 'tb-query' );
		$post_type   = sanitize_key( $attributes['postType'] ?? 'post' );
		$per_page    = absint( $attributes['postsPerPage'] ?? 10 );
		$order       = in_array( $attributes['order'] ?? 'DESC', array( 'ASC', 'DESC' ), true )
			? $attributes['order']
			: 'DESC';
		$orderby     = sanitize_key( $attributes['orderBy'] ?? 'date' );
		$offset      = absint( $attributes['offset'] ?? 0 );
		$no_results  = wp_kses_post( $attributes['noResultsText'] ?? __( 'No posts found.', 'toolbox-blocks' ) );

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
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			// Render inner blocks with the current post context.
			$block_content = ( new WP_Block( $block->parsed_block, array( 'postId' => get_the_ID(), 'postType' => $post_type ) ) )->render();
			$loop_html .= $block_content;
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
