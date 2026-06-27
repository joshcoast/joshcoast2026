(function (blocks, element, i18n) {
	"use strict";

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType("wp-blog-category-filter/posts-filter", {
		title: __("Josh's Notes", "wp-blog-category-filter"),
		description: __(
			"Render AJAX category filters and paginated post cards.",
			"wp-blog-category-filter"
		),
		icon: "filter",
		category: "widgets",
		supports: {
			html: false,
		},
		edit: function () {
			return el(
				"div",
				{ className: "wp-blog-filter-block-placeholder" },
				__(
					"Blog Category Filter will render on the frontend.",
					"wp-blog-category-filter"
				)
			);
		},
		save: function () {
			return null;
		},
	});
})(window.wp.blocks, window.wp.element, window.wp.i18n);
