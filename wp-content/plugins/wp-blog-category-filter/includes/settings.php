<?php
/**
 * Settings for WP Blog Category Filter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get default options
 */
function wp_blog_filter_get_default_options() {
	return array(
		// Filter display options
		'all_text'              => __( 'All Posts', 'wp-blog-category-filter' ),
		'loading_text'          => __( 'Loading...', 'wp-blog-category-filter' ),
		'no_posts_text'         => __( 'No posts found.', 'wp-blog-category-filter' ),
		'layout'                => 'horizontal',
		'show_counts'           => true,
		'hide_empty_categories' => true,

		// Category ordering
		'category_orderby'      => 'name',
		'category_order'        => 'ASC',

		// Post display options
		'show_featured_image'   => true,
		'image_size'            => 'medium',
		'show_categories'       => true,
		'show_meta'             => true,
		'content_type'          => 'excerpt',
		'show_read_more'        => true,
		'read_more_text'        => __( 'Read More', 'wp-blog-category-filter' ),

		// Post ordering
		'post_orderby'          => 'date',
		'post_order'            => 'DESC',

		// Pagination
		'show_pagination'       => true,
	);
}

/**
 * Get plugin options
 */
function wp_blog_filter_get_options() {
	return wp_parse_args(
		get_option( 'wp_blog_filter_options', array() ),
		wp_blog_filter_get_default_options()
	);
}

/**
 * Add settings page
 */
function wp_blog_filter_add_settings_page() {
	add_options_page(
		__( 'Blog Category Filter Settings', 'wp-blog-category-filter' ),
		__( 'Blog Filter', 'wp-blog-category-filter' ),
		'manage_options',
		'wp-blog-category-filter',
		'wp_blog_filter_settings_page'
	);
}
add_action( 'admin_menu', 'wp_blog_filter_add_settings_page' );

/**
 * Register settings
 */
function wp_blog_filter_register_settings() {
	register_setting(
		'wp_blog_filter_options',
		'wp_blog_filter_options',
		'wp_blog_filter_sanitize_options'
	);

	// Filter Display Section
	add_settings_section(
		'wp_blog_filter_display_section',
		__( 'Filter Display', 'wp-blog-category-filter' ),
		'wp_blog_filter_display_section_callback',
		'wp-blog-category-filter'
	);

	add_settings_field(
		'all_text',
		__( '"All Posts" Button Text', 'wp-blog-category-filter' ),
		'wp_blog_filter_all_text_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_display_section'
	);

	add_settings_field(
		'loading_text',
		__( 'Loading Text', 'wp-blog-category-filter' ),
		'wp_blog_filter_loading_text_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_display_section'
	);

	add_settings_field(
		'no_posts_text',
		__( 'No Posts Found Text', 'wp-blog-category-filter' ),
		'wp_blog_filter_no_posts_text_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_display_section'
	);

	add_settings_field(
		'layout',
		__( 'Filter Layout', 'wp-blog-category-filter' ),
		'wp_blog_filter_layout_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_display_section'
	);

	add_settings_field(
		'show_counts',
		__( 'Show Post Counts', 'wp-blog-category-filter' ),
		'wp_blog_filter_show_counts_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_display_section'
	);

	add_settings_field(
		'hide_empty_categories',
		__( 'Hide Empty Categories', 'wp-blog-category-filter' ),
		'wp_blog_filter_hide_empty_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_display_section'
	);

	// Post Display Section
	add_settings_section(
		'wp_blog_filter_posts_section',
		__( 'Post Display', 'wp-blog-category-filter' ),
		'wp_blog_filter_posts_section_callback',
		'wp-blog-category-filter'
	);

	add_settings_field(
		'show_featured_image',
		__( 'Show Featured Images', 'wp-blog-category-filter' ),
		'wp_blog_filter_show_featured_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_posts_section'
	);

	add_settings_field(
		'image_size',
		__( 'Image Size', 'wp-blog-category-filter' ),
		'wp_blog_filter_image_size_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_posts_section'
	);

	add_settings_field(
		'show_categories',
		__( 'Show Categories', 'wp-blog-category-filter' ),
		'wp_blog_filter_show_categories_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_posts_section'
	);

	add_settings_field(
		'show_meta',
		__( 'Show Post Meta', 'wp-blog-category-filter' ),
		'wp_blog_filter_show_meta_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_posts_section'
	);

	add_settings_field(
		'content_type',
		__( 'Content Display', 'wp-blog-category-filter' ),
		'wp_blog_filter_content_type_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_posts_section'
	);

	add_settings_field(
		'show_read_more',
		__( 'Show Read More Link', 'wp-blog-category-filter' ),
		'wp_blog_filter_show_read_more_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_posts_section'
	);

	add_settings_field(
		'read_more_text',
		__( 'Read More Text', 'wp-blog-category-filter' ),
		'wp_blog_filter_read_more_text_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_posts_section'
	);

	// Ordering Section
	add_settings_section(
		'wp_blog_filter_ordering_section',
		__( 'Ordering & Pagination', 'wp-blog-category-filter' ),
		'wp_blog_filter_ordering_section_callback',
		'wp-blog-category-filter'
	);

	add_settings_field(
		'category_orderby',
		__( 'Category Order By', 'wp-blog-category-filter' ),
		'wp_blog_filter_category_orderby_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_ordering_section'
	);

	add_settings_field(
		'category_order',
		__( 'Category Order', 'wp-blog-category-filter' ),
		'wp_blog_filter_category_order_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_ordering_section'
	);

	add_settings_field(
		'post_orderby',
		__( 'Post Order By', 'wp-blog-category-filter' ),
		'wp_blog_filter_post_orderby_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_ordering_section'
	);

	add_settings_field(
		'post_order',
		__( 'Post Order', 'wp-blog-category-filter' ),
		'wp_blog_filter_post_order_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_ordering_section'
	);

	add_settings_field(
		'show_pagination',
		__( 'Show Pagination', 'wp-blog-category-filter' ),
		'wp_blog_filter_show_pagination_callback',
		'wp-blog-category-filter',
		'wp_blog_filter_ordering_section'
	);
}
add_action( 'admin_init', 'wp_blog_filter_register_settings' );

// Section Callbacks
function wp_blog_filter_display_section_callback() {
	echo '<p>' . __( 'Configure how the category filter buttons are displayed.', 'wp-blog-category-filter' ) . '</p>';
}

function wp_blog_filter_posts_section_callback() {
	echo '<p>' . __( 'Configure how individual posts are displayed in the filtered results.', 'wp-blog-category-filter' ) . '</p>';
}

function wp_blog_filter_ordering_section_callback() {
	echo '<p>' . __( 'Configure the ordering of categories and posts, and pagination settings.', 'wp-blog-category-filter' ) . '</p>';
}

// Field Callbacks
function wp_blog_filter_all_text_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<input type="text" name="wp_blog_filter_options[all_text]" value="<?php echo esc_attr( $options['all_text'] ); ?>" />
	<?php
}

function wp_blog_filter_loading_text_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<input type="text" name="wp_blog_filter_options[loading_text]" value="<?php echo esc_attr( $options['loading_text'] ); ?>" />
	<?php
}

function wp_blog_filter_no_posts_text_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<input type="text" name="wp_blog_filter_options[no_posts_text]" value="<?php echo esc_attr( $options['no_posts_text'] ); ?>" />
	<?php
}

function wp_blog_filter_layout_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<select name="wp_blog_filter_options[layout]">
		<option value="horizontal" <?php selected( $options['layout'], 'horizontal' ); ?>><?php _e( 'Horizontal', 'wp-blog-category-filter' ); ?></option>
		<option value="vertical" <?php selected( $options['layout'], 'vertical' ); ?>><?php _e( 'Vertical', 'wp-blog-category-filter' ); ?></option>
	</select>
	<?php
}

function wp_blog_filter_show_counts_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<input type="checkbox" name="wp_blog_filter_options[show_counts]" value="1" <?php checked( 1, $options['show_counts'] ); ?> />
	<label><?php _e( 'Show post count next to each category', 'wp-blog-category-filter' ); ?></label>
	<?php
}

function wp_blog_filter_hide_empty_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<input type="checkbox" name="wp_blog_filter_options[hide_empty_categories]" value="1" <?php checked( 1, $options['hide_empty_categories'] ); ?> />
	<label><?php _e( 'Hide categories with no posts', 'wp-blog-category-filter' ); ?></label>
	<?php
}

function wp_blog_filter_show_featured_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<input type="checkbox" name="wp_blog_filter_options[show_featured_image]" value="1" <?php checked( 1, $options['show_featured_image'] ); ?> />
	<label><?php _e( 'Show featured images in post previews', 'wp-blog-category-filter' ); ?></label>
	<?php
}

function wp_blog_filter_image_size_callback() {
	$options = wp_blog_filter_get_options();
	$sizes   = get_intermediate_image_sizes();
	?>
	<select name="wp_blog_filter_options[image_size]">
		<?php foreach ( $sizes as $size ) : ?>
			<option value="<?php echo esc_attr( $size ); ?>" <?php selected( $options['image_size'], $size ); ?>><?php echo esc_html( $size ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
}

function wp_blog_filter_show_categories_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<input type="checkbox" name="wp_blog_filter_options[show_categories]" value="1" <?php checked( 1, $options['show_categories'] ); ?> />
	<label><?php _e( 'Show post categories', 'wp-blog-category-filter' ); ?></label>
	<?php
}

function wp_blog_filter_show_meta_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<input type="checkbox" name="wp_blog_filter_options[show_meta]" value="1" <?php checked( 1, $options['show_meta'] ); ?> />
	<label><?php _e( 'Show post date and author', 'wp-blog-category-filter' ); ?></label>
	<?php
}

function wp_blog_filter_content_type_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<select name="wp_blog_filter_options[content_type]">
		<option value="excerpt" <?php selected( $options['content_type'], 'excerpt' ); ?>><?php _e( 'Show Excerpt', 'wp-blog-category-filter' ); ?></option>
		<option value="full" <?php selected( $options['content_type'], 'full' ); ?>><?php _e( 'Show Full Content', 'wp-blog-category-filter' ); ?></option>
	</select>
	<?php
}

function wp_blog_filter_show_read_more_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<input type="checkbox" name="wp_blog_filter_options[show_read_more]" value="1" <?php checked( 1, $options['show_read_more'] ); ?> />
	<label><?php _e( 'Show "Read More" link', 'wp-blog-category-filter' ); ?></label>
	<?php
}

function wp_blog_filter_read_more_text_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<input type="text" name="wp_blog_filter_options[read_more_text]" value="<?php echo esc_attr( $options['read_more_text'] ); ?>" />
	<?php
}

function wp_blog_filter_category_orderby_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<select name="wp_blog_filter_options[category_orderby]">
		<option value="name" <?php selected( $options['category_orderby'], 'name' ); ?>><?php _e( 'Name', 'wp-blog-category-filter' ); ?></option>
		<option value="count" <?php selected( $options['category_orderby'], 'count' ); ?>><?php _e( 'Post Count', 'wp-blog-category-filter' ); ?></option>
		<option value="id" <?php selected( $options['category_orderby'], 'id' ); ?>><?php _e( 'ID', 'wp-blog-category-filter' ); ?></option>
	</select>
	<?php
}

function wp_blog_filter_category_order_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<select name="wp_blog_filter_options[category_order]">
		<option value="ASC" <?php selected( $options['category_order'], 'ASC' ); ?>><?php _e( 'Ascending', 'wp-blog-category-filter' ); ?></option>
		<option value="DESC" <?php selected( $options['category_order'], 'DESC' ); ?>><?php _e( 'Descending', 'wp-blog-category-filter' ); ?></option>
	</select>
	<?php
}

function wp_blog_filter_post_orderby_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<select name="wp_blog_filter_options[post_orderby]">
		<option value="date" <?php selected( $options['post_orderby'], 'date' ); ?>><?php _e( 'Date', 'wp-blog-category-filter' ); ?></option>
		<option value="title" <?php selected( $options['post_orderby'], 'title' ); ?>><?php _e( 'Title', 'wp-blog-category-filter' ); ?></option>
		<option value="modified" <?php selected( $options['post_orderby'], 'modified' ); ?>><?php _e( 'Last Modified', 'wp-blog-category-filter' ); ?></option>
		<option value="comment_count" <?php selected( $options['post_orderby'], 'comment_count' ); ?>><?php _e( 'Comment Count', 'wp-blog-category-filter' ); ?></option>
	</select>
	<?php
}

function wp_blog_filter_post_order_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<select name="wp_blog_filter_options[post_order]">
		<option value="DESC" <?php selected( $options['post_order'], 'DESC' ); ?>><?php _e( 'Descending', 'wp-blog-category-filter' ); ?></option>
		<option value="ASC" <?php selected( $options['post_order'], 'ASC' ); ?>><?php _e( 'Ascending', 'wp-blog-category-filter' ); ?></option>
	</select>
	<?php
}

function wp_blog_filter_show_pagination_callback() {
	$options = wp_blog_filter_get_options();
	?>
	<input type="checkbox" name="wp_blog_filter_options[show_pagination]" value="1" <?php checked( 1, $options['show_pagination'] ); ?> />
	<label><?php _e( 'Show pagination controls for filtered results', 'wp-blog-category-filter' ); ?></label>
	<?php
}

/**
 * Sanitize options
 */
function wp_blog_filter_sanitize_options( $options ) {
	$sanitized = array();

	// Text fields
	$text_fields = array( 'all_text', 'loading_text', 'no_posts_text', 'read_more_text' );
	foreach ( $text_fields as $field ) {
		$sanitized[ $field ] = sanitize_text_field( $options[ $field ] ?? '' );
	}

	// Select fields
	$select_fields = array( 'layout', 'image_size', 'content_type', 'category_orderby', 'category_order', 'post_orderby', 'post_order' );
	foreach ( $select_fields as $field ) {
		$sanitized[ $field ] = sanitize_text_field( $options[ $field ] ?? '' );
	}

	// Checkbox fields
	$checkbox_fields = array( 'show_counts', 'hide_empty_categories', 'show_featured_image', 'show_categories', 'show_meta', 'show_read_more', 'show_pagination' );
	foreach ( $checkbox_fields as $field ) {
		$sanitized[ $field ] = isset( $options[ $field ] ) ? 1 : 0;
	}

	return $sanitized;
}

/**
 * Check if GeneratePress is active
 */
function wp_blog_filter_is_gp_active() {
	return defined( 'GENERATE_VERSION' ) && function_exists( 'generate_blog_get_defaults' );
}

/**
 * Settings page callback
 */
function wp_blog_filter_settings_page() {
	?>
	<div class="wrap">
		<h1><?php _e( 'Blog Category Filter Settings', 'wp-blog-category-filter' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'wp_blog_filter_options' );
			do_settings_sections( 'wp-blog-category-filter' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}