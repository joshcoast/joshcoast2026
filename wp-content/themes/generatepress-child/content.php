<?php
/**
 * The template for displaying posts within the loop - Custom for WP Blog Category Filter
 *
 * @package GeneratePress Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check if we're in the blog category filter grid
global $wp_blog_filter_in_grid;
if ( $wp_blog_filter_in_grid ) {
	// Use our custom post format
	wp_blog_filter_render_post();
} else {
	// Use the parent theme's default content template
	get_template_part( 'content', get_post_format() );
}
