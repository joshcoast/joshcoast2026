/**
 * WP Blog Category Filter - Frontend JavaScript
 */

(function ($) {
	"use strict";

	var WPBlogFilter = {
		init: function () {
			this.$instance = $(".wp-blog-filter__instance").first();
			if (!this.$instance.length) {
				return;
			}

			this.$container = this.$instance.find(".wp-blog-filter__container").first();
			this.bindEvents();
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
				if (!self.$instance.length) return;

				var $instance = $(this).closest(".wp-blog-filter__instance");
				if (!$instance.length || !$instance.is(self.$instance)) return;

				e.preventDefault();
				var categoryId = $(this).data("category");

				if (self.isLoading) return;

				self.filterPosts(categoryId, 1, $instance);
			});

			// Pagination clicks
			$(document).on("click", ".wp-blog-filter__page-btn", function (e) {
				if (!self.$instance.length) return;

				var $instance = $(this).closest(".wp-blog-filter__instance");
				if (!$instance.length || !$instance.is(self.$instance)) return;

				e.preventDefault();
				var page = $(this).data("page");
				var categoryId =
					$(this).data("category") || self.currentCategory;

				if (self.isLoading) return;

				self.filterPosts(categoryId, page, $instance);
			});
		},

		filterPosts: function (categoryId, page, $instance) {
			var self = this;
			var $activeInstance = $instance && $instance.length ? $instance : self.$instance;

			// Don't filter if already loading
			if (self.isLoading) return;

			self.isLoading = true;
			self.currentCategory = categoryId;
			self.currentPage = page;

			// Update active button
			$activeInstance.find(".wp-blog-filter__button").removeClass(
				"wp-blog-filter__button--active"
			);
			$activeInstance.find(
				'.wp-blog-filter__button[data-category="' + categoryId + '"]'
			).addClass("wp-blog-filter__button--active");

			// Show loading
			$activeInstance.find(".wp-blog-filter__loading").show();

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
							self.updatePosts(response.data.posts, $activeInstance);

						// Update pagination
							self.updatePagination(response.data.pagination, $activeInstance);

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
					$activeInstance.find(".wp-blog-filter__loading").hide();
				},
			});
		},

		updatePosts: function (postsHtml, $instance) {
			// Find the posts container
			var $postsContainer = this.findPostsContainer($instance);

			if ($postsContainer.length) {
				// Find our custom grid wrapper
				var $gridWrapper = $postsContainer.hasClass(
					"wp-blog-filter__posts-grid"
				)
					? $postsContainer
					: $postsContainer.find(".wp-blog-filter__posts-grid").first();

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
					$gridWrapper = $postsContainer.find(".wp-blog-filter__posts-grid").first();
				}

				// Remove only plugin pagination near this posts container
				if ($gridWrapper.length) {
					$gridWrapper.siblings(".wp-blog-filter__pagination").remove();
				}
				$postsContainer.find(".wp-blog-filter__pagination").remove();

				// Ensure problematic classes are removed after content update
				this.removeProblematicClasses();
			} else {
				// Fallback: use plugin-owned insertion near the filter controls
				console.warn(
					"Could not find posts container, using plugin fallback"
				);
				var $scope = $instance.find(".wp-blog-filter__loop-scope").first();
				if ($scope.length) {
					$scope.find(".wp-blog-filter__posts-grid").remove();
					$scope.append(
						'<div class="wp-blog-filter__posts-grid">' +
							postsHtml +
							"</div>"
					);
				} else if (this.$container.length) {
					$instance.find(".wp-blog-filter__posts-grid").remove();
					this.$container.after(
						'<div class="wp-blog-filter__posts-grid">' +
							postsHtml +
							"</div>"
					);
				}
			}
		},

		updatePagination: function (paginationHtml, $instance) {
			// Remove existing plugin pagination before adding updated controls
			$instance.find(".wp-blog-filter__pagination").remove();

			// Add new pagination after the posts grid - ensure it's outside the grid container
			if (paginationHtml) {
				var $postsContainer = this.findPostsContainer($instance);
				if ($postsContainer.length) {
					var $gridWrapper = $postsContainer.hasClass(
						"wp-blog-filter__posts-grid"
					)
						? $postsContainer
						: $postsContainer.find(".wp-blog-filter__posts-grid").first();
					if ($gridWrapper.length) {
						// Place pagination directly after the grid wrapper
						$gridWrapper.after(paginationHtml);
					} else {
						// Fallback: place after posts container
						$postsContainer.after(paginationHtml);
					}
				} else {
					// Last resort: add after plugin grid if present, then after filter controls
					var $grid = $instance.find(".wp-blog-filter__posts-grid").first();
					if ($grid.length) {
						$grid.after(paginationHtml);
					} else if (this.$container.length) {
						this.$container.after(paginationHtml);
					}
				}
			}
		},

		findPostsContainer: function ($instance) {
			var $scopeRoot = $instance && $instance.length ? $instance : this.$instance;

			// Prefer plugin-owned loop scope when available
			var $loopScope = $scopeRoot.find(".wp-blog-filter__loop-scope").first();
			if ($loopScope.length > 0) {
				return $loopScope;
			}

			// If our grid wrapper already exists, use its parent container first
			var $existingGrid = $scopeRoot.find(".wp-blog-filter__posts-grid").first();
			if ($existingGrid.length > 0) {
				return $existingGrid.parent();
			}

			// Standard WordPress containers - exclude our grid wrapper
			var standardSelectors = [
				".wp-blog-filter-posts",
				".posts-container",
				".blog-posts",
				".archive-posts",
				".entry-content",
				".site-content",
				".content-area",
				".main-content",
				".content",
				"#main",
				"main",
			];

			for (var i = 0; i < standardSelectors.length; i++) {
				var $container = $scopeRoot.find(standardSelectors[i]).first();
				if ($container.length > 0) {
					return $container;
				}
			}

			// Fallback: find any container with posts (but not our grid wrapper)
			var $fallback = $scopeRoot.find(".post").first().parent();
			if (
				$fallback.length > 0 &&
				!$fallback.hasClass("wp-blog-filter__posts-grid")
			) {
				return $fallback;
			}

			return $scopeRoot;
		},

		updateURL: function (categoryId, page) {
			// Update URL for bookmarking and browser back/forward
			var url = new URL(window.location);

			if (categoryId > 0) {
				url.searchParams.set("wpbf_cat", categoryId);
			} else {
				url.searchParams.delete("wpbf_cat");
			}

			if (page > 1) {
				url.searchParams.set("wpbf_page", page);
			} else {
				url.searchParams.delete("wpbf_page");
			}

			// Update URL without page reload
			window.history.pushState({}, "", url.toString());
		},

		scrollToPosts: function () {
			// Smooth scroll to the posts container
			var $container = this.$instance.find(".wp-blog-filter__container").first();
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
