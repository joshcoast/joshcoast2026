<?php
/**
 * Plugin Name: JC Taxonomy Filter
 * Description: AJAX-powered taxonomy filter block for filtering posts by category/tag
 * Version: 1.0.0
 * Author: Josh Coast
 * Text Domain: jc-taxonomy-filter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JC_TAX_FILTER_VERSION', '1.0.0' );
define( 'JC_TAX_FILTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'JC_TAX_FILTER_URL', plugin_dir_url( __FILE__ ) );

// Include settings
require_once JC_TAX_FILTER_PATH . 'includes/settings.php';

/**
 * Register the block and assets
 */
function jc_taxonomy_filter_init() {
	// Register block editor script
	wp_register_script(
		'jc-taxonomy-filter-editor',
		JC_TAX_FILTER_URL . 'build/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-data' ),
		JC_TAX_FILTER_VERSION,
		true
	);

	// Register frontend script
	wp_register_script(
		'jc-taxonomy-filter-frontend',
		JC_TAX_FILTER_URL . 'build/frontend.js',
		array(),
		JC_TAX_FILTER_VERSION,
		true
	);

	// Register styles
	wp_register_style(
		'jc-taxonomy-filter-style',
		JC_TAX_FILTER_URL . 'build/style.css',
		array(),
		JC_TAX_FILTER_VERSION
	);

	wp_register_style(
		'jc-taxonomy-filter-editor-style',
		JC_TAX_FILTER_URL . 'build/editor.css',
		array(),
		JC_TAX_FILTER_VERSION
	);

	// Register the block
	register_block_type(
		'jc/taxonomy-filter',
		array(
			'editor_script'   => 'jc-taxonomy-filter-editor',
			'editor_style'    => 'jc-taxonomy-filter-editor-style',
			'style'           => 'jc-taxonomy-filter-style',
			'script'          => 'jc-taxonomy-filter-frontend',
			'render_callback' => 'jc_taxonomy_filter_render',
			'attributes'      => array(
				'taxonomy'       => array(
					'type'    => 'string',
					'default' => 'category',
				),
				'showAll'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'allLabel'       => array(
					'type'    => 'string',
					'default' => 'All',
				),
				'queryId'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'layout'         => array(
					'type'    => 'string',
					'default' => 'horizontal',
				),
				'style'          => array(
					'type'    => 'string',
					'default' => 'buttons',
				),
				'showPagination' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
		)
	);
}
add_action( 'init', 'jc_taxonomy_filter_init' );

/**
 * Render callback for the block
 */
function jc_taxonomy_filter_render( $attributes ) {
	$taxonomy        = $attributes['taxonomy'] ?? 'category';
	$show_all        = $attributes['showAll'] ?? true;
	$all_label       = $attributes['allLabel'] ?? 'All';
	$query_id        = $attributes['queryId'] ?? '';
	$layout          = $attributes['layout'] ?? 'horizontal';
	$style           = $attributes['style'] ?? 'buttons';
	$show_pagination = $attributes['showPagination'] ?? false;

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

	// Get current filter from URL
	$current_term = isset( $_GET[ 'filter_' . $taxonomy ] ) ? sanitize_text_field( $_GET[ 'filter_' . $taxonomy ] ) : '';

	$classes = array(
		'jc-taxonomy-filter',
		'jc-taxonomy-filter--' . $layout,
		'jc-taxonomy-filter--' . $style,
	);

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" 
		data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
		data-query-id="<?php echo esc_attr( $query_id ); ?>">
		
		<?php if ( $show_all ) : ?>
			<button type="button" 
					class="jc-taxonomy-filter__button<?php echo empty( $current_term ) ? ' is-active' : ''; ?>" 
					data-term-slug="">
				<?php echo esc_html( $all_label ); ?>
			</button>
		<?php endif; ?>

		<?php foreach ( $terms as $term ) : ?>
			<button type="button" 
					class="jc-taxonomy-filter__button<?php echo $current_term === $term->slug ? ' is-active' : ''; ?>" 
					data-term-slug="<?php echo esc_attr( $term->slug ); ?>"
					data-term-id="<?php echo esc_attr( $term->term_id ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</button>
		<?php endforeach; ?>

		<?php if ( $show_pagination ) : ?>
			<div class="jc-pagination" style="display: none;">
				<button type="button" class="jc-pagination__prev" disabled>&larr; Previous</button>
				<span class="jc-pagination__info">Page 1 of 1</span>
				<button type="button" class="jc-pagination__next" disabled>Next &rarr;</button>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * REST API endpoint for filtered posts
 */
function jc_taxonomy_filter_register_rest_routes() {
	register_rest_route(
		'jc-taxonomy-filter/v1',
		'/posts',
		array(
			'methods'             => 'GET',
			'callback'            => 'jc_taxonomy_filter_get_posts',
			'permission_callback' => '__return_true',
			'args'                => array(
				'taxonomy'  => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'term'      => array(
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'post_type' => array(
					'required'          => false,
					'default'           => 'post',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'per_page'  => array(
					'required'          => false,
					'default'           => 10,
					'sanitize_callback' => 'absint',
				),
				'page'      => array(
					'required'          => false,
					'default'           => 1,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'jc_taxonomy_filter_register_rest_routes' );

/**
 * Get filtered posts
 */
function jc_taxonomy_filter_get_posts( $request ) {
	$taxonomy  = $request->get_param( 'taxonomy' );
	$term      = $request->get_param( 'term' );
	$post_type = $request->get_param( 'post_type' );
	$per_page  = $request->get_param( 'per_page' );
	$page      = $request->get_param( 'page' );

	$args = array(
		'post_type'      => $post_type,
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'post_status'    => 'publish',
	);

	// Add taxonomy query if term is specified
	if ( ! empty( $term ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $term,
			),
		);
	}

	$query = new WP_Query( $args );
	$posts = array();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$excerpt = get_the_excerpt();
			if ( ! empty( $excerpt ) ) {
				$excerpt .= ' <a title="' . get_the_title() . '" class="read-more" href="' . get_permalink() . '" aria-label="Read more about ' . get_the_title() . '">' . __( 'View', 'jc-taxonomy-filter' ) . '</a>';
			}
			$posts[] = array(
				'id'         => get_the_ID(),
				'title'      => get_the_title(),
				'excerpt'    => $excerpt,
				'link'       => get_permalink(),
				'date'       => get_the_date(),
				'thumbnail'  => get_the_post_thumbnail( get_the_ID(), 'full' ),
				'categories' => wp_get_post_categories( get_the_ID(), array( 'fields' => 'names' ) ),
				'tags'       => wp_get_post_tags( get_the_ID(), array( 'fields' => 'names' ) ),
			);
		}
		wp_reset_postdata();
	}

	return new WP_REST_Response(
		array(
			'posts'        => $posts,
			'total'        => $query->found_posts,
			'pages'        => $query->max_num_pages,
			'current_page' => $page,
		),
		200
	);
}
