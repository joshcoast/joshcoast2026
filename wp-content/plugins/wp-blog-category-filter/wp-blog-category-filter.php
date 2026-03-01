<?php
/**
 * Plugin Name: WP Blog Category Filter
 * Description: AJAX-powered category filter for the WordPress blog page with consistent post formatting
 * Version: 1.0.0
 * Author: Josh Coast
 * Text Domain: wp-blog-category-filter
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_BLOG_FILTER_VERSION', '1.0.0' );
define( 'WP_BLOG_FILTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_BLOG_FILTER_URL', plugin_dir_url( __FILE__ ) );

// Include settings
require_once WP_BLOG_FILTER_PATH . 'includes/settings.php';

/**
 * Enqueue scripts and styles
 */
function wp_blog_filter_enqueue_scripts() {
	// Only load on blog/home page
	if ( ! is_home() && ! ( is_front_page() && is_home() ) ) {
		return;
	}

	wp_enqueue_script(
		'wp-blog-filter-js',
		WP_BLOG_FILTER_URL . 'assets/js/filter.js',
		array( 'jquery' ),
		WP_BLOG_FILTER_VERSION,
		false
	);

	wp_enqueue_style(
		'wp-blog-filter-css',
		WP_BLOG_FILTER_URL . 'assets/css/filter.css',
		array(),
		WP_BLOG_FILTER_VERSION
	);

	// Localize script with AJAX data
	wp_localize_script(
		'wp-blog-filter-js',
		'wpBlogFilterAjax',
		array(
			'ajaxurl'         => admin_url( 'admin-ajax.php' ),
			'nonce'           => wp_create_nonce( 'wp_blog_filter_nonce' ),
			'currentCategory' => get_query_var( 'cat' ) ?: 0,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'wp_blog_filter_enqueue_scripts' );

/**
 * Add filter buttons before the main loop
 */
function wp_blog_filter_add_filters() {
	// Only show on blog/home page
	if ( ! is_home() && ! ( is_front_page() && is_home() ) ) {
		return;
	}

	$options = wp_blog_filter_get_options();

	// Get all categories
	$categories = get_categories(
		array(
			'hide_empty' => $options['hide_empty_categories'],
			'orderby'    => $options['category_orderby'],
			'order'      => $options['category_order'],
		)
	);

	if ( empty( $categories ) ) {
		return;
	}

	$current_cat = get_query_var( 'cat' ) ?: 0;

	$container_classes = array( 'wp-blog-filter__container' );
	if ( $options['layout'] === 'vertical' ) {
		$container_classes[] = 'wp-blog-filter__container--vertical';
	}

	?>
	<div class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>">
		<div class="wp-blog-filter__buttons">
			<button type="button"
					class="wp-blog-filter__button <?php echo $current_cat == 0 ? 'wp-blog-filter__button--active' : ''; ?>"
					data-category="0">
				<?php echo esc_html( $options['all_text'] ); ?>
			</button>

			<?php foreach ( $categories as $category ) : ?>
				<button type="button"
						class="wp-blog-filter__button <?php echo $current_cat == $category->term_id ? 'wp-blog-filter__button--active' : ''; ?>"
						data-category="<?php echo esc_attr( $category->term_id ); ?>">
					<?php echo esc_html( $category->name ); ?>
					<?php if ( $options['show_counts'] ) : ?>
						<span class="wp-blog-filter__count">(<?php echo esc_html( $category->count ); ?>)</span>
					<?php endif; ?>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="wp-blog-filter__loading" style="display: none;">
			<?php echo esc_html( $options['loading_text'] ); ?>
		</div>
	</div>
	<?php
}
add_action( 'loop_start', 'wp_blog_filter_add_filters', 20 );

/**
 * Add grid wrapper around posts
 */
function wp_blog_filter_add_grid_wrapper() {
	// Only on blog/home page
	if ( ! is_home() && ! ( is_front_page() && is_home() ) && ! is_page( 'blog' ) ) {
		return;
	}

	do_action( 'wp_blog_filter_grid_start' );
	echo '<div class="wp-blog-filter__posts-grid">';
}
add_action( 'loop_start', 'wp_blog_filter_add_grid_wrapper', 25 );

/**
 * Close grid wrapper after posts
 */
function wp_blog_filter_close_grid_wrapper() {
	// Only on blog/home page
	if ( ! is_home() && ! ( is_front_page() && is_home() ) && ! is_page( 'blog' ) ) {
		return;
	}

	echo '</div>';
	do_action( 'wp_blog_filter_grid_end' );

	// Add pagination after the grid on initial page load
	wp_blog_filter_add_initial_pagination();
}
add_action( 'loop_end', 'wp_blog_filter_close_grid_wrapper', 25 );

/**
 * Replace post content with custom layout when inside our grid
 */
function wp_blog_filter_replace_grid_content( $content ) {
	// Prevent infinite recursion
	static $is_rendering = false;
	if ( $is_rendering ) {
		return $content;
	}

	// Only on blog/home page and in main query
	if ( ! is_home() && ! ( is_front_page() && is_home() ) && ! is_page( 'blog' ) ) {
		return $content;
	}

	if ( ! is_main_query() ) {
		return $content;
	}

	// Only replace content if we're in the loop
	if ( ! in_the_loop() ) {
		return $content;
	}

	// Check if we're inside our grid wrapper by looking for a global flag
	global $wp_blog_filter_in_grid;
	if ( ! $wp_blog_filter_in_grid ) {
		return $content;
	}

	// Set flag to prevent recursion
	$is_rendering = true;

	// Get the custom rendered post content
	ob_start();
	wp_blog_filter_render_post();
	$custom_content = ob_get_clean();

	// Clear flag
	$is_rendering = false;

	// Return custom content instead of default content
	return $custom_content;
}
// Content filtering disabled - using child theme template override instead
// add_filter( 'the_content', 'wp_blog_filter_replace_grid_content', 1 );
// add_filter( 'get_the_excerpt', 'wp_blog_filter_replace_grid_content', 1 );

/**
 * Set flag when entering our grid wrapper
 */
function wp_blog_filter_set_grid_flag() {
	global $wp_blog_filter_in_grid;
	$wp_blog_filter_in_grid = true;
}
add_action( 'wp_blog_filter_grid_start', 'wp_blog_filter_set_grid_flag' );

/**
 * Clear flag when exiting our grid wrapper
 */
function wp_blog_filter_clear_grid_flag() {
	global $wp_blog_filter_in_grid;
	$wp_blog_filter_in_grid = false;
}
add_action( 'wp_blog_filter_grid_end', 'wp_blog_filter_clear_grid_flag' );

/**
 * Add pagination after the grid on initial page load
 */
function wp_blog_filter_add_initial_pagination() {
	global $wp_query;

	// Only show if we have multiple pages
	if ( $wp_query->max_num_pages <= 1 ) {
		return;
	}

	$current_page = get_query_var( 'paged' ) ?: 1;
	$current_cat  = get_query_var( 'cat' ) ?: 0;

	$pagination_html = wp_blog_filter_generate_pagination( $wp_query->max_num_pages, $current_page, $current_cat );

	if ( $pagination_html ) {
		echo $pagination_html;
	}
}

/**
 * Generate custom post grid to replace theme's output
 */
function wp_blog_filter_generate_custom_grid() {
	global $wp_query;

	if ( ! $wp_query->have_posts() ) {
		return '<div class="wp-blog-filter__no-posts">No posts found.</div>';
	}

	ob_start();

	while ( $wp_query->have_posts() ) {
		$wp_query->the_post();
		wp_blog_filter_render_post();
	}

	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * Override post display when in our grid
 */
function wp_blog_filter_override_post_display( $post ) {
	global $wp_blog_filter_in_grid;

	// Only override if we're in our grid
	if ( ! $wp_blog_filter_in_grid ) {
		return;
	}

	// Only on blog/home page and in main query
	if ( ! is_home() && ! ( is_front_page() && is_home() ) && ! is_page( 'blog' ) ) {
		return;
	}

	if ( ! is_main_query() ) {
		return;
	}

	// Output our custom post format
	wp_blog_filter_render_post();

	// Prevent the theme's template from running by removing common actions
	remove_action( 'the_post', 'wp_blog_filter_override_post_display', 10 );

	// Use output buffering to capture and discard the theme's output
	// ob_start();
}
// add_action( 'the_post', 'wp_blog_filter_override_post_display', 1 );

/**
 * AJAX handler for filtering posts
 */
function wp_blog_filter_ajax_handler() {
	check_ajax_referer( 'wp_blog_filter_nonce', 'nonce' );

	$category_id = intval( $_POST['category_id'] ?? 0 );
	$page        = intval( $_POST['page'] ?? 1 );
	$options     = wp_blog_filter_get_options();

	// Build query arguments
	$query_args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => get_option( 'posts_per_page' ),
		'paged'          => $page,
		'orderby'        => $options['post_orderby'],
		'order'          => $options['post_order'],
	);

	if ( $category_id > 0 ) {
		$query_args['cat'] = $category_id;
	}

	$query = new WP_Query( $query_args );

	ob_start();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			wp_blog_filter_render_post();
		}
	} else {
		echo '<div class="wp-blog-filter__no-posts">' . esc_html( $options['no_posts_text'] ) . '</div>';
	}

	$posts_html = ob_get_clean();

	// Generate pagination
	$pagination_html = '';
	if ( $query->max_num_pages > 1 ) {
		$pagination_html = wp_blog_filter_generate_pagination( $query->max_num_pages, $page, $category_id );
	}

	wp_reset_postdata();

	wp_send_json_success(
		array(
			'posts'       => $posts_html,
			'pagination'  => $pagination_html,
			'found_posts' => $query->found_posts,
			'max_pages'   => $query->max_num_pages,
		)
	);
}
add_action( 'wp_ajax_filter_posts', 'wp_blog_filter_ajax_handler' );
add_action( 'wp_ajax_nopriv_filter_posts', 'wp_blog_filter_ajax_handler' );

/**
 * Render a single post with custom layout
 */
function wp_blog_filter_render_post() {
	$options = wp_blog_filter_get_options();

	// Custom post format with modern layout
	?>
	<article class="wp-blog-filter__post-card">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="wp-blog-filter__post-image">
				<a href="<?php the_permalink(); ?>">
					<?php the_post_thumbnail( 'medium_large' ); ?>
				</a>
			</div>
		<?php endif; ?>

		<header class="wp-blog-filter__post-header">
			<h2 class="wp-blog-filter__post-title">
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h2>

			<div class="wp-blog-filter__post-meta">
				<time class="wp-blog-filter__post-date" datetime="<?php echo get_the_date( 'c' ); ?>">
					<?php echo get_the_date(); ?>
				</time>
			</div>
		</header>

		<div class="wp-blog-filter__post-excerpt">
			<?php echo wp_trim_words( get_the_excerpt(), 25 ); ?>
		</div>

		<footer class="wp-blog-filter__post-footer">
			<a href="<?php the_permalink(); ?>" class="wp-blog-filter__read-it-btn">
				View Note
				<span class="wp-blog-filter__arrow-icon">
					<svg viewBox="0 0 16 16" class="bi bi-arrow-right" fill="currentColor" height="16" width="16" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" fill-rule="evenodd"></path>
					</svg>
				</span>
			</a>
		</footer>
	</article>
	<?php
}

/**
 * Add CSS to hide theme pagination when our plugin is active
 */
function wp_blog_filter_hide_theme_pagination_css() {
	// Only on blog/home page
	if ( ! is_home() && ! ( is_front_page() && is_home() ) && ! is_page( 'blog' ) ) {
		return;
	}

	?>
	<style>
		/* Hide theme pagination when our plugin is active */
		.paging-navigation,
		.pagination,
		.nav-links,
		.page-numbers,
		.generate-pagination,
		.generate-page-numbers {
			display: none !important;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'wp_blog_filter_hide_theme_pagination_css' );

/**
 * Generate pagination HTML
 */
function wp_blog_filter_generate_pagination( $max_pages, $current_page, $category_id ) {
	$options = wp_blog_filter_get_options();

	if ( ! $options['show_pagination'] ) {
		return '';
	}

	ob_start();
	?>
	<nav class="wp-blog-filter__pagination">
		<?php if ( $current_page > 1 ) : ?>
			<button type="button" class="wp-blog-filter__page-btn" data-page="<?php echo $current_page - 1; ?>" data-category="<?php echo $category_id; ?>">
				&laquo; <?php _e( 'Previous', 'wp-blog-category-filter' ); ?>
			</button>
		<?php endif; ?>

		<span class="wp-blog-filter__page-info">
			<?php printf( __( 'Page %1$d of %2$d', 'wp-blog-category-filter' ), $current_page, $max_pages ); ?>
		</span>

		<?php if ( $current_page < $max_pages ) : ?>
			<button type="button" class="wp-blog-filter__page-btn" data-page="<?php echo $current_page + 1; ?>" data-category="<?php echo $category_id; ?>">
				<?php _e( 'Next', 'wp-blog-category-filter' ); ?> &raquo;
			</button>
		<?php endif; ?>
	</nav>
	<?php

	return ob_get_clean();
}

/**
 * Remove problematic GeneratePress classes that interfere with our grid
 */
// function wp_blog_filter_remove_theme_classes( $attributes, $context, $settings ) {
// Only modify content attributes on blog/home pages
// if ( ! is_home() && ! ( is_front_page() && is_home() ) ) {
// return $attributes;
// }

// Remove classes that interfere with our custom grid
// $problematic_classes = array(
// 'generate-columns-container',
// 'masonry-container',
// 'generate-masonry',
// 'masonry',
// 'columns-container',
// );

// if ( isset( $attributes['class'] ) ) {
// $classes             = explode( ' ', $attributes['class'] );
// $classes             = array_diff( $classes, $problematic_classes );
// $attributes['class'] = implode( ' ', $classes );
// }

// return $attributes;
// }
// add_filter( 'generate_parse_attr', 'wp_blog_filter_remove_theme_classes', 20, 3 );

/**
 * Activation hook
 */
function wp_blog_filter_activate() {
	// Set default options
	add_option( 'wp_blog_filter_options', wp_blog_filter_get_default_options() );
}
register_activation_hook( __FILE__, 'wp_blog_filter_activate' );