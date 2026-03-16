/**
 * Reusable SVG icon library for Toolbox blocks.
 */

import { __ } from "@wordpress/i18n";

export const TB_ICON_LIBRARY = [
	{
		key: "arrow-down",
		label: __("Arrow Down", "toolbox-blocks"),
		paths: ["M12 4v16", "M8 14 L12 20 L16 14"],
	},
	{
		key: "arrow-right",
		label: __("Arrow Right", "toolbox-blocks"),
		paths: ["M4 12h16", "M14 8 L20 12 L14 16"],
	},
	{
		key: "arrow-up-right",
		label: __("Arrow Up Right", "toolbox-blocks"),
		paths: ["M7 17 L17 7", "M12 12 L17 7 L13 11"],
	},
	{
		key: "chevron-right",
		label: __("Chevron Right", "toolbox-blocks"),
		paths: ["M9 6 L15 12 L9 18"],
	},
	{
		key: "download",
		label: __("Download", "toolbox-blocks"),
		paths: ["M12 4 v11", "M8 12 L12 18 L16 12", "M4 20 h16"],
	},
	{
		key: "external-link",
		label: __("External Link", "toolbox-blocks"),
		paths: ["M14 4h6v6", "M10 14 20 4", "M20 13v7H4V4h7"],
	},
	{
		key: "plus",
		label: __("Plus", "toolbox-blocks"),
		paths: ["M12 5v14", "M5 12h14"],
	},
	{
		key: "play",
		label: __("Play", "toolbox-blocks"),
		paths: ["m9 7 8 5-8 5z"],
		fill: true,
	},
	{
		key: "star",
		label: __("Star", "toolbox-blocks"),
		paths: [
			"m12 3 2.9 5.9 6.5 1-4.7 4.6 1.1 6.5L12 18l-5.8 3.1 1.1-6.5L2.6 9.9l6.5-1z",
		],
	},
	{
		key: "plus-circle",
		label: __("Plus Circle", "toolbox-blocks"),
		paths: [
			"M12 5v14",
			"M5 12h14",
			"M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z",
		],
	},
];

export function getIconByKey(iconKey) {
	return (
		TB_ICON_LIBRARY.find((icon) => icon.key === iconKey) ||
		TB_ICON_LIBRARY[0]
	);
}

export function ToolboxIcon({ iconKey, className = "", title = "" }) {
	const icon = getIconByKey(iconKey);
	const isFilled = Boolean(icon.fill);

	return (
		<svg
			className={className}
			viewBox="0 0 24 24"
			width="1em"
			height="1em"
			fill={isFilled ? "currentColor" : "none"}
			stroke={isFilled ? "none" : "currentColor"}
			strokeWidth="1.8"
			strokeLinecap="round"
			strokeLinejoin="round"
			aria-hidden="true"
			focusable="false"
		>
			{title ? <title>{title}</title> : null}
			{icon.paths.map((path) => (
				<path key={path} d={path} />
			))}
		</svg>
	);
}
