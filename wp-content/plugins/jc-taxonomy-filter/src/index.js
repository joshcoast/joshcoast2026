import { registerBlockType } from "@wordpress/blocks";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from "@wordpress/components";
import { useSelect } from "@wordpress/data";
import { __ } from "@wordpress/i18n";
import "./editor.scss";

registerBlockType("jc/taxonomy-filter", {
	title: __("Taxonomy Filter", "jc-taxonomy-filter"),
	description: __(
		"AJAX-powered filter for posts by taxonomy",
		"jc-taxonomy-filter"
	),
	category: "widgets",
	icon: "filter",
	keywords: [__("filter"), __("category"), __("tag"), __("taxonomy")],
	supports: {
		html: false,
		align: ["wide", "full"],
	},
	attributes: {
		taxonomy: {
			type: "string",
			default: "category",
		},
		showAll: {
			type: "boolean",
			default: true,
		},
		allLabel: {
			type: "string",
			default: "All",
		},
		queryId: {
			type: "string",
			default: "",
		},
		layout: {
			type: "string",
			default: "horizontal",
		},
		style: {
			type: "string",
			default: "buttons",
		},
		showPagination: {
			type: "boolean",
			default: false,
		},
	},

	edit: function Edit({ attributes, setAttributes }) {
		const {
			taxonomy,
			showAll,
			allLabel,
			queryId,
			layout,
			style,
			showPagination,
		} = attributes;
		const blockProps = useBlockProps({
			className: `jc-taxonomy-filter jc-taxonomy-filter--${layout} jc-taxonomy-filter--${style}`,
		});

		// Get available taxonomies
		const taxonomies = useSelect((select) => {
			const allTaxonomies = select("core").getTaxonomies({
				per_page: -1,
			});
			if (!allTaxonomies) return [];
			return allTaxonomies
				.filter((tax) => tax.visibility?.show_ui)
				.map((tax) => ({
					label: tax.name,
					value: tax.slug,
				}));
		}, []);

		// Get terms for selected taxonomy
		const terms = useSelect(
			(select) => {
				if (!taxonomy) return [];
				const allTerms = select("core").getEntityRecords(
					"taxonomy",
					taxonomy,
					{
						per_page: -1,
						hide_empty: true,
					}
				);
				return allTerms || [];
			},
			[taxonomy]
		);

		return (
			<>
				<InspectorControls>
					<PanelBody
						title={__("Filter Settings", "jc-taxonomy-filter")}
					>
						<SelectControl
							label={__("Taxonomy", "jc-taxonomy-filter")}
							value={taxonomy}
							options={[
								{ label: __("Select taxonomy..."), value: "" },
								...taxonomies,
							]}
							onChange={(value) =>
								setAttributes({ taxonomy: value })
							}
						/>
						<ToggleControl
							label={__(
								'Show "All" Button',
								"jc-taxonomy-filter"
							)}
							checked={showAll}
							onChange={(value) =>
								setAttributes({ showAll: value })
							}
						/>
						{showAll && (
							<TextControl
								label={__(
									"All Button Label",
									"jc-taxonomy-filter"
								)}
								value={allLabel}
								onChange={(value) =>
									setAttributes({ allLabel: value })
								}
							/>
						)}
						<TextControl
							label={__("Query Loop ID", "jc-taxonomy-filter")}
							help={__(
								"CSS selector for the post container to update (e.g., .gb-query-loop-wrapper)",
								"jc-taxonomy-filter"
							)}
							value={queryId}
							onChange={(value) =>
								setAttributes({ queryId: value })
							}
						/>
					</PanelBody>
					<PanelBody title={__("Layout", "jc-taxonomy-filter")}>
						<SelectControl
							label={__("Layout", "jc-taxonomy-filter")}
							value={layout}
							options={[
								{
									label: __("Horizontal"),
									value: "horizontal",
								},
								{ label: __("Vertical"), value: "vertical" },
							]}
							onChange={(value) =>
								setAttributes({ layout: value })
							}
						/>
						<SelectControl
							label={__("Style", "jc-taxonomy-filter")}
							value={style}
							options={[
								{ label: __("Buttons"), value: "buttons" },
								{ label: __("Pills"), value: "pills" },
								{ label: __("Links"), value: "links" },
							]}
							onChange={(value) =>
								setAttributes({ style: value })
							}
						/>
					</PanelBody>
					<PanelBody title={__("Pagination", "jc-taxonomy-filter")}>
						<ToggleControl
							label={__("Show Pagination", "jc-taxonomy-filter")}
							help={__(
								"Display pagination controls for filtered results",
								"jc-taxonomy-filter"
							)}
							checked={showPagination}
							onChange={(value) =>
								setAttributes({ showPagination: value })
							}
						/>
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					{showAll && (
						<button
							type="button"
							className="jc-taxonomy-filter__button is-active"
						>
							{allLabel}
						</button>
					)}
					{terms.map((term) => (
						<button
							key={term.id}
							type="button"
							className="jc-taxonomy-filter__button"
						>
							{term.name}
						</button>
					))}
					{terms.length === 0 && (
						<p className="jc-taxonomy-filter__placeholder">
							{taxonomy
								? __(
										"No terms found for this taxonomy.",
										"jc-taxonomy-filter"
								  )
								: __(
										"Select a taxonomy in the block settings.",
										"jc-taxonomy-filter"
								  )}
						</p>
					)}
				</div>
			</>
		);
	},

	save: function Save() {
		// Dynamic block - rendered on server
		return null;
	},
});
