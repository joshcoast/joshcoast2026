<?php
/**
 * Settings for JC Taxonomy Filter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add settings page
 */
function jc_taxonomy_filter_add_settings_page() {
	add_options_page(
		__( 'JC Taxonomy Filter Settings', 'jc-taxonomy-filter' ),
		__( 'Taxonomy Filter', 'jc-taxonomy-filter' ),
		'manage_options',
		'jc-taxonomy-filter',
		'jc_taxonomy_filter_settings_page'
	);
}
add_action( 'admin_menu', 'jc_taxonomy_filter_add_settings_page' );

/**
 * Register settings
 */
function jc_taxonomy_filter_register_settings() {
	register_setting(
		'jc_taxonomy_filter_options',
		'jc_taxonomy_filter_options',
		'jc_taxonomy_filter_sanitize_options'
	);

	add_settings_section(
		'jc_taxonomy_filter_archive_section',
		__( 'Archive Page Integration', 'jc-taxonomy-filter' ),
		'jc_taxonomy_filter_archive_section_callback',
		'jc-taxonomy-filter'
	);

	add_settings_field(
		'enable_archive_filters',
		__( 'Enable on Archive Pages', 'jc-taxonomy-filter' ),
		'jc_taxonomy_filter_enable_archive_callback',
		'jc-taxonomy-filter',
		'jc_taxonomy_filter_archive_section'
	);

	add_settings_field(
		'archive_taxonomy',
		__( 'Taxonomy for Archives', 'jc-taxonomy-filter' ),
		'jc_taxonomy_filter_archive_taxonomy_callback',
		'jc-taxonomy-filter',
		'jc_taxonomy_filter_archive_section'
	);

	add_settings_field(
		'archive_layout',
		__( 'Filter Layout', 'jc-taxonomy-filter' ),
		'jc_taxonomy_filter_archive_layout_callback',
		'jc-taxonomy-filter',
		'jc_taxonomy_filter_archive_section'
	);

	add_settings_field(
		'archive_style',
		__( 'Filter Style', 'jc-taxonomy-filter' ),
		'jc_taxonomy_filter_archive_style_callback',
		'jc-taxonomy-filter',
		'jc_taxonomy_filter_archive_section'
	);

	add_settings_field(
		'archive_show_pagination',
		__( 'Show Pagination', 'jc-taxonomy-filter' ),
		'jc_taxonomy_filter_archive_pagination_callback',
		'jc-taxonomy-filter',
		'jc_taxonomy_filter_archive_section'
	);
}
add_action( 'admin_init', 'jc_taxonomy_filter_register_settings' );

/**
 * Sanitize options
 */
function jc_taxonomy_filter_sanitize_options( $options ) {
	$sanitized = array();

	$sanitized['enable_archive_filters']  = isset( $options['enable_archive_filters'] ) ? 1 : 0;
	$sanitized['archive_taxonomy']        = sanitize_text_field( $options['archive_taxonomy'] ?? 'category' );
	$sanitized['archive_layout']          = sanitize_text_field( $options['archive_layout'] ?? 'horizontal' );
	$sanitized['archive_style']           = sanitize_text_field( $options['archive_style'] ?? 'buttons' );
	$sanitized['archive_show_pagination'] = isset( $options['archive_show_pagination'] ) ? 1 : 0;

	return $sanitized;
}

/**
 * Get plugin options
 */
function jc_taxonomy_filter_get_options() {
	return get_option(
		'jc_taxonomy_filter_options',
		array(
			'enable_archive_filters'  => 0,
			'archive_taxonomy'        => 'category',
			'archive_layout'          => 'horizontal',
			'archive_style'           => 'buttons',
			'archive_show_pagination' => 0,
		)
	);
}

/**
 * Settings page callback
 */
function jc_taxonomy_filter_settings_page() {
	?>
	<div class="wrap">
		<h1><?php _e( 'JC Taxonomy Filter Settings', 'jc-taxonomy-filter' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'jc_taxonomy_filter_options' );
			do_settings_sections( 'jc-taxonomy-filter' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Section callback
 */
function jc_taxonomy_filter_archive_section_callback() {
	echo '<p>' . __( 'Automatically add taxonomy filters to archive pages (blog, category, tag, etc.).', 'jc-taxonomy-filter' ) . '</p>';
}

/**
 * Enable archive filters callback
 */
function jc_taxonomy_filter_enable_archive_callback() {
	$options = jc_taxonomy_filter_get_options();
	?>
	<input type="checkbox" name="jc_taxonomy_filter_options[enable_archive_filters]" value="1" <?php checked( 1, $options['enable_archive_filters'] ); ?> />
	<label for="jc_taxonomy_filter_options[enable_archive_filters]"><?php _e( 'Enable taxonomy filters on archive pages', 'jc-taxonomy-filter' ); ?></label>
	<?php
}

/**
 * Archive taxonomy callback
 */
function jc_taxonomy_filter_archive_taxonomy_callback() {
	$options    = jc_taxonomy_filter_get_options();
	$taxonomies = get_taxonomies(
		array(
			'public'  => true,
			'show_ui' => true,
		),
		'objects'
	);
	?>
	<select name="jc_taxonomy_filter_options[archive_taxonomy]">
		<?php foreach ( $taxonomies as $tax ) : ?>
			<option value="<?php echo esc_attr( $tax->name ); ?>" <?php selected( $options['archive_taxonomy'], $tax->name ); ?>>
				<?php echo esc_html( $tax->label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}

/**
 * Archive layout callback
 */
function jc_taxonomy_filter_archive_layout_callback() {
	$options = jc_taxonomy_filter_get_options();
	?>
	<select name="jc_taxonomy_filter_options[archive_layout]">
		<option value="horizontal" <?php selected( $options['archive_layout'], 'horizontal' ); ?>><?php _e( 'Horizontal', 'jc-taxonomy-filter' ); ?></option>
		<option value="vertical" <?php selected( $options['archive_layout'], 'vertical' ); ?>><?php _e( 'Vertical', 'jc-taxonomy-filter' ); ?></option>
	</select>
	<?php
}

/**
 * Archive style callback
 */
function jc_taxonomy_filter_archive_style_callback() {
	$options = jc_taxonomy_filter_get_options();
	?>
	<select name="jc_taxonomy_filter_options[archive_style]">
		<option value="buttons" <?php selected( $options['archive_style'], 'buttons' ); ?>><?php _e( 'Buttons', 'jc-taxonomy-filter' ); ?></option>
		<option value="pills" <?php selected( $options['archive_style'], 'pills' ); ?>><?php _e( 'Pills', 'jc-taxonomy-filter' ); ?></option>
		<option value="links" <?php selected( $options['archive_style'], 'links' ); ?>><?php _e( 'Links', 'jc-taxonomy-filter' ); ?></option>
	</select>
	<?php
}

/**
 * Archive pagination callback
 */
function jc_taxonomy_filter_archive_pagination_callback() {
	$options = jc_taxonomy_filter_get_options();
	?>
	<input type="checkbox" name="jc_taxonomy_filter_options[archive_show_pagination]" value="1" <?php checked( 1, $options['archive_show_pagination'] ); ?> />
	<label for="jc_taxonomy_filter_options[archive_show_pagination]"><?php _e( 'Show pagination controls', 'jc-taxonomy-filter' ); ?></label>
	<?php
}

/**
 * Inject filters on archive pages
 */
function jc_taxonomy_filter_inject_archive_filters() {
	$options = jc_taxonomy_filter_get_options();

	if ( ! $options['enable_archive_filters'] ) {
		return;
	}

	// Only on archive pages (blog, category, tag, etc.)
	if ( ! is_archive() && ! is_home() ) {
		return;
	}

	// Get current taxonomy term if on taxonomy archive
	$current_term     = '';
	$current_taxonomy = $options['archive_taxonomy'];

	if ( is_category() ) {
		$current_term     = get_queried_object()->slug;
		$current_taxonomy = 'category';
	} elseif ( is_tag() ) {
		$current_term     = get_queried_object()->slug;
		$current_taxonomy = 'post_tag';
	} elseif ( is_tax() ) {
		$current_term     = get_queried_object()->slug;
		$current_taxonomy = get_queried_object()->taxonomy;
	}

	// Generate filter HTML
	$filter_html = jc_taxonomy_filter_generate_archive_html( $options, $current_taxonomy, $current_term );

	if ( $filter_html ) {
		echo $filter_html;
	}
}
add_action( 'loop_start', 'jc_taxonomy_filter_inject_archive_filters' );

/**
 * Generate filter HTML for archives
 */
function jc_taxonomy_filter_generate_archive_html( $options, $taxonomy, $current_term = '' ) {
	// Get terms for the taxonomy
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	$classes = array(
		'jc-taxonomy-filter',
		'jc-taxonomy-filter--' . $options['archive_layout'],
		'jc-taxonomy-filter--' . $options['archive_style'],
	);

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
		data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
		data-query-id=".site-main"
		data-per-page="10"
		data-current-term="<?php echo esc_attr( $current_term ); ?>"
		data-current-page="1">

		<button type="button" class="jc-taxonomy-filter__button<?php echo empty( $current_term ) ? ' is-active' : ''; ?>" data-term-slug="">
			<?php _e( 'All', 'jc-taxonomy-filter' ); ?>
		</button>

		<?php foreach ( $terms as $term ) : ?>
			<button type="button" class="jc-taxonomy-filter__button<?php echo $current_term === $term->slug ? ' is-active' : ''; ?>" data-term-slug="<?php echo esc_attr( $term->slug ); ?>" data-term-id="<?php echo esc_attr( $term->term_id ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</button>
		<?php endforeach; ?>

		<?php if ( $options['archive_show_pagination'] ) : ?>
			<div class="jc-pagination" style="display: none;">
				<button type="button" class="jc-pagination__prev" disabled>&larr; <?php _e( 'Previous', 'jc-taxonomy-filter' ); ?></button>
				<span class="jc-pagination__info"><?php _e( 'Page 1 of 1', 'jc-taxonomy-filter' ); ?></span>
				<button type="button" class="jc-pagination__next" disabled><?php _e( 'Next', 'jc-taxonomy-filter' ); ?> &rarr;</button>
			</div>
		<?php endif; ?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Enqueue scripts on archive pages
 */
function jc_taxonomy_filter_enqueue_archive_scripts() {
	$options = jc_taxonomy_filter_get_options();

	if ( ! $options['enable_archive_filters'] ) {
		return;
	}

	if ( ! is_archive() && ! is_home() ) {
		return;
	}

	wp_enqueue_script( 'jc-taxonomy-filter-frontend' );
	wp_enqueue_style( 'jc-taxonomy-filter-style' );
}
add_action( 'wp_enqueue_scripts', 'jc_taxonomy_filter_enqueue_archive_scripts' );