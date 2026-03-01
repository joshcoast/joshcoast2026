/**
 * WP Blog Category Filter - Frontend JavaScript
 */

(function ($) {
	"use strict";

	var WPBlogFilter = {
		init: function () {
			this.bindEvents();
			this.$container = $(".wp-blog-filter__container");
			// Better detection for GP and other themes
			this.$postsContainer = this.findPostsContainer();
			this.currentCategory = wpBlogFilterAjax.currentCategory || 0;
			this.currentPage = 1;
			this.isLoading = false;

			// Remove problematic theme classes that interfere with our grid
			this.removeProblematicClasses();

			// Theme post structure cleanup removed - using child theme template override
		},

		removeThemePostStructure: function () {},

		removeProblematicClasses: function () {
			// Remove GeneratePress masonry and column classes that interfere with our grid
			var problematicClasses = [
				"generate-columns-container",
				"masonry-container",
				"generate-masonry",
				"masonry",
				"columns-container",
			];

			// Target the main content wrapper and any parent containers
			var $contentWrappers = $(
				".content, #content, .site-main, #main, .main-content"
			);

			$contentWrappers.each(function () {
				var $wrapper = $(this);
				problematicClasses.forEach(function (className) {
					$wrapper.removeClass(className);
				});
			});

			// Also check for any elements that might have these classes
			problematicClasses.forEach(function (className) {
				$("." + className).removeClass(className);
			});
		},

		bindEvents: function () {
			var self = this;

			// Filter button clicks
			$(document).on("click", ".wp-blog-filter__button", function (e) {
				e.preventDefault();
				var categoryId = $(this).data("category");

				if (self.isLoading) return;

				self.filterPosts(categoryId, 1);
			});

			// Pagination clicks
			$(document).on("click", ".wp-blog-filter__page-btn", function (e) {
				e.preventDefault();
				var page = $(this).data("page");
				var categoryId =
					$(this).data("category") || self.currentCategory;

				if (self.isLoading) return;

				self.filterPosts(categoryId, page);
			});
		},

		filterPosts: function (categoryId, page) {
			var self = this;

			// Don't filter if already loading
			if (self.isLoading) return;

			self.isLoading = true;
			self.currentCategory = categoryId;
			self.currentPage = page;

			// Update active button
			$(".wp-blog-filter__button").removeClass(
				"wp-blog-filter__button--active"
			);
			$(
				'.wp-blog-filter__button[data-category="' + categoryId + '"]'
			).addClass("wp-blog-filter__button--active");

			// Show loading
			$(".wp-blog-filter__loading").show();

			// Make AJAX request
			$.ajax({
				url: wpBlogFilterAjax.ajaxurl,
				type: "POST",
				data: {
					action: "filter_posts",
					category_id: categoryId,
					page: page,
					nonce: wpBlogFilterAjax.nonce,
				},
				success: function (response) {
					if (response.success) {
						// Update posts
						self.updatePosts(response.data.posts);

						// Update pagination
						self.updatePagination(response.data.pagination);

						// Update URL without page reload
						self.updateURL(categoryId, page);

						// Scroll to top of posts
						self.scrollToPosts();
					} else {
						console.error("AJAX Error:", response.data);
						self.showError(
							"Failed to load posts. Please try again."
						);
					}
				},
				error: function (xhr, status, error) {
					console.error("AJAX Error:", error);
					self.showError("Network error. Please try again.");
				},
				complete: function () {
					self.isLoading = false;
					$(".wp-blog-filter__loading").hide();
				},
			});
		},

		updatePosts: function (postsHtml) {
			// Find the posts container
			var $postsContainer = this.findPostsContainer();

			if ($postsContainer.length) {
				// Find our custom grid wrapper
				var $gridWrapper = $postsContainer.find(
					".wp-blog-filter__posts-grid"
				);

				if ($gridWrapper.length) {
					// Clear existing posts and add new ones
					$gridWrapper.empty().append(postsHtml);
				} else {
					// Fallback: create grid wrapper if it doesn't exist
					$postsContainer.find("article").remove();
					$postsContainer.append(
						'<div class="wp-blog-filter__posts-grid">' +
							postsHtml +
							"</div>"
					);
				}

				// Remove ALL pagination elements - both plugin and theme pagination
				$postsContainer.find(".wp-blog-filter__pagination").remove();
				$(".wp-blog-filter__pagination").remove();
				// Remove common theme pagination classes
				$(
					".paging-navigation, .pagination, .nav-links, .page-numbers"
				).remove();
				// Remove GeneratePress specific pagination
				$(".generate-pagination, .generate-page-numbers").remove();

				// Ensure problematic classes are removed after content update
				this.removeProblematicClasses();
			} else {
				// Fallback: replace the entire content area
				console.warn(
					"Could not find posts container, using fallback method"
				);
				$(".site-main").html(
					'<div class="wp-blog-filter__container">' +
						$(".wp-blog-filter__container").html() +
						"</div>" +
						'<div class="wp-blog-filter__posts-grid">' +
						postsHtml +
						"</div>"
				);
			}
		},

		updatePagination: function (paginationHtml) {
			// Remove ALL existing pagination elements first
			$(".wp-blog-filter__pagination").remove();
			$(
				".paging-navigation, .pagination, .nav-links, .page-numbers"
			).remove();
			$(".generate-pagination, .generate-page-numbers").remove();

			// Add new pagination after the posts grid - ensure it's outside the grid container
			if (paginationHtml) {
				var $postsContainer = this.findPostsContainer();
				if ($postsContainer.length) {
					var $gridWrapper = $postsContainer.find(
						".wp-blog-filter__posts-grid"
					);
					if ($gridWrapper.length) {
						// Place pagination directly after the grid wrapper
						$gridWrapper.after(paginationHtml);
					} else {
						// Fallback: place after posts container
						$postsContainer.after(paginationHtml);
					}
				} else {
					// Last resort: add after site-main
					$(".site-main").after(paginationHtml);
				}
			}
		},

		findPostsContainer: function () {
			// Standard WordPress containers - exclude our grid wrapper
			var standardSelectors = [
				".wp-blog-filter-posts",
				".posts-container",
				".blog-posts",
				".archive-posts",
				".main-content",
				".content",
				"#main",
			];

			for (var i = 0; i < standardSelectors.length; i++) {
				var $container = $(standardSelectors[i]);
				if ($container.length > 0) {
					return $container;
				}
			}

			// Fallback: find any container with posts (but not our grid wrapper)
			var $fallback = $(".post").first().parent();
			if (
				$fallback.length > 0 &&
				!$fallback.hasClass("wp-blog-filter__posts-grid")
			) {
				return $fallback;
			}

			// Last resort: main content area
			return $(".site-main, .content, #content, #main").first();
		},

		updateURL: function (categoryId, page) {
			// Update URL for bookmarking and browser back/forward
			var url = new URL(window.location);

			if (categoryId > 0) {
				url.searchParams.set("cat", categoryId);
			} else {
				url.searchParams.delete("cat");
			}

			if (page > 1) {
				url.searchParams.set("paged", page);
			} else {
				url.searchParams.delete("paged");
			}

			// Update URL without page reload
			window.history.pushState({}, "", url.toString());
		},

		scrollToPosts: function () {
			// Smooth scroll to the posts container
			var $container = $(".wp-blog-filter__container");
			if ($container.length) {
				$("html, body").animate(
					{
						scrollTop: $container.offset().top - 50,
					},
					300
				);
			}
		},

		showError: function (message) {
			// Simple error display - could be enhanced with a proper notice system
			alert(message);
		},
	};

	// Initialize when document is ready
	$(document).ready(function () {
		WPBlogFilter.init();
	});

	// Handle browser back/forward buttons
	$(window).on("popstate", function () {
		// Reload the page to show the correct filtered content
		// This is simpler than trying to parse the URL and filter accordingly
		window.location.reload();
	});
})(jQuery);
