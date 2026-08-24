(function (blocks, element, blockEditor, components, data, i18n) {
	"use strict";

	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;
	var useSelect = data.useSelect;
	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var MediaUpload = blockEditor.MediaUpload;
	var MediaUploadCheck = blockEditor.MediaUploadCheck;

	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ToggleControl = components.ToggleControl;
	var Button = components.Button;
	var Placeholder = components.Placeholder;

	blocks.registerBlockType("jc/project-client-card", {
		title: __("Client Project Card", "jc-16bit-arcade"),
		description: __(
			"Show a selected Client with image, project content, skills, and action button.",
			"jc-16bit-arcade"
		),
		icon: "format-image",
		category: "widgets",
		supports: {
			html: false,
		},
		attributes: {
			clientId: {
				type: "number",
				default: 0,
			},
			title: {
				type: "string",
				default: "",
			},
			description: {
				type: "string",
				default: "",
			},
			actionLabel: {
				type: "string",
				default: "View Project",
			},
			actionUrl: {
				type: "string",
				default: "",
			},
			imageId: {
				type: "number",
				default: 0,
			},
			imageUrl: {
				type: "string",
				default: "",
			},
			imageAlt: {
				type: "string",
				default: "",
			},
			imageOnRight: {
				type: "boolean",
				default: false,
			},
		},
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			var clientId = attributes.clientId;
			var title = attributes.title;
			var description = attributes.description;
			var actionLabel = attributes.actionLabel;
			var actionUrl = attributes.actionUrl;
			var imageId = attributes.imageId;
			var imageUrl = attributes.imageUrl;
			var imageAlt = attributes.imageAlt;
			var imageOnRight = attributes.imageOnRight;

			var clients = useSelect(
				function (select) {
					return (
						select("core").getEntityRecords("postType", "client", {
							per_page: -1,
							orderby: "title",
							order: "asc",
						}) || []
					);
				},
				[]
			);

			var selectedClient = useSelect(
				function (select) {
					if (!clientId) {
						return null;
					}

					return select("core").getEntityRecord("postType", "client", clientId);
				},
				[clientId]
			);

			var selectedClientMedia = useSelect(
				function (select) {
					if (!selectedClient || !selectedClient.featured_media) {
						return null;
					}

					return select("core").getMedia(selectedClient.featured_media);
				},
				[selectedClient ? selectedClient.featured_media : 0]
			);

			var selectedClientSkills = useSelect(
				function (select) {
					if (
						!selectedClient ||
						!Array.isArray(selectedClient.client_skill) ||
						!selectedClient.client_skill.length
					) {
						return [];
					}

					return (
						select("core").getEntityRecords("taxonomy", "client_skill", {
							include: selectedClient.client_skill,
							per_page: 100,
						}) || []
					);
				},
				[
					selectedClient && Array.isArray(selectedClient.client_skill)
						? selectedClient.client_skill.join(",")
						: "",
				]
			);

			var previewImageUrl = imageUrl;
			if (!previewImageUrl && selectedClientMedia && selectedClientMedia.source_url) {
				previewImageUrl = selectedClientMedia.source_url;
			}

			var previewTitle = title;
			if (!previewTitle && selectedClient && selectedClient.title && selectedClient.title.rendered) {
				previewTitle = selectedClient.title.rendered;
			}

			var previewActionLabel = actionLabel || __("View Project", "jc-16bit-arcade");

			var clientOptions = [
				{
					label: __("Select a client...", "jc-16bit-arcade"),
					value: "0",
				},
			].concat(
				clients.map(function (client) {
					return {
						label: client.title && client.title.rendered ? client.title.rendered : __("(Untitled)", "jc-16bit-arcade"),
						value: String(client.id),
					};
				})
			);

			var cardClasses = ["jc-project-client-card"];
			if (imageOnRight) {
				cardClasses.push("jc-project-client-card--image-right");
			}

			var blockProps = useBlockProps({
				className: cardClasses.join(" "),
			});

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __("Client & Layout", "jc-16bit-arcade"), initialOpen: true },
						el(SelectControl, {
							label: __("Client", "jc-16bit-arcade"),
							value: String(clientId || 0),
							options: clientOptions,
							onChange: function (value) {
								setAttributes({ clientId: parseInt(value, 10) || 0 });
							},
						}),
						el(ToggleControl, {
							label: __("Image on right", "jc-16bit-arcade"),
							checked: !!imageOnRight,
							onChange: function (value) {
								setAttributes({ imageOnRight: !!value });
							},
						})
					),
					el(
						PanelBody,
						{ title: __("Content", "jc-16bit-arcade"), initialOpen: true },
						el(TextControl, {
							label: __("Title", "jc-16bit-arcade"),
							value: title,
							onChange: function (value) {
								setAttributes({ title: value });
							},
						}),
						el(TextareaControl, {
							label: __("Description", "jc-16bit-arcade"),
							value: description,
							onChange: function (value) {
								setAttributes({ description: value });
							},
						}),
						el(TextControl, {
							label: __("Action Button Label", "jc-16bit-arcade"),
							value: actionLabel,
							onChange: function (value) {
								setAttributes({ actionLabel: value });
							},
						}),
						el(TextControl, {
							label: __("Action Button URL", "jc-16bit-arcade"),
							value: actionUrl,
							onChange: function (value) {
								setAttributes({ actionUrl: value });
							},
						})
					),
					el(
						PanelBody,
						{ title: __("Image Override", "jc-16bit-arcade"), initialOpen: false },
						el(TextControl, {
							label: __("Image Alt Text", "jc-16bit-arcade"),
							value: imageAlt,
							onChange: function (value) {
								setAttributes({ imageAlt: value });
							},
						}),
						el(
							MediaUploadCheck,
							null,
							el(MediaUpload, {
								onSelect: function (media) {
									setAttributes({
										imageId: media && media.id ? media.id : 0,
										imageUrl: media && media.url ? media.url : "",
										imageAlt: media && media.alt ? media.alt : imageAlt,
									});
								},
								allowedTypes: ["image"],
								value: imageId,
								render: function (renderProps) {
									return el(
										Button,
										{
											variant: "secondary",
											onClick: renderProps.open,
										},
										imageId
											? __("Replace Override Image", "jc-16bit-arcade")
											: __("Set Override Image", "jc-16bit-arcade")
									);
								},
							})
						),
						imageId
							? el(
								Button,
								{
									variant: "tertiary",
									onClick: function () {
										setAttributes({ imageId: 0, imageUrl: "" });
									},
									style: { marginLeft: "8px" },
								},
								__("Clear Override", "jc-16bit-arcade")
							)
							: null
					)
				),
				el(
					"div",
					blockProps,
					!clientId
						? el(Placeholder, {
							icon: "format-image",
							label: __("Client Project Card", "jc-16bit-arcade"),
							instructions: __("Select a Client in block settings to preview this project row.", "jc-16bit-arcade"),
						})
						: el(
							Fragment,
							null,
							el(
								"figure",
								{ className: "jc-project-client-card__media" },
								el(
									"div",
									{ className: "jc-project-client-card__media-frame" },
									previewImageUrl
										? el("img", {
											className: "jc-project-client-card__image",
											src: previewImageUrl,
											alt: imageAlt || previewTitle || "",
										})
										: el(
											"div",
											{ className: "jc-project-client-card__image-placeholder" },
											__("No image available.", "jc-16bit-arcade")
										)
								)
							),
							el(
								"div",
								{ className: "jc-project-client-card__content" },
								el("h2", { className: "jc-project-client-card__title" }, previewTitle || __("Add a project title", "jc-16bit-arcade")),
								description
									? el("p", { className: "jc-project-client-card__description" }, description)
									: el("p", { className: "jc-project-client-card__description" }, __("Add a project description in block settings.", "jc-16bit-arcade")),
								selectedClientSkills && selectedClientSkills.length
									? el(
										"ul",
										{ className: "jc-project-client-card__skills" },
										selectedClientSkills.map(function (term) {
											return el("li", { className: "jc-project-client-card__skill", key: term.id }, term.name);
										})
									)
									: el(
										"p",
										{ className: "jc-project-client-card__skills-empty" },
										__("Assign client skills on the selected Client post to show skill chips.", "jc-16bit-arcade")
									),
								actionUrl
									? el(
										"a",
										{
											className: "jc-btn jc-btn--sm jc-project-client-card__action",
											href: actionUrl,
											onClick: function (event) {
												event.preventDefault();
											},
										},
										previewActionLabel
									)
									: el(
										"span",
										{ className: "jc-btn jc-btn--sm jc-project-client-card__action is-disabled" },
										previewActionLabel
									)
							)
						)
				)
			);
		},
		save: function () {
			return null;
		},
	});
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.data, window.wp.i18n);
