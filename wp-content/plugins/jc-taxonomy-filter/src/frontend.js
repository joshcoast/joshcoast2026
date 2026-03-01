/**
 * Frontend AJAX filtering functionality
 *
 * This script handles clicking on filter buttons and updating the post grid
 * Works with GenerateBlocks Query Loop or any container with posts
 */

(function () {
	"use strict";

	const init = () => {
		const filters = document.querySelectorAll(".jc-taxonomy-filter");

		filters.forEach((filter) => {
			const buttons = filter.querySelectorAll(
				".jc-taxonomy-filter__button"
			);
			const taxonomy = filter.dataset.taxonomy;
			const queryId = filter.dataset.queryId;

			buttons.forEach((button) => {
				button.addEventListener("click", (e) => {
					e.preventDefault();
					handleFilterClick(button, filter, taxonomy, queryId);
				});
			});
		});
	};

	const handleFilterClick = async (
		button,
		filterContainer,
		taxonomy,
		queryId
	) => {
		const termSlug = button.dataset.termSlug;
		const termId = button.dataset.termId;

		// Update active state
		filterContainer
			.querySelectorAll(".jc-taxonomy-filter__button")
			.forEach((btn) => {
				btn.classList.remove("is-active");
			});
		button.classList.add("is-active");

		// Reset page to 1 when changing filter
		filterContainer.dataset.currentPage = "1";
		filterContainer.dataset.currentTerm = termSlug;

		// Find the target container
		let targetContainer = null;

		if (queryId) {
			targetContainer = document.querySelector(queryId);
		} else {
			// Try common selectors for post containers
			const selectors = [
				".gb-query-loop-wrapper",
				".wp-block-post-template",
				".posts-container",
				".blog-posts",
				"main .gb-grid-wrapper",
			];

			for (const selector of selectors) {
				targetContainer = document.querySelector(selector);
				if (targetContainer) break;
			}
		}

		if (!targetContainer) {
			// Fallback: Update URL and reload
			updateUrlAndReload(taxonomy, termSlug);
			return;
		}

		// Show loading state
		targetContainer.classList.add("jc-filter-loading");
		filterContainer.classList.add("jc-filter-loading");

		try {
			// Fetch filtered posts
			const posts = await fetchPosts(
				taxonomy,
				termSlug,
				1,
				filterContainer.dataset.perPage || 10
			);

			// Render posts
			renderPosts(targetContainer, posts);

			// Render pagination
			renderPagination(filterContainer, posts);

			// Update URL without reload
			updateUrl(taxonomy, termSlug);
		} catch (error) {
			console.error("JC Taxonomy Filter: Error fetching posts", error);
			// Fallback to page reload
			updateUrlAndReload(taxonomy, termSlug);
		} finally {
			targetContainer.classList.remove("jc-filter-loading");
			filterContainer.classList.remove("jc-filter-loading");
		}
	};

	const fetchPosts = async (taxonomy, termSlug, page = 1, perPage = 10) => {
		const params = new URLSearchParams({
			taxonomy: taxonomy,
			term: termSlug || "",
			page: page,
			per_page: perPage,
		});

		const response = await fetch(
			`/wp-json/jc-taxonomy-filter/v1/posts?${params}`
		);

		if (!response.ok) {
			throw new Error("Failed to fetch posts");
		}

		return response.json();
	};

	const renderPosts = (container, data) => {
		const { posts } = data;

		if (posts.length === 0) {
			container.innerHTML = `
                <div class="jc-filter-no-results">
                    <p>No posts found.</p>
                </div>
            `;
			return;
		}

		// Check if we're dealing with GenerateBlocks grid
		const isGbGrid =
			container.classList.contains("gb-grid-wrapper") ||
			container.classList.contains("gb-query-loop-wrapper");

		if (isGbGrid) {
			renderGbGridPosts(container, posts);
		} else {
			renderDefaultPosts(container, posts);
		}
	};

	const renderGbGridPosts = (container, posts) => {
		// Find the template from existing items
		const existingItem = container.querySelector(
			".gb-query-loop-item, .gb-grid-column"
		);
		let template = null;

		if (existingItem) {
			template = existingItem.cloneNode(true);
		}

		// Clear container
		container.innerHTML = "";

		posts.forEach((post) => {
			let item;

			if (template) {
				item = template.cloneNode(true);

				// Update post ID in class
				item.classList.forEach((className) => {
					if (className.startsWith("post-")) {
						item.classList.remove(className);
					}
				});
				item.classList.add("post-" + post.id);

				// Update content
				const titleEl = item.querySelector(
					".gb-headline a, .entry-title a, h2 a, h3 a"
				);
				if (titleEl) {
					titleEl.textContent = post.title;
					titleEl.href = post.link;
				}

				const excerptEl = item.querySelector(
					".gb-container div.gb-headline-text"
				);
				if (excerptEl) {
					excerptEl.innerHTML = post.excerpt;
				}

				const imageEl = item.querySelector("img");
				if (imageEl && post.thumbnail) {
					imageEl.outerHTML = post.thumbnail;
				}

				const linkEls = item.querySelectorAll("a[href]");
				linkEls.forEach((link) => {
					if (
						link.href.includes("/post") ||
						link.getAttribute("href").startsWith("/")
					) {
						link.href = post.link;
					}
				});
			} else {
				// Create basic card
				item = createPostCard(post);
			}

			container.appendChild(item);
		});
	};

	const renderDefaultPosts = (container, posts) => {
		container.innerHTML = "";

		posts.forEach((post) => {
			container.appendChild(createPostCard(post));
		});
	};

	const renderPagination = (filterContainer, data) => {
		const paginationEl = filterContainer.querySelector(".jc-pagination");
		if (!paginationEl) return;

		const { pages, current_page, total } = data;

		if (pages > 1) {
			paginationEl.style.display = "block";
			paginationEl.querySelector(
				".jc-pagination__info"
			).textContent = `Page ${current_page} of ${pages}`;

			const prevBtn = paginationEl.querySelector(".jc-pagination__prev");
			const nextBtn = paginationEl.querySelector(".jc-pagination__next");

			prevBtn.disabled = current_page <= 1;
			nextBtn.disabled = current_page >= pages;

			// Remove existing listeners
			prevBtn.onclick = null;
			nextBtn.onclick = null;

			// Add new listeners
			prevBtn.onclick = () =>
				handlePaginationClick(filterContainer, current_page - 1);
			nextBtn.onclick = () =>
				handlePaginationClick(filterContainer, current_page + 1);
		} else {
			paginationEl.style.display = "none";
		}
	};

	const handlePaginationClick = async (filterContainer, page) => {
		const taxonomy = filterContainer.dataset.taxonomy;
		const termSlug = filterContainer.dataset.currentTerm || "";
		const perPage = filterContainer.dataset.perPage || 10;

		// Update current page
		filterContainer.dataset.currentPage = page;

		// Find target container
		let targetContainer = null;
		const queryId = filterContainer.dataset.queryId;

		if (queryId) {
			targetContainer = document.querySelector(queryId);
		} else {
			const selectors = [
				".gb-query-loop-wrapper",
				".wp-block-post-template",
				".posts-container",
				".blog-posts",
				"main .gb-grid-wrapper",
			];

			for (const selector of selectors) {
				targetContainer = document.querySelector(selector);
				if (targetContainer) break;
			}
		}

		if (!targetContainer) return;

		// Show loading
		targetContainer.classList.add("jc-filter-loading");
		filterContainer.classList.add("jc-filter-loading");

		try {
			const posts = await fetchPosts(taxonomy, termSlug, page, perPage);
			renderPosts(targetContainer, posts);
			renderPagination(filterContainer, posts);
		} catch (error) {
			console.error("JC Taxonomy Filter: Error fetching posts", error);
		} finally {
			targetContainer.classList.remove("jc-filter-loading");
			filterContainer.classList.remove("jc-filter-loading");
		}
	};

	const createPostCard = (post) => {
		const article = document.createElement("article");
		article.className = "jc-filter-post";

		article.innerHTML = `
            ${
				post.thumbnail
					? `
                <div class="jc-filter-post__image">
                    <a href="${post.link}">
                        <img src="${post.thumbnail}" alt="${post.title}" loading="lazy" />
                    </a>
                </div>
            `
					: ""
			}
            <div class="jc-filter-post__content">
                <h3 class="jc-filter-post__title">
                    <a href="${post.link}">${post.title}</a>
                </h3>
                <div class="jc-filter-post__date">${post.date}</div>
                ${
					post.excerpt
						? `<p class="jc-filter-post__excerpt">${post.excerpt}</p>`
						: ""
				}
            </div>
        `;

		return article;
	};

	const updateUrl = (taxonomy, termSlug) => {
		const url = new URL(window.location.href);

		if (termSlug) {
			url.searchParams.set(`filter_${taxonomy}`, termSlug);
		} else {
			url.searchParams.delete(`filter_${taxonomy}`);
		}

		window.history.pushState({}, "", url);
	};

	const updateUrlAndReload = (taxonomy, termSlug) => {
		const url = new URL(window.location.href);

		if (termSlug) {
			url.searchParams.set(`filter_${taxonomy}`, termSlug);
		} else {
			url.searchParams.delete(`filter_${taxonomy}`);
		}

		window.location.href = url.toString();
	};

	// Initialize when DOM is ready
	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})();
