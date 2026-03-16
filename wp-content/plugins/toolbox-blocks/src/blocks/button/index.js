/**
 * Toolbox Blocks – Button block registration.
 */

import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";
import ButtonEdit from "./edit";
import {
	DEFAULT_BUTTON_STYLES,
	DEFAULT_BUTTON_ICON,
	DEFAULT_BUTTON_ICON_POSITION,
} from "./defaults";

registerBlockType("toolbox-blocks/button", {
	apiVersion: 3,
	title: __("Button"),
	description: __("A link button with full style controls."),
	icon: "button",
	category: "toolbox-blocks",
	supports: { anchor: true, customClassName: true },
	attributes: {
		uniqueId: { type: "string", default: "" },
		styles: { type: "object", default: DEFAULT_BUTTON_STYLES },
		text: { type: "string", default: "" },
		url: { type: "string", default: "" },
		target: { type: "string", default: "_self" },
		rel: { type: "string", default: "" },
		showIcon: { type: "boolean", default: false },
		icon: { type: "string", default: DEFAULT_BUTTON_ICON },
		iconPosition: { type: "string", default: DEFAULT_BUTTON_ICON_POSITION },
		anchor: { type: "string", default: "" },
		className: { type: "string", default: "" },
	},
	edit: ButtonEdit,
	save: () => null,
});
