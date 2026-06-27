<?php
/**
 * Front page template.
 *
 * @package jc_16bit_arcade
 */

get_header();

$post_count = jc_16bit_arcade_post_count( 'post' );
$page_count = jc_16bit_arcade_post_count( 'page' );
$project_count = post_type_exists( 'project' ) ? jc_16bit_arcade_post_count( 'project' ) : 0;
$linkedin_url = apply_filters( 'jc_16bit_arcade_linkedin_url', 'https://www.linkedin.com/' );
?>
<section class="arcade-panel hero">
	<h1 class="hud-title"><button id="player-one-trigger" class="hud-firework-trigger" type="button">PLAYER ONE: <?php bloginfo( 'name' ); ?></button></h1>
	<p class="hud-sub"><?php bloginfo( 'description' ); ?></p>
	<div class="stat-grid" role="list" aria-label="Portfolio stats">
		<div class="stat" role="listitem">
			<span class="stat-label">POSTS UNLOCKED</span>
			<span class="stat-value"><?php echo esc_html( $post_count ); ?></span>
		</div>
		<div class="stat" role="listitem">
			<span class="stat-label">PAGES CLEARED</span>
			<span class="stat-value"><?php echo esc_html( $page_count ); ?></span>
		</div>
		<div class="stat" role="listitem">
			<span class="stat-label">PROJECT BOSSES</span>
			<span class="stat-value"><?php echo esc_html( $project_count ); ?></span>
		</div>
	</div>
	<div class="cta-row">
		<a class="btn-arcade btn-primary" href="<?php echo esc_url( $linkedin_url ); ?>" target="_blank" rel="noopener noreferrer">START CONVERSATION <span class="external-site-icon" aria-hidden="true"><svg viewBox="0 0 14 14" focusable="false"><path d="M3 11h8V7h2v6H1V1h6v2H3z" fill="currentColor"/><path d="M8 1h5v5h-2V4.4L6.7 8.7 5.3 7.3 9.6 3H8z" fill="currentColor"/></svg></span><span class="screen-reader-text"> Opens LinkedIn in a new tab</span></a>
		<a class="btn-arcade btn-secondary" href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/' ) ); ?>">VIEW ALL JOURNALS</a>
	</div>
	<div class="humor-box">
		<strong>NPC Tip:</strong>
		<?php echo esc_html( jc_16bit_arcade_humor_line() ); ?>
	</div>

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
			'avatar'       => 'beard-byte',
		),
		array(
			'name'         => 'Stacey Rice',
			'title'        => 'UX/UI Designer and Developer',
			'quote'        => 'Josh has an impeccable eye for design, and his style is clean and modern, with great attention to detail. Along with his excellent design ability, Josh is a welcome teammate and is always willing to help, whether it be perfecting a design, teaching co-workers new techniques, or helping to improve department processes. Josh always goes above and beyond to give clients their perfect website and always puts in the extra time to do so.',
			'linkedin_url' => 'https://www.linkedin.com/in/staceycrice/',
			'avatar'       => 'clip-commit',
		),
		array(
			'name'         => 'Erica Pidor',
			'title'        => 'Content & Social Media Expert',
			'quote'        => 'Josh is an incredibly talented designer – working with Josh, I always knew his designs would be high-quality and that his finished product would be flawless. He is always conscious of the usability of a website, as well as the look and brand. Josh was a pleasure to work with!',
			'linkedin_url' => 'https://www.linkedin.com/in/erica-pidor/',
			'avatar'       => 'mohawk-merge',
		),
		array(
			'name'         => 'Tammy Smith',
			'title'        => 'SEO Manager · Digible, Inc',
			'quote'        => 'Josh is an extremely dedicated and talented web designer and programmer. I enjoyed working with him at Page 1 immensely. Josh is loyal and hard working, with an excellent eye for detail. He is always kind and helpful and will go out of his way to help a co-worker. There are several times when he jumped in to help on projects that needed working on. He strives for perfection in everything he does. I would highly recommend Josh Coast for any position, he is an asset for any company he works for.',
			'linkedin_url' => 'https://www.linkedin.com/in/tammymsmith/',
			'avatar'       => 'neon-dot',
		),
	);

	$avatar_svgs = array(
		'beard-byte' => array(
			'neutral' => '<rect width="96" height="96" fill="#d9e9bf"/><rect x="26" y="12" width="42" height="20" fill="#181a21"/><rect x="30" y="26" width="34" height="28" fill="#f1e8db"/><rect x="28" y="34" width="15" height="9" fill="#1c202c"/><rect x="49" y="34" width="15" height="9" fill="#1c202c"/><rect x="43" y="37" width="6" height="3" fill="#1c202c"/><rect x="31" y="37" width="5" height="3" fill="#a6ebff"/><rect x="52" y="37" width="5" height="3" fill="#a6ebff"/><rect x="48" y="44" width="8" height="2" fill="#8f6d56"/><rect x="42" y="46" width="24" height="16" fill="#25232d"/><rect x="24" y="62" width="48" height="20" fill="#c6db9d"/>',
			'hover'   => '<rect width="96" height="96" fill="#d9e9bf"/><rect x="26" y="12" width="42" height="20" fill="#181a21"/><rect x="30" y="26" width="34" height="28" fill="#f1e8db"/><rect x="28" y="34" width="15" height="9" fill="#1c202c"/><rect x="49" y="34" width="15" height="9" fill="#1c202c"/><rect x="43" y="37" width="6" height="3" fill="#1c202c"/><rect x="33" y="37" width="5" height="3" fill="#a6ebff"/><rect x="54" y="37" width="5" height="3" fill="#a6ebff"/><rect x="45" y="44" width="11" height="3" fill="#e95d6a"/><rect x="42" y="46" width="24" height="16" fill="#25232d"/><rect x="24" y="62" width="48" height="20" fill="#c6db9d"/>',
		),
		'clip-commit' => array(
			'neutral' => '<rect width="96" height="96" fill="#d9e9bf"/><rect x="24" y="14" width="48" height="38" fill="#1e222f"/><rect x="26" y="26" width="44" height="30" fill="#eaded1"/><rect x="23" y="24" width="6" height="12" fill="#f06aad"/><rect x="34" y="38" width="7" height="3" fill="#181b24"/><rect x="54" y="38" width="7" height="3" fill="#181b24"/><rect x="42" y="45" width="8" height="2" fill="#7d5a4b"/><rect x="30" y="46" width="6" height="3" fill="#f79cc0"/><rect x="58" y="46" width="6" height="3" fill="#f79cc0"/><rect x="24" y="58" width="48" height="22" fill="#d1e2aa"/>',
			'hover'   => '<rect width="96" height="96" fill="#d9e9bf"/><rect x="24" y="14" width="48" height="38" fill="#1e222f"/><rect x="26" y="26" width="44" height="30" fill="#eaded1"/><rect x="23" y="24" width="6" height="12" fill="#f06aad"/><rect x="34" y="38" width="7" height="3" fill="#181b24"/><rect x="54" y="38" width="7" height="3" fill="#181b24"/><rect x="40" y="45" width="11" height="3" fill="#e75d6a"/><rect x="30" y="46" width="6" height="3" fill="#f79cc0"/><rect x="58" y="46" width="6" height="3" fill="#f79cc0"/><rect x="24" y="58" width="48" height="22" fill="#d1e2aa"/>',
		),
		'mohawk-merge' => array(
			'neutral' => '<rect width="96" height="96" fill="#eddba7"/><rect x="32" y="12" width="32" height="16" fill="#ef5ea9"/><rect x="28" y="28" width="40" height="30" fill="#f2e4d6"/><rect x="28" y="36" width="14" height="9" fill="#1c202b"/><rect x="54" y="36" width="14" height="9" fill="#1c202b"/><rect x="42" y="39" width="12" height="3" fill="#1c202b"/><rect x="32" y="39" width="4" height="3" fill="#a7ebff"/><rect x="58" y="39" width="4" height="3" fill="#a7ebff"/><rect x="42" y="47" width="11" height="3" fill="#db6178"/><rect x="24" y="58" width="48" height="22" fill="#f58fbc"/>',
			'hover'   => '<rect width="96" height="96" fill="#eddba7"/><rect x="32" y="12" width="32" height="16" fill="#ef5ea9"/><rect x="28" y="28" width="40" height="30" fill="#f2e4d6"/><rect x="28" y="36" width="14" height="9" fill="#1c202b"/><rect x="54" y="36" width="14" height="9" fill="#1c202b"/><rect x="42" y="39" width="12" height="3" fill="#1c202b"/><rect x="34" y="39" width="4" height="3" fill="#a7ebff"/><rect x="60" y="39" width="4" height="3" fill="#a7ebff"/><rect x="40" y="47" width="14" height="4" fill="#ec6074"/><rect x="24" y="58" width="48" height="22" fill="#f58fbc"/>',
		),
		'neon-dot' => array(
			'neutral' => '<rect width="96" height="96" fill="#eddba7"/><rect x="22" y="12" width="52" height="40" fill="#f089ca"/><rect x="30" y="28" width="36" height="30" fill="#6d3d32"/><rect x="35" y="38" width="7" height="3" fill="#121623"/><rect x="54" y="38" width="7" height="3" fill="#121623"/><rect x="42" y="45" width="9" height="3" fill="#0e111c"/><rect x="30" y="46" width="5" height="3" fill="#e96ca5"/><rect x="60" y="46" width="5" height="3" fill="#e96ca5"/><rect x="24" y="58" width="48" height="22" fill="#5d3ab5"/>',
			'hover'   => '<rect width="96" height="96" fill="#eddba7"/><rect x="22" y="12" width="52" height="40" fill="#f089ca"/><rect x="30" y="28" width="36" height="30" fill="#6d3d32"/><rect x="37" y="38" width="7" height="3" fill="#121623"/><rect x="56" y="38" width="7" height="3" fill="#121623"/><rect x="40" y="45" width="12" height="3" fill="#db6075"/><rect x="30" y="46" width="5" height="3" fill="#e96ca5"/><rect x="60" y="46" width="5" height="3" fill="#e96ca5"/><rect x="24" y="58" width="48" height="22" fill="#5d3ab5"/>',
		),
		'pixel-beard-2' => array(
			'neutral' => '<rect width="96" height="96" fill="#d9e9bf"/><rect x="26" y="12" width="42" height="20" fill="#181a21"/><rect x="30" y="26" width="34" height="28" fill="#f1e8db"/><rect x="28" y="34" width="15" height="9" fill="#1c202c"/><rect x="49" y="34" width="15" height="9" fill="#1c202c"/><rect x="43" y="37" width="6" height="3" fill="#1c202c"/><rect x="31" y="37" width="5" height="3" fill="#a6ebff"/><rect x="52" y="37" width="5" height="3" fill="#a6ebff"/><rect x="47" y="44" width="8" height="2" fill="#8f6d56"/><rect x="40" y="46" width="26" height="16" fill="#25232d"/><rect x="24" y="62" width="48" height="20" fill="#c6db9d"/>',
			'hover'   => '<rect width="96" height="96" fill="#d9e9bf"/><rect x="26" y="12" width="42" height="20" fill="#181a21"/><rect x="30" y="26" width="34" height="28" fill="#f1e8db"/><rect x="28" y="34" width="15" height="9" fill="#1c202c"/><rect x="49" y="34" width="15" height="9" fill="#1c202c"/><rect x="43" y="37" width="6" height="3" fill="#1c202c"/><rect x="33" y="37" width="5" height="3" fill="#a6ebff"/><rect x="54" y="37" width="5" height="3" fill="#a6ebff"/><rect x="44" y="44" width="11" height="3" fill="#e95d6a"/><rect x="40" y="46" width="26" height="16" fill="#25232d"/><rect x="24" y="62" width="48" height="20" fill="#c6db9d"/>',
		),
	);
	?>
	<div class="dev-heroes dev-faces" aria-label="Colleague references — click a portrait to read">
		<p class="sprite-mode">REFERENCE PACK: 4 HEROES</p>

		<?php foreach ( $references as $i => $ref ) :
			$svgs   = $avatar_svgs[ $ref['avatar'] ];
			$bub_id = 'ref-bubble-' . ( $i + 1 );
		?>
		<figure
			class="face-card"
			tabindex="0"
			role="button"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $bub_id ); ?>"
			aria-label="<?php echo esc_attr( $ref['name'] ) . ' — click to read reference'; ?>"
		>
			<div class="face-swap" aria-hidden="true">
				<svg class="face-svg face-neutral" viewBox="0 0 96 96" xmlns="http://www.w3.org/2000/svg" focusable="false">
					<?php echo $svgs['neutral']; ?>
				</svg>
				<svg class="face-svg face-hover" viewBox="0 0 96 96" xmlns="http://www.w3.org/2000/svg" focusable="false">
					<?php echo $svgs['hover']; ?>
				</svg>
			</div>
			<figcaption><?php echo esc_html( $ref['name'] ); ?></figcaption>

			<div class="ref-bubble" id="<?php echo esc_attr( $bub_id ); ?>" aria-hidden="true">
				<div class="ref-bubble-inner">
					<button class="ref-bubble-close" type="button" aria-label="Close reference">&#x2715;</button>
					<blockquote class="ref-quote">
						<p><?php echo esc_html( $ref['quote'] ); ?></p>
					</blockquote>
					<footer class="ref-footer">
						<strong class="ref-name"><?php echo esc_html( $ref['name'] ); ?></strong>
						<span class="ref-title"><?php echo esc_html( $ref['title'] ); ?></span>
						<a class="ref-linkedin-link" href="<?php echo esc_url( $ref['linkedin_url'] ); ?>" target="_blank" rel="noopener noreferrer">
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

<div class="content-grid">
	<section class="arcade-panel">
		<h2 class="section-title">LATEST QUEST LOGS</h2>
		<div class="card-list">
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
					<article <?php post_class( 'card-item' ); ?>>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="meta"><?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?></p>
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

	<section class="arcade-panel">
		<h2 class="section-title">SIDE QUESTS (PAGES)</h2>
		<div class="card-list">
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
					<article <?php post_class( 'card-item' ); ?>>
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
