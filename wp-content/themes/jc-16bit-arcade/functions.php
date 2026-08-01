<?php
/**
 * Theme setup for JC 16-Bit Arcade Resume.
 *
 * @package jc_16bit_arcade
 */

if ( ! function_exists( 'jc_16bit_arcade_setup' ) ) {
	function jc_16bit_arcade_setup() {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'jc-16bit-arcade' ),
			)
		);
	}
}
add_action( 'after_setup_theme', 'jc_16bit_arcade_setup' );

/**
 * Register a lightweight Client post type used by legacy portfolio content.
 */
function jc_16bit_arcade_register_client_post_type() {
	$labels = array(
		'name'                  => __( 'Clients', 'jc-16bit-arcade' ),
		'singular_name'         => __( 'Client', 'jc-16bit-arcade' ),
		'menu_name'             => __( 'Clients', 'jc-16bit-arcade' ),
		'name_admin_bar'        => __( 'Client', 'jc-16bit-arcade' ),
		'add_new'               => __( 'Add New', 'jc-16bit-arcade' ),
		'add_new_item'          => __( 'Add New Client', 'jc-16bit-arcade' ),
		'new_item'              => __( 'New Client', 'jc-16bit-arcade' ),
		'edit_item'             => __( 'Edit Client', 'jc-16bit-arcade' ),
		'view_item'             => __( 'View Client', 'jc-16bit-arcade' ),
		'all_items'             => __( 'All Clients', 'jc-16bit-arcade' ),
		'search_items'          => __( 'Search Clients', 'jc-16bit-arcade' ),
		'not_found'             => __( 'No clients found.', 'jc-16bit-arcade' ),
		'not_found_in_trash'    => __( 'No clients found in Trash.', 'jc-16bit-arcade' ),
		'archives'              => __( 'Client Archives', 'jc-16bit-arcade' ),
		'attributes'            => __( 'Client Attributes', 'jc-16bit-arcade' ),
		'insert_into_item'      => __( 'Insert into client', 'jc-16bit-arcade' ),
		'uploaded_to_this_item' => __( 'Uploaded to this client', 'jc-16bit-arcade' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'has_archive'        => 'clients',
		'rewrite'            => array(
			'slug'       => 'clients',
			'with_front' => false,
		),
		'show_in_rest'       => true,
		'menu_icon'          => 'dashicons-groups',
		'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		'publicly_queryable' => true,
		'exclude_from_search'=> false,
	);

	register_post_type( 'client', $args );
}
add_action( 'init', 'jc_16bit_arcade_register_client_post_type' );

function jc_16bit_arcade_assets() {
	wp_enqueue_style( 'jc-16bit-google-fonts', 'https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&display=swap', array(), null );
	wp_enqueue_style( 'jc-16bit-style', get_stylesheet_uri(), array( 'jc-16bit-google-fonts' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_script( 'jc-16bit-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), wp_get_theme()->get( 'Version' ), true );
}
add_action( 'wp_enqueue_scripts', 'jc_16bit_arcade_assets' );

/**
 * Keep WP preset text colors readable against the dark arcade theme in the editor.
 */
function jc_16bit_arcade_editor_palette_fixes() {
	$editor_css = '.editor-styles-wrapper{--wp--preset--color--base-3:#ff4fd8;}.editor-styles-wrapper .has-base-3-color{color:var(--wp--preset--color--base-3)!important;}';
	wp_add_inline_style( 'wp-edit-blocks', $editor_css );
}
add_action( 'enqueue_block_editor_assets', 'jc_16bit_arcade_editor_palette_fixes' );

function jc_16bit_arcade_body_classes( $classes ) {
	$classes[] = 'jc-16bit-theme';

	if ( is_front_page() ) {
		$classes[] = 'is-arcade-home';
	}

	return $classes;
}
add_filter( 'body_class', 'jc_16bit_arcade_body_classes' );

/**
 * Add an external-link icon to LinkedIn items in the primary nav.
 *
 * @param string   $title Item title HTML.
 * @param WP_Post  $item  Menu item object.
 * @param stdClass $args  Nav menu args.
 * @param int      $depth Menu depth.
 * @return string
 */
function jc_16bit_arcade_nav_linkedin_icon( $title, $item, $args, $depth ) {
	if ( ! isset( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $title;
	}

	if ( empty( $item->url ) || false === strpos( strtolower( $item->url ), 'linkedin.com' ) ) {
		return $title;
	}

	if ( false !== strpos( $title, 'external-site-icon' ) ) {
		return $title;
	}

	$icon = '<span class="external-site-icon" aria-hidden="true"><svg viewBox="0 0 14 14" focusable="false"><path d="M3 11h8V7h2v6H1V1h6v2H3z" fill="currentColor"/><path d="M8 1h5v5h-2V4.4L6.7 8.7 5.3 7.3 9.6 3H8z" fill="currentColor"/></svg></span><span class="screen-reader-text"> Opens LinkedIn in a new tab</span>';

	return $title . ' ' . $icon;
}
add_filter( 'nav_menu_item_title', 'jc_16bit_arcade_nav_linkedin_icon', 10, 4 );

function jc_16bit_arcade_humor_line() {
	$lines = array(
		'New challenge unlocked: Making deadlines look easy.',
		'Powered by coffee, curiosity, and occasional boss battles.',
		'Currently grinding side quests for better UX loot.',
		'No NPCs were harmed in the making of this portfolio.',
		'Please enjoy this handcrafted chaos in glorious pseudo-8K.',
	);

	return $lines[ array_rand( $lines ) ];
}

function jc_16bit_arcade_post_count( $post_type = 'post' ) {
	$count = wp_count_posts( $post_type );
	if ( ! $count || ! isset( $count->publish ) ) {
		return 0;
	}

	return (int) $count->publish;
}

function jc_16bit_arcade_category_icon_svg( $slug ) {
	$key = sanitize_title( (string) $slug );

	switch ( $key ) {
		case 'wordpress':
		case 'wp':
			return '<svg viewBox="0 0 20 20" class="cat-icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#21759b"/><circle cx="10" cy="10" r="7" fill="#fff"/><path d="M6 6h2l2 8 1-3 1 3 2-8h2l-3 10h-2l-1-3-1 3H9z" fill="#21759b"/></svg>';

		case 'javascript':
		case 'js':
			return '<svg viewBox="0 0 20 20" class="cat-icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#f7df1e"/><rect x="4" y="4" width="12" height="12" fill="#121212"/><rect x="7" y="6" width="2" height="7" fill="#f7df1e"/><rect x="10" y="11" width="2" height="2" fill="#f7df1e"/><rect x="12" y="6" width="2" height="2" fill="#f7df1e"/><rect x="12" y="9" width="2" height="4" fill="#f7df1e"/></svg>';

		case 'css':
		case 'css3':
			return '<svg viewBox="0 0 20 20" class="cat-icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#0b3a7e"/><path d="M4 4h12l-1 10-5 2-5-2z" fill="#42a5f5"/><path d="M10 6v8l4-2 .6-6z" fill="#90caf9"/></svg>';

		case 'react':
			return '<svg viewBox="0 0 20 20" class="cat-icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#102332"/><circle cx="10" cy="10" r="2" fill="#61dafb"/><ellipse cx="10" cy="10" rx="7" ry="3" fill="none" stroke="#61dafb" stroke-width="1.2"/><ellipse cx="10" cy="10" rx="7" ry="3" fill="none" stroke="#61dafb" stroke-width="1.2" transform="rotate(60 10 10)"/><ellipse cx="10" cy="10" rx="7" ry="3" fill="none" stroke="#61dafb" stroke-width="1.2" transform="rotate(120 10 10)"/></svg>';

		case 'php':
			return '<svg viewBox="0 0 20 20" class="cat-icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#4f5b93"/><ellipse cx="10" cy="10" rx="7" ry="4" fill="#fff"/><rect x="5" y="9" width="2" height="2" fill="#4f5b93"/><rect x="8" y="9" width="2" height="2" fill="#4f5b93"/><rect x="11" y="9" width="2" height="2" fill="#4f5b93"/><rect x="14" y="9" width="1" height="2" fill="#4f5b93"/></svg>';

		default:
			return '<svg viewBox="0 0 20 20" class="cat-icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#5e35b1"/><rect x="4" y="6" width="12" height="9" fill="#d1c4e9"/><rect x="4" y="5" width="6" height="3" fill="#b39ddb"/></svg>';
	}
}

function jc_16bit_arcade_render_category_icons( $post_id = 0 ) {
	$post_id    = $post_id ? (int) $post_id : get_the_ID();
	$categories = get_the_category( $post_id );

	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		return;
	}

	echo '<ul class="cat-row" aria-label="Post categories">';
	foreach ( $categories as $category ) {
		echo '<li class="cat-chip">';
		echo jc_16bit_arcade_category_icon_svg( $category->slug );
		echo '<span class="cat-name">' . esc_html( $category->name ) . '</span>';
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * Get the category image for a post based on its first category.
 *
 * Falls back to tools.jpg when the category is uncategorized or has no image.
 *
 * @param int $post_id Post ID.
 * @return array{slug:string,label:string,url:string}
 */
function jc_16bit_arcade_get_post_category_image( $post_id = 0 ) {
	$post_id      = $post_id ? (int) $post_id : get_the_ID();
	$categories   = get_the_category( $post_id );
	$fallback_slug = 'tools';
	$fallback     = array(
		'slug'  => $fallback_slug,
		'label' => __( 'Tools', 'jc-16bit-arcade' ),
		'url'   => get_theme_file_uri( 'assets/img/catagories/tools.jpg' ),
	);

	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		return $fallback;
	}

	$primary = $categories[0];
	$slug    = sanitize_title( $primary->slug );

	if ( 'uncategorized' === $slug ) {
		return $fallback;
	}

	$relative_path = 'assets/img/catagories/' . $slug . '.jpg';
	$absolute_path = get_theme_file_path( $relative_path );

	if ( ! file_exists( $absolute_path ) ) {
		return $fallback;
	}

	return array(
		'slug'  => $slug,
		'label' => $primary->name,
		'url'   => get_theme_file_uri( $relative_path ),
	);
}

/**
 * Render the category image for a post card.
 *
 * @param int $post_id Post ID.
 */
function jc_16bit_arcade_render_post_card_image( $post_id = 0 ) {
	$image = jc_16bit_arcade_get_post_category_image( $post_id );

	echo '<div class="card-thumb card-thumb--' . esc_attr( $image['slug'] ) . '">';
	echo '<img src="' . esc_url( $image['url'] ) . '" alt="' . esc_attr( $image['label'] ) . '" loading="lazy" decoding="async" />';
	echo '</div>';
}
