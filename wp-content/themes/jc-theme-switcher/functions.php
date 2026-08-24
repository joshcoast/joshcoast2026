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
 * Fallback menu output for the primary nav when no menu is assigned.
 *
 * @return void
 */
function jc_16bit_arcade_primary_menu_fallback() {
	$home_url     = home_url( '/' );
	$projects_url = home_url( '/projects/' );
	$notes_url    = home_url( '/joshs-notes/' );
	$linkedin_url = apply_filters( 'jc_16bit_arcade_linkedin_url', 'https://www.linkedin.com/' );

	$items = array(
		array(
			'label' => __( 'Home', 'jc-16bit-arcade' ),
			'url'   => $home_url,
		),
		array(
			'label' => __( 'Projects', 'jc-16bit-arcade' ),
			'url'   => $projects_url,
		),
		array(
			'label' => __( 'Notes', 'jc-16bit-arcade' ),
			'url'   => $notes_url,
		),
		array(
			'label'    => __( 'LinkedIn', 'jc-16bit-arcade' ),
			'url'      => $linkedin_url,
			'external' => true,
		),
	);

	echo '<ul class="menu">';
	foreach ( $items as $item ) {
		$is_external = ! empty( $item['external'] );
		$target      = $is_external ? ' target="_blank" rel="noopener noreferrer"' : '';
		$is_linkedin = $is_external && false !== strpos( strtolower( $item['url'] ), 'linkedin.com' );

		$icon_markup = $is_linkedin ? jc_16bit_arcade_external_link_icon_markup() : '';

		echo '<li class="menu-item">';
		echo '<a href="' . esc_url( $item['url'] ) . '"' . $target . '>' . esc_html( $item['label'] ) . $icon_markup . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * External-site icon markup used in nav/buttons for off-site new-tab links.
 *
 * @return string
 */
function jc_16bit_arcade_external_link_icon_markup() {
	return ' <span class="jc-icon-external" aria-hidden="true"><svg viewBox="0 0 12 12" focusable="false"><path d="M2 10h8V7h1v4H1V1h4v1H2z" fill="currentColor"/><path d="M6 1h5v5H9.5V3.9L6 7.4 4.6 6 8.1 2.5H6z" fill="currentColor"/></svg></span><span class="screen-reader-text"> Opens on another site in a new tab</span>';
}

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
		'labels'              => $labels,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'has_archive'         => 'clients',
		'rewrite'             => array(
			'slug'       => 'clients',
			'with_front' => false,
		),
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-groups',
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
	);

	register_post_type( 'client', $args );
}
add_action( 'init', 'jc_16bit_arcade_register_client_post_type' );

/**
 * Register a skills taxonomy for the Client post type.
 */
function jc_16bit_arcade_register_client_skill_taxonomy() {
	$labels = array(
		'name'                       => __( 'Client Skills', 'jc-16bit-arcade' ),
		'singular_name'              => __( 'Client Skill', 'jc-16bit-arcade' ),
		'menu_name'                  => __( 'Client Skills', 'jc-16bit-arcade' ),
		'all_items'                  => __( 'All Client Skills', 'jc-16bit-arcade' ),
		'edit_item'                  => __( 'Edit Client Skill', 'jc-16bit-arcade' ),
		'view_item'                  => __( 'View Client Skill', 'jc-16bit-arcade' ),
		'update_item'                => __( 'Update Client Skill', 'jc-16bit-arcade' ),
		'add_new_item'               => __( 'Add New Client Skill', 'jc-16bit-arcade' ),
		'new_item_name'              => __( 'New Client Skill Name', 'jc-16bit-arcade' ),
		'search_items'               => __( 'Search Client Skills', 'jc-16bit-arcade' ),
		'popular_items'              => __( 'Popular Client Skills', 'jc-16bit-arcade' ),
		'separate_items_with_commas' => __( 'Separate client skills with commas', 'jc-16bit-arcade' ),
		'add_or_remove_items'        => __( 'Add or remove client skills', 'jc-16bit-arcade' ),
		'choose_from_most_used'      => __( 'Choose from the most used client skills', 'jc-16bit-arcade' ),
		'not_found'                  => __( 'No client skills found.', 'jc-16bit-arcade' ),
	);

	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'hierarchical'      => false,
		'rewrite'           => array(
			'slug'       => 'client-skill',
			'with_front' => false,
		),
	);

	register_taxonomy( 'client_skill', array( 'client' ), $args );
}
add_action( 'init', 'jc_16bit_arcade_register_client_skill_taxonomy' );

/**
 * Seed baseline client skills used by the Projects page.
 */
function jc_16bit_arcade_seed_client_skill_terms() {
	$seed_terms = array(
		'REST API',
		'WordPress',
		'Design',
		'SCSS/HTML/JSX',
		'WP Theme',
		'SCSS/HTML/JS',
		'Figma',
		'React',
		'Custom Blocks',
		'UX Design',
		'Headless',
		'Craft CMS',
		'PHP',
		'Twig',
		'Tailwind',
	);

	foreach ( $seed_terms as $term_name ) {
		$slug       = sanitize_title( $term_name );
		$term_check = term_exists( $slug, 'client_skill' );

		if ( $term_check ) {
			continue;
		}

		wp_insert_term(
			$term_name,
			'client_skill',
			array(
				'slug' => $slug,
			)
		);
	}
}
add_action( 'init', 'jc_16bit_arcade_seed_client_skill_terms', 20 );

/**
 * Register the custom project block for selecting and rendering Client content.
 */
function jc_16bit_arcade_register_project_client_block() {
	$script_handle = 'jc-project-client-card-block';
	$script_path   = get_template_directory() . '/assets/js/blocks/project-client-card.js';
	$script_url    = get_template_directory_uri() . '/assets/js/blocks/project-client-card.js';
	$script_ver    = file_exists( $script_path ) ? (string) filemtime( $script_path ) : wp_get_theme()->get( 'Version' );

	wp_register_script(
		$script_handle,
		$script_url,
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data', 'wp-i18n' ),
		$script_ver,
		true
	);

	register_block_type(
		'jc/project-client-card',
		array(
			'api_version'     => 2,
			'editor_script'   => $script_handle,
			'render_callback' => 'jc_16bit_arcade_render_project_client_block',
			'attributes'      => array(
				'clientId' => array(
					'type'    => 'number',
					'default' => 0,
				),
				'title' => array(
					'type'    => 'string',
					'default' => '',
				),
				'description' => array(
					'type'    => 'string',
					'default' => '',
				),
				'actionLabel' => array(
					'type'    => 'string',
					'default' => __( 'View Project', 'jc-16bit-arcade' ),
				),
				'actionUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
				'imageId' => array(
					'type'    => 'number',
					'default' => 0,
				),
				'imageUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
				'imageAlt' => array(
					'type'    => 'string',
					'default' => '',
				),
				'imageOnRight' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
		)
	);
}
add_action( 'init', 'jc_16bit_arcade_register_project_client_block', 30 );

/**
 * Render callback for the project client card block.
 *
 * @param array<string,mixed> $attributes Block attributes.
 * @return string
 */
function jc_16bit_arcade_render_project_client_block( $attributes ) {
	$client_id      = isset( $attributes['clientId'] ) ? absint( $attributes['clientId'] ) : 0;
	$image_id       = isset( $attributes['imageId'] ) ? absint( $attributes['imageId'] ) : 0;
	$image_url      = isset( $attributes['imageUrl'] ) ? esc_url_raw( (string) $attributes['imageUrl'] ) : '';
	$image_alt      = isset( $attributes['imageAlt'] ) ? sanitize_text_field( (string) $attributes['imageAlt'] ) : '';
	$title          = isset( $attributes['title'] ) ? sanitize_text_field( (string) $attributes['title'] ) : '';
	$description    = isset( $attributes['description'] ) ? trim( (string) $attributes['description'] ) : '';
	$action_label   = isset( $attributes['actionLabel'] ) ? sanitize_text_field( (string) $attributes['actionLabel'] ) : '';
	$action_url     = isset( $attributes['actionUrl'] ) ? esc_url_raw( (string) $attributes['actionUrl'] ) : '';
	$image_on_right = ! empty( $attributes['imageOnRight'] );

	if ( ! $client_id || 'client' !== get_post_type( $client_id ) ) {
		if ( is_admin() ) {
			return '<div class="jc-project-client-card jc-project-client-card--placeholder"><p>' . esc_html__( 'Select a Client to render this block.', 'jc-16bit-arcade' ) . '</p></div>';
		}

		return '';
	}

	$client_title = get_the_title( $client_id );

	if ( '' === $title ) {
		$title = $client_title;
	}

	if ( '' === $action_label ) {
		$action_label = __( 'View Project', 'jc-16bit-arcade' );
	}

	if ( '' === $action_url ) {
		$action_url = get_permalink( $client_id );
	}

	if ( '' === $image_alt ) {
		$image_alt = $title;
	}

	$image_markup = '';

	if ( $image_id ) {
		$image_markup = wp_get_attachment_image(
			$image_id,
			'large',
			false,
			array(
				'class'    => 'jc-project-client-card__image',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'alt'      => $image_alt,
			)
		);
	} elseif ( '' !== $image_url ) {
		$image_markup = '<img class="jc-project-client-card__image" src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image_alt ) . '" loading="lazy" decoding="async" />';
	} elseif ( has_post_thumbnail( $client_id ) ) {
		$image_markup = get_the_post_thumbnail(
			$client_id,
			'large',
			array(
				'class'    => 'jc-project-client-card__image',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'alt'      => $image_alt,
			)
		);
	}

	if ( '' === $image_markup ) {
		$image_markup = '<div class="jc-project-client-card__image-placeholder">' . esc_html__( 'No client image selected.', 'jc-16bit-arcade' ) . '</div>';
	}

	$skills = get_the_terms( $client_id, 'client_skill' );
	$classes = array( 'jc-project-client-card' );

	if ( $image_on_right ) {
		$classes[] = 'jc-project-client-card--image-right';
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => implode( ' ', $classes ),
		)
	);

	ob_start();
	?>
	<article <?php echo $wrapper_attributes; ?>>
		<figure class="jc-project-client-card__media">
			<div class="jc-project-client-card__media-frame">
				<?php echo $image_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</figure>
		<div class="jc-project-client-card__content">
			<?php if ( '' !== $title ) : ?>
				<h2 class="jc-project-client-card__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( '' !== $description ) : ?>
				<p class="jc-project-client-card__description"><?php echo nl2br( esc_html( $description ) ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $skills ) && ! is_wp_error( $skills ) ) : ?>
				<ul class="jc-project-client-card__skills" aria-label="<?php esc_attr_e( 'Client skills', 'jc-16bit-arcade' ); ?>">
					<?php foreach ( $skills as $skill ) : ?>
						<li class="jc-project-client-card__skill"><?php echo esc_html( $skill->name ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( '' !== $action_url ) : ?>
				<a class="jc-btn jc-btn--sm jc-project-client-card__action" href="<?php echo esc_url( $action_url ); ?>"><?php echo esc_html( $action_label ); ?></a>
			<?php endif; ?>
		</div>
	</article>
	<?php

	return (string) ob_get_clean();
}

function jc_16bit_arcade_assets() {
	wp_enqueue_style( 'jc-16bit-google-fonts', 'https://fonts.googleapis.com/css2?family=Ultra&family=Press+Start+2P&family=VT323&display=swap', array(), null );
	wp_enqueue_style( 'jc-16bit-style', get_template_directory_uri() . '/assets/css/style.min.css', array( 'jc-16bit-google-fonts' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'jc-16bit-theme-arcade', get_template_directory_uri() . '/assets/css/theme-arcade.min.css', array( 'jc-16bit-style' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'jc-16bit-theme-stripes', get_template_directory_uri() . '/assets/css/theme-stripes.min.css', array( 'jc-16bit-theme-arcade' ), wp_get_theme()->get( 'Version' ) );
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

/**
 * Resolve the active style scheme with a safe default.
 *
 * @return string 'arcade' or 'stripes'.
 */
function jc_16bit_arcade_get_style_scheme() {
	$style_scheme = '';

	if ( isset( $_COOKIE['jc_style_scheme'] ) ) {
		$cookie_scheme = sanitize_key( wp_unslash( $_COOKIE['jc_style_scheme'] ) );
		if ( 'neon' === $cookie_scheme ) {
			$cookie_scheme = 'stripes';
		}

		if ( in_array( $cookie_scheme, array( 'arcade', 'stripes' ), true ) ) {
			$style_scheme = $cookie_scheme;
		}
	}

	if ( '' === $style_scheme ) {
		$style_scheme = 'stripes';
	}

	return $style_scheme;
}

function jc_16bit_arcade_body_classes( $classes ) {
	$style_scheme = jc_16bit_arcade_get_style_scheme();

	$classes[] = 'jc-16bit-theme';
	$classes[] = 'style-scheme-' . $style_scheme;

	if ( is_front_page() ) {
		$classes[] = 'is-theme-home';
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

	if ( false !== strpos( $title, 'jc-icon-external' ) || false !== strpos( $title, 'jc-external-icon' ) ) {
		return $title;
	}

	return $title . jc_16bit_arcade_external_link_icon_markup();
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
				return '<svg viewBox="0 0 20 20" class="jc-cats__icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#21759b"/><circle cx="10" cy="10" r="7" fill="#fff"/><path d="M5.9 6.2h1.8l1.6 6.1 1.1-3.7h1.2l1.1 3.7 1.6-6.1h1.8l-2.5 8.3h-1.6l-1-3.2-1 3.2H8.4z" fill="#21759b"/></svg>';

		case 'javascript':
		case 'js':
			return '<svg viewBox="0 0 20 20" class="jc-cats__icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#f7df1e"/><rect x="4" y="4" width="12" height="12" rx="1" fill="#121212"/><path d="M8 7v4.8c0 1.2-.6 1.9-1.8 1.9-.6 0-1.1-.2-1.5-.5" fill="none" stroke="#f7df1e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12.9 7.2c-.9 0-1.5.4-1.5 1s.5.9 1.4 1.2c1.3.4 2.1 1 2.1 2.3 0 1.4-1.2 2.3-2.8 2.3-1.1 0-2.1-.4-2.7-1.2" fill="none" stroke="#f7df1e" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		case 'css':
		case 'css3':
			return '<svg viewBox="0 0 20 20" class="jc-cats__icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#0b3a7e"/><path d="M4 4h12l-1 10-5 2-5-2z" fill="#42a5f5"/><path d="M10 6v8l4-2 .6-6z" fill="#90caf9"/></svg>';

		case 'react':
			return '<svg viewBox="0 0 20 20" class="jc-cats__icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#102332"/><circle cx="10" cy="10" r="2" fill="#61dafb"/><ellipse cx="10" cy="10" rx="7" ry="3" fill="none" stroke="#61dafb" stroke-width="1.2"/><ellipse cx="10" cy="10" rx="7" ry="3" fill="none" stroke="#61dafb" stroke-width="1.2" transform="rotate(60 10 10)"/><ellipse cx="10" cy="10" rx="7" ry="3" fill="none" stroke="#61dafb" stroke-width="1.2" transform="rotate(120 10 10)"/></svg>';

		case 'php':
			return '<svg viewBox="0 0 20 20" class="jc-cats__icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#4f5b93"/><ellipse cx="10" cy="10" rx="7" ry="4" fill="#fff"/><rect x="5" y="9" width="2" height="2" fill="#4f5b93"/><rect x="8" y="9" width="2" height="2" fill="#4f5b93"/><rect x="11" y="9" width="2" height="2" fill="#4f5b93"/><rect x="14" y="9" width="1" height="2" fill="#4f5b93"/></svg>';

		default:
			return '<svg viewBox="0 0 20 20" class="jc-cats__icon" aria-hidden="true" focusable="false"><rect width="20" height="20" rx="3" fill="#5e35b1"/><rect x="4" y="6" width="12" height="9" fill="#d1c4e9"/><rect x="4" y="5" width="6" height="3" fill="#b39ddb"/></svg>';
	}
}

function jc_16bit_arcade_render_category_icons( $post_id = 0 ) {
	$post_id    = $post_id ? (int) $post_id : get_the_ID();
	$categories = get_the_category( $post_id );

	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		return;
	}

	echo '<ul class="jc-cats" aria-label="Post categories">';
	foreach ( $categories as $category ) {
		$chip_slug = sanitize_html_class( $category->slug );
		echo '<li class="jc-cats__chip jc-cats__chip--' . esc_attr( $chip_slug ) . '">';
		echo jc_16bit_arcade_category_icon_svg( $category->slug );
		echo '<span class="jc-cats__name">' . esc_html( $category->name ) . '</span>';
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
	$post_id       = $post_id ? (int) $post_id : get_the_ID();
	$categories    = get_the_category( $post_id );
	$fallback_slug = 'tools';
	$fallback      = array(
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

	echo '<div class="jc-card__thumb jc-card__thumb--' . esc_attr( $image['slug'] ) . '">';
	echo '<img src="' . esc_url( $image['url'] ) . '" alt="' . esc_attr( $image['label'] ) . '" loading="lazy" decoding="async" />';
	echo '</div>';
}
