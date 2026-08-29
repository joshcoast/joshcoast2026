<?php
/**
 * Front page template.
 *
 * @package jc_16bit_arcade
 */

get_header();

$linkedin_url = apply_filters( 'jc_16bit_arcade_linkedin_url', 'https://www.linkedin.com/' );
$resume_url   = 'https://joshcoast.com/wp-content/uploads/2026/08/Resume-2026.pdf';

$projects_page = get_page_by_path( 'projects' );
$projects_url  = $projects_page ? get_permalink( $projects_page ) : home_url( '/projects/' );
$clients_url   = post_type_exists( 'client' ) ? get_post_type_archive_link( 'client' ) : $projects_url;

$featured_projects_query = null;

if ( post_type_exists( 'client' ) ) {
	$featured_projects_query = new WP_Query(
		array(
			'post_type'      => 'client',
			'posts_per_page' => 3,
			'post_status'    => 'publish',
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
}

$skill_stats = array(
	array(
		'label'   => 'WORDPRESS DEVELOPMENT',
		'percent' => 96,
		'level'   => 'CUSTOM BLOCKS + THEME XP',
	),
	array(
		'label'   => 'UI/UX DESIGN',
		'percent' => 93,
		'level'   => 'PIXEL-PERFECT CRAFT',
	),
	array(
		'label'   => 'JAVASCRIPT',
		'percent' => 90,
		'level'   => 'INTERACTION COMBOS',
	),
);
?>
<section class="jc-panel jc-hero">
	<div class="jc-hero__eyebrow"><button id="player-one-trigger" class="jc-hero__firework-trigger" type="button">PLAYER ONE: <?php bloginfo( 'name' ); ?></button></div>
	<h1 class="jc-hero__title" data-text="<?php echo esc_attr( get_bloginfo( 'description' ) ); ?>"><?php bloginfo( 'description' ); ?></h1>
	<div class="jc-stats" role="list" aria-label="Top skills">
		<?php foreach ( $skill_stats as $skill ) : ?>
		<div class="jc-stat" role="listitem">
			<span class="jc-stat__label"><?php echo esc_html( $skill['label'] ); ?></span>
			<div class="jc-stat__progress">
				<span class="jc-stat__value"><?php echo esc_html( $skill['percent'] ); ?>%</span>
				<span class="jc-stat__level"><?php echo esc_html( $skill['level'] ); ?></span>
			</div>
			<div class="jc-stat__meter" role="presentation" aria-hidden="true">
				<span class="jc-stat__fill" style="width: <?php echo esc_attr( (int) $skill['percent'] ); ?>%;"></span>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<div class="jc-cta-row">
			<a class="jc-btn jc-btn--primary" href="<?php echo esc_url( $linkedin_url ); ?>" target="_blank" rel="noopener noreferrer">START CONVERSATION <span class="jc-external-icon" aria-hidden="true"><svg viewBox="0 0 14 14" focusable="false"><path d="M3 11h8V7h2v6H1V1h6v2H3z" fill="currentColor"/><path d="M8 1h5v5h-2V4.4L6.7 8.7 5.3 7.3 9.6 3H8z" fill="currentColor"/></svg></span><span class="screen-reader-text"> Opens LinkedIn in a new tab</span></a>
			<a class="jc-btn jc-btn--primary" href="<?php echo esc_url( $resume_url ); ?>" download>RESUME <span class="jc-external-icon" aria-hidden="true"><svg viewBox="0 0 14 14" focusable="false"><path d="M7 2v6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M4.9 6.6 7 8.9l2.1-2.3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 10h8v2H3z" fill="currentColor"/></svg></span><span class="screen-reader-text"> Download resume</span></a>
			<a class="jc-btn jc-btn--secondary" href="<?php echo esc_url( $projects_url ); ?>">VIEW FEATURED CLIENTS</a>
	</div>
	<div class="jc-humor-box">
		<strong>NPC Tip:</strong>
		<?php echo esc_html( jc_16bit_arcade_humor_line() ); ?>
	</div>
</section>

	<section class="jc-showcase" aria-labelledby="featured-projects-title">
		<div class="jc-showcase__head">
			<p class="jc-eyebrow">HIRING MODE: CLIENT WORK SHOWCASE</p>
			<h2 class="jc-section-title jc-showcase__title" id="featured-projects-title">FEATURED CLIENT PROJECTS</h2>
			<p class="jc-showcase__lead">The fastest way to evaluate fit is to review shipped client work. These highlights spotlight implementation quality, UX thinking, and production-ready details.</p>
		</div>

		<?php if ( $featured_projects_query && $featured_projects_query->have_posts() ) : ?>
		<div class="jc-showcase__grid">
			<?php
			while ( $featured_projects_query->have_posts() ) :
				$featured_projects_query->the_post();
				?>
				<article <?php post_class( 'jc-showcase__card' ); ?>>
					<?php if ( 'client' === get_post_type() && has_post_thumbnail() ) : ?>
						<div class="jc-card__thumb jc-card__thumb--featured">
							<?php
							the_post_thumbnail(
								'large',
								array(
									'loading'  => 'lazy',
									'decoding' => 'async',
								)
							);
							?>
						</div>
					<?php else : ?>
						<?php jc_16bit_arcade_render_post_card_image(); ?>
					<?php endif; ?>
					<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<?php if ( 'post' === get_post_type() ) : ?>
						<?php jc_16bit_arcade_render_category_icons(); ?>
					<?php endif; ?>
					<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ?: get_the_content() ), 24 ) ); ?></p>
					<a class="jc-showcase__link jc-btn jc-btn--secondary jc-btn--sm" href="<?php the_permalink(); ?>">OPEN BUILD</a>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<?php else : ?>
		<p class="jc-showcase__empty">Client entries will appear here automatically from the Client post type.</p>
		<?php endif; ?>

		<div class="jc-showcase__actions">
			<a class="jc-btn jc-btn--primary" href="<?php echo esc_url( $clients_url ); ?>">BROWSE ALL CLIENTS</a>
		</div>
	</section>

	<?php
	$references_query = new WP_Query(
		array(
			'post_type'      => 'jc_reference',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);

	$reference_count = $references_query->have_posts() ? (int) $references_query->post_count : 0;
	?>

	<?php if ( $reference_count > 0 ) : ?>
	<section class="jc-references-section" aria-label="Colleague references">
	<div class="jc-references jc-references__faces" aria-label="Colleague references — click a portrait to read">
		<h2 class="jc-section-title jc-references__title">REFERENCE PACK: <span class="jc-references__count"><?php echo esc_html( $reference_count ); ?></span> HEROES</h2>

		<?php
		while ( $references_query->have_posts() ) :
			$references_query->the_post();

				$reference_id     = get_the_ID();
				$reference_name   = (string) get_post_meta( $reference_id, '_jc_reference_name', true );
				$reference_title  = (string) get_post_meta( $reference_id, '_jc_reference_title', true );
				$reference_quote  = (string) get_post_meta( $reference_id, '_jc_reference_quote', true );
				$linkedin_url     = (string) get_post_meta( $reference_id, '_jc_reference_linkedin_url', true );
				$avatar_arcade_url = (string) get_post_meta( $reference_id, '_jc_reference_avatar_arcade_url', true );
				$avatar_stripes_url = (string) get_post_meta( $reference_id, '_jc_reference_avatar_stripes_url', true );
				$reference_bg     = (string) get_post_meta( $reference_id, '_jc_reference_avatar_bg', true );

				if ( '' === $reference_name ) {
					$reference_name = get_the_title( $reference_id );
				}

				if ( '' === $reference_quote ) {
					$reference_quote = (string) get_the_excerpt( $reference_id );
				}

				if ( '' === $avatar_arcade_url && '' !== $avatar_stripes_url ) {
					$avatar_arcade_url = $avatar_stripes_url;
				}

				if ( '' === $avatar_stripes_url && '' !== $avatar_arcade_url ) {
					$avatar_stripes_url = $avatar_arcade_url;
				}

				if ( '' === $avatar_arcade_url || '' === $avatar_stripes_url ) {
					$featured_avatar = get_the_post_thumbnail_url( $reference_id, 'medium' );

					if ( $featured_avatar ) {
						if ( '' === $avatar_arcade_url ) {
							$avatar_arcade_url = $featured_avatar;
						}

						if ( '' === $avatar_stripes_url ) {
							$avatar_stripes_url = $featured_avatar;
						}
					}
				}

				if ( '' === $avatar_arcade_url ) {
					$avatar_arcade_url = get_theme_file_uri( 'assets/img/person-1.svg' );
				}

				if ( '' === $avatar_stripes_url ) {
					$avatar_stripes_url = get_theme_file_uri( 'assets/img/person-1.svg' );
				}

				$bub_id = 'ref-bubble-' . $reference_id;
				?>
		<figure
			class="jc-reference"
			<?php if ( '' !== $reference_bg ) : ?>style="--jc-reference-bg: <?php echo esc_attr( $reference_bg ); ?>;"<?php endif; ?>
			tabindex="0"
			role="button"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $bub_id ); ?>"
			aria-label="<?php echo esc_attr( $reference_name ) . ' — click to read reference'; ?>"
		>
			<div class="jc-reference__swap" aria-hidden="true">
				<img class="jc-reference__avatar jc-reference__avatar--arcade" src="<?php echo esc_url( $avatar_arcade_url ); ?>" alt="" loading="lazy" decoding="async" />
				<img class="jc-reference__avatar jc-reference__avatar--stripes" src="<?php echo esc_url( $avatar_stripes_url ); ?>" alt="" loading="lazy" decoding="async" />
			</div>
			<figcaption><?php echo esc_html( $reference_name ); ?></figcaption>

			<div class="jc-reference__bubble" id="<?php echo esc_attr( $bub_id ); ?>" aria-hidden="true">
				<div class="jc-reference__bubble-inner">
					<button class="jc-reference__close" type="button" aria-label="Close reference">&#x2715;</button>
					<blockquote class="jc-reference__quote">
						<p><?php echo esc_html( $reference_quote ); ?></p>
					</blockquote>
					<footer class="jc-reference__footer">
						<strong class="jc-reference__name"><?php echo esc_html( $reference_name ); ?></strong>
						<span class="jc-reference__title"><?php echo esc_html( $reference_title ); ?></span>
						<?php if ( '' !== $linkedin_url ) : ?>
							<a class="jc-reference__link jc-btn jc-btn--secondary jc-btn--sm" href="<?php echo esc_url( $linkedin_url ); ?>" target="_blank" rel="noopener noreferrer">
								View on LinkedIn
								<svg viewBox="0 0 12 12" aria-hidden="true" focusable="false"><path d="M2 10h8V7h1v4H1V1h4v1H2z" fill="currentColor"/><path d="M6 1h5v5H9.5V3.9L6 7.4 4.6 6 8.1 2.5H6z" fill="currentColor"/></svg>
							</a>
						<?php endif; ?>
					</footer>
				</div>
			</div>
		</figure>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
	<?php endif; ?>

<div class="jc-layout-grid">
	<section class="jc-panel">
		<?php
		$credential_icons = array(
			'skill' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2.5 14.9 9.1 22 12l-7.1 2.9L12 22l-2.9-7.1L2 12l7.1-2.9Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 7.2v9.6M7.2 12h9.6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
			'work'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M7 6.5V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4.5 8.5h15A1.5 1.5 0 0 1 21 10v7.5A2.5 2.5 0 0 1 18.5 20h-13A2.5 2.5 0 0 1 3 17.5V10a1.5 1.5 0 0 1 1.5-1.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 8.5h6v2H9z" fill="currentColor"/></svg>',
			'edu'   => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 9.6 12 5l9 4.6-9 4.6z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M7.2 12.2V16c0 1.7 2.2 3 4.8 3s4.8-1.3 4.8-3v-3.8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 9.8v5.2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
			'cert'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2.5 14.7 7l5.3.8-3.9 3.8.9 5.3-4.8-2.5-4.8 2.5.9-5.3L4.4 7l5.3-.8Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9.5 14.9v6l2.5-1.7 2.5 1.7v-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		);

		$credential_sections = array(
			array(
				'title' => 'SKILLS',
				'lead'  => 'Core capabilities and adjacent tools I use to ship clean, maintainable work.',
				'icon'  => 'skill',
				'items' => array(
					array(
						'label' => 'WordPress Development',
						'meta'  => 'Custom blocks, themes, and scalable sites',
					),
					array(
						'label' => 'Custom Blocks & Themes',
						'meta'  => 'Reusable systems with editor-friendly patterns',
					),
					array(
						'label' => 'PHP, CSS, JavaScript, React',
						'meta'  => 'Full front-end to back-end implementation',
					),
					array(
						'label' => 'Git, Webpack, PHPUnit',
						'meta'  => 'Shipping, bundling, and test coverage workflows',
					),
					array(
						'label' => 'Full Stack Development',
						'meta'  => 'Delivery across design, data, and deployment',
					),
					array(
						'label' => 'Agentic Engineer',
						'meta'  => 'Tool-assisted workflows with human review',
					),
					array(
						'label' => 'Design Thinking',
						'meta'  => 'Systems-first problem solving for product work',
					),
					array(
						'label' => 'Figma & Adobe Creative Suite',
						'meta'  => 'Visual design, prototyping, and handoff',
					),
					array(
						'label' => 'Project Lifecycle Management',
						'meta'  => 'Planning through launch and iteration',
					),
				),
			),
			array(
				'title' => 'WORK EXPERIENCE',
				'lead'  => 'Over a decade of building digital experiences across agencies, product teams, and internal orgs.',
				'icon'  => 'work',
				'items' => array(
					array(
						'label'   => 'Senior Applications Developer',
						'meta'    => 'Janus Henderson Investors - 2021 - Present',
						'details' => array(
							'Developed and scaled custom multi-site WordPress solutions supporting global operations and cross-regional content distribution.',
							'API Connectivity',
							'Website and UI Design',
						),
					),
					array(
						'label'   => 'Web Developer',
						'meta'    => 'Culture Foundry - 2011 - 2021',
						'details' => array(
							'Multi Client and Multi Tech Stack Development',
							'CMS Developer (WordPress, Drupal, Modx, Craft)',
							'Website and UI Design',
						),
					),
					array(
						'label'   => 'Designer | Frontend Developer',
						'meta'    => 'Claremont Information Systems - 2010 - 2011',
						'details' => array(
							'Ruby on Rails Website Development',
							'Frontend Website Development (HAML, SASS)',
							'Website and UI Design',
						),
					),
				),
			),
			array(
				'title' => 'EDUCATION',
				'lead'  => 'Academic foundation that shaped my design and systems thinking approach.',
				'icon'  => 'edu',
				'items' => array(
					array(
						'label'   => 'Bachelor of Fine Arts',
						'meta'    => 'Colorado State University',
						'details' => array(
							'1996 - 2000',
						),
					),
					array(
						'label'   => 'Communication Design',
						'meta'    => 'Metropoliten State University',
						'details' => array(
							'2004 - 2006',
						),
					),
				),
			),
			array(
				'title' => 'CERTIFICATIONS',
				'lead'  => 'Focused credentials that support the technical and product work above.',
				'icon'  => 'cert',
				'items' => array(
					array(
						'label' => 'Full Stack Development',
						'meta'  => 'University of Denver',
					),
					array(
						'label' => 'JavaScript Circuit',
						'meta'  => 'General Assembly',
					),
					array(
						'label' => 'User Experience Design',
						'meta'  => 'General Assembly',
					),
				),
			),
		);
		?>
		<h2 class="jc-section-title">SKILLS, EXPERIENCE, EDUCATION &amp; CERTIFICATIONS</h2>
		<p class="jc-credentials__lead">A quick scan of the stack, the roles behind it, and the credentials that keep the work sharp.</p>
		<div class="jc-card-list jc-credentials__grid">
			<?php foreach ( $credential_sections as $section ) : ?>
				<article class="jc-card jc-credentials__card">
					<h3 class="jc-credentials__card-title"><?php echo esc_html( $section['title'] ); ?></h3>
					<p class="jc-credentials__card-lead"><?php echo esc_html( $section['lead'] ); ?></p>
					<ul class="jc-credentials__list">
						<?php foreach ( $section['items'] as $item ) : ?>
							<li class="jc-credentials__item">
								<span class="jc-credentials__icon" aria-hidden="true"><?php echo $credential_icons[ $section['icon'] ]; ?></span>
								<div class="jc-credentials__content">
									<h4 class="jc-credentials__item-title"><?php echo esc_html( $item['label'] ); ?></h4>
									<?php if ( ! empty( $item['meta'] ) ) : ?>
										<p class="jc-credentials__item-meta"><?php echo esc_html( $item['meta'] ); ?></p>
									<?php endif; ?>
									<?php if ( ! empty( $item['details'] ) ) : ?>
										<ul class="jc-credentials__item-list">
											<?php foreach ( $item['details'] as $detail ) : ?>
												<li><?php echo esc_html( $detail ); ?></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="jc-panel">
		<h2 class="jc-section-title">JOSH's NOTES</h2>
		<div class="jc-card-list">
			<?php
			$recent_posts = new WP_Query(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 3,
				)
			);

			if ( $recent_posts->have_posts() ) :
				while ( $recent_posts->have_posts() ) :
					$recent_posts->the_post();
					?>
					<article <?php post_class( 'jc-card' ); ?>>
						<?php jc_16bit_arcade_render_post_card_image(); ?>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="jc-meta"><?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?></p>
						<?php jc_16bit_arcade_render_category_icons(); ?>
						<?php the_excerpt(); ?>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			else :
				?>
				<p>No quest logs yet. Time to write your origin story.</p>
				<?php
			endif;
			?>
		</div>
	</section>
</div>
<?php
get_footer();
