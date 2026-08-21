<?php
/**
 * Front page template.
 *
 * @package jc_16bit_arcade
 */

get_header();

$linkedin_url = apply_filters( 'jc_16bit_arcade_linkedin_url', 'https://www.linkedin.com/' );

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
			<a class="jc-btn jc-btn--secondary" href="<?php echo esc_url( $projects_url ); ?>">VIEW FEATURED CLIENTS</a>
	</div>
	<div class="jc-humor-box">
		<strong>NPC Tip:</strong>
		<?php echo esc_html( jc_16bit_arcade_humor_line() ); ?>
	</div>

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
	/*
	 * REFERENCES SECTION
	 * Replace the quote, name, title, company, and linkedin_url for each entry below.
	 */
	$references = array(
		array(
			'name'         => 'Aaron Longwell',
			'title'        => 'Chief Technology Officer · Culture Foundry',
			'quote'        => 'Josh was exceptional at Claremont. Although he was only tasked with design maintenance of our corporate website, he quickly learned HAML, SASS, and Ruby on Rails to allow our development team to utilize his talents in all of our applications. He\'s a quick learner, has a top-notch work ethic, and pays attention to detail. It\'d be a pleasure to work with Josh again.',
			'linkedin_url' => 'https://www.linkedin.com/in/aaronlongwell/',
			'avatar_file'  => 'assets/img/person-1.svg',
			'avatar_bg'    => '#c6db9d',
		),
		array(
			'name'         => 'Stacey Rice',
			'title'        => 'UX/UI Designer and Developer',
			'quote'        => 'Josh has an impeccable eye for design, and his style is clean and modern, with great attention to detail. Along with his excellent design ability, Josh is a welcome teammate and is always willing to help, whether it be perfecting a design, teaching co-workers new techniques, or helping to improve department processes. Josh always goes above and beyond to give clients their perfect website and always puts in the extra time to do so.',
			'linkedin_url' => 'https://www.linkedin.com/in/staceycrice/',
			'avatar_file'  => 'assets/img/person-2.svg',
			'avatar_bg'    => '#d1e2aa',
		),
		array(
			'name'         => 'Erica Pidor',
			'title'        => 'Content & Social Media Expert',
			'quote'        => 'Josh is an incredibly talented designer – working with Josh, I always knew his designs would be high-quality and that his finished product would be flawless. He is always conscious of the usability of a website, as well as the look and brand. Josh was a pleasure to work with!',
			'linkedin_url' => 'https://www.linkedin.com/in/erica-pidor/',
			'avatar_file'  => 'assets/img/person-5.svg',
			'avatar_bg'    => '#f58fbc',
		),
		array(
			'name'         => 'Tammy Smith',
			'title'        => 'SEO Manager · Digible, Inc',
			'quote'        => 'Josh is an extremely dedicated and talented web designer and programmer. I enjoyed working with him at Page 1 immensely. Josh is loyal and hard working, with an excellent eye for detail. He is always kind and helpful and will go out of his way to help a co-worker. There are several times when he jumped in to help on projects that needed working on. He strives for perfection in everything he does. I would highly recommend Josh Coast for any position, he is an asset for any company he works for.',
			'linkedin_url' => 'https://www.linkedin.com/in/tammymsmith/',
			'avatar_file'  => 'assets/img/person-4.svg',
			'avatar_bg'    => '#5d3ab5',
		),
	);
	?>
	<div class="jc-references jc-references__faces" aria-label="Colleague references — click a portrait to read">
		<p class="jc-eyebrow">REFERENCE PACK: 4 HEROES</p>

		<?php
		foreach ( $references as $i => $ref ) :
			$avatar_url = get_theme_file_uri( $ref['avatar_file'] );
			$bub_id     = 'ref-bubble-' . ( $i + 1 );
			?>
		<figure
			class="jc-reference"
				style="--jc-reference-bg: <?php echo esc_attr( $ref['avatar_bg'] ); ?>;"
			tabindex="0"
			role="button"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $bub_id ); ?>"
			aria-label="<?php echo esc_attr( $ref['name'] ) . ' — click to read reference'; ?>"
		>
			<div class="jc-reference__swap" aria-hidden="true">
				<img class="jc-reference__avatar" src="<?php echo esc_url( $avatar_url ); ?>" alt="" loading="lazy" decoding="async" />
			</div>
			<figcaption><?php echo esc_html( $ref['name'] ); ?></figcaption>

			<div class="jc-reference__bubble" id="<?php echo esc_attr( $bub_id ); ?>" aria-hidden="true">
				<div class="jc-reference__bubble-inner">
					<button class="jc-reference__close" type="button" aria-label="Close reference">&#x2715;</button>
					<blockquote class="jc-reference__quote">
						<p><?php echo esc_html( $ref['quote'] ); ?></p>
					</blockquote>
					<footer class="jc-reference__footer">
						<strong class="jc-reference__name"><?php echo esc_html( $ref['name'] ); ?></strong>
						<span class="jc-reference__title"><?php echo esc_html( $ref['title'] ); ?></span>
						<a class="jc-reference__link jc-btn jc-btn--secondary jc-btn--sm" href="<?php echo esc_url( $ref['linkedin_url'] ); ?>" target="_blank" rel="noopener noreferrer">
							View on LinkedIn
							<svg viewBox="0 0 12 12" aria-hidden="true" focusable="false"><path d="M2 10h8V7h1v4H1V1h4v1H2z" fill="currentColor"/><path d="M6 1h5v5H9.5V3.9L6 7.4 4.6 6 8.1 2.5H6z" fill="currentColor"/></svg>
						</a>
					</footer>
				</div>
			</div>
		</figure>
		<?php endforeach; ?>
	</div>
</section>

<div class="jc-layout-grid">
	<section class="jc-panel">
		<h2 class="jc-section-title">DEV JOURNAL</h2>
		<div class="jc-card-list">
			<?php
			$recent_posts = new WP_Query(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 5,
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

	<section class="jc-panel">
		<h2 class="jc-section-title">SUPPORTING PAGES</h2>
		<div class="jc-card-list">
			<?php
			$recent_pages = new WP_Query(
				array(
					'post_type'      => 'page',
					'posts_per_page' => 5,
					'post__not_in'   => array( get_queried_object_id() ),
					'orderby'        => 'menu_order title',
					'order'          => 'ASC',
				)
			);

			if ( $recent_pages->have_posts() ) :
				while ( $recent_pages->have_posts() ) :
					$recent_pages->the_post();
					?>
					<article <?php post_class( 'jc-card' ); ?>>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php if ( has_excerpt() ) : ?>
							<?php the_excerpt(); ?>
						<?php else : ?>
							<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_content() ), 20 ) ); ?></p>
						<?php endif; ?>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			else :
				?>
				<p>Your side quests will appear here automatically.</p>
				<?php
			endif;
			?>
		</div>
	</section>
</div>
<?php
get_footer();
