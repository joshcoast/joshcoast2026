import { useEffect } from "@wordpress/element";
import { useSelect } from "@wordpress/data";
import {
	useBlockProps,
	InnerBlocks,
	InspectorControls,
} from "@wordpress/block-editor";
import { DeviceSwitcher, SpacingControls } from "../../shared/SpacingControls";
import { BorderControls } from "../../shared/BorderControls";

const TEMPLATE = [["core/paragraph", { placeholder: "Add content…" }]];

function deviceTypeToSuffix(deviceType) {
	if (deviceType === "Tablet") return "Tablet";
	if (deviceType === "Mobile") return "Mobile";
	return "";
}

export default function ContainerEdit({ attributes, setAttributes, clientId }) {
	const editorDeviceType = useSelect((select) => {
		const editor = select("core/editor");
		if (editor?.getDeviceType) return editor.getDeviceType();
		const editPost = select("core/edit-post");
		if (editPost?.__experimentalGetPreviewDeviceType)
			return editPost.__experimentalGetPreviewDeviceType();
		return "Desktop";
	}, []);
	const deviceSuffix = deviceTypeToSuffix(editorDeviceType);

	// Ensure a stable uniqueId for CSS (used in render_callback).
	useEffect(() => {
		if (!attributes.uniqueId && clientId) {
			setAttributes({
				uniqueId: clientId.replace(/^[^a-z]+/i, "c").slice(0, 12),
			});
		}
	}, [clientId]);

	const blockProps = useBlockProps({
		className: "cb-container-editor",
		style: {
			...coastBlocksGetSpacingInlineStyle(
				attributes.spacing,
				deviceSuffix,
			),
			...coastBlocksGetBorderInlineStyle(
				attributes.borders,
				deviceSuffix,
			),
		},
	});

	return (
		<>
			<InspectorControls>
				<DeviceSwitcher />
				<SpacingControls
					attributes={attributes}
					setAttributes={setAttributes}
				/>
				<BorderControls
					attributes={attributes}
					setAttributes={setAttributes}
				/>
			</InspectorControls>
			<div {...blockProps}>
				<InnerBlocks template={TEMPLATE} templateLock={false} />
			</div>
		</>
	);
}

/**
 * Inline style for spacing (desktop only in editor for simplicity).
 */
function coastBlocksGetSpacingInlineStyle(spacing, suffix) {
	if (!spacing) return {};
	const s = {};
	const sides = ["Top", "Right", "Bottom", "Left"];
	const types = ["padding", "margin"];
	types.forEach((type) => {
		sides.forEach((side) => {
			const key = type + side + suffix;
			const v = spacing[key];
			// React style expects camelCase (e.g. paddingTop, marginLeft).
			const styleKey = type + side;
			if (v !== undefined && v !== "") {
				s[styleKey] = isNumeric(v) ? v + "px" : String(v);
			}
		});
	});
	return s;
}

function isNumeric(v) {
	return (
		typeof v === "number" ||
		(typeof v === "string" && /^\d+(\.\d+)?$/.test(v))
	);
}

/**
 * Inline style for borders (desktop only in editor).
 */
function coastBlocksGetBorderInlineStyle(borders, suffix) {
	if (!borders) return {};
	const s = {};
	const sides = ["Top", "Right", "Bottom", "Left"];
	const corners = ["TopLeft", "TopRight", "BottomRight", "BottomLeft"];
	sides.forEach((side) => {
		const w = borders["border" + side + "Width" + suffix];
		const style =
			borders["border" + side + "Style" + suffix] ||
			(w !== undefined && w !== "" ? "solid" : undefined);
		const color = borders["border" + side + "Color" + suffix];
		if (w !== undefined && w !== "")
			s["border" + side + "Width"] = isNumeric(w) ? w + "px" : String(w);
		if (style !== undefined && style !== "" && style !== "none")
			s["border" + side + "Style"] = style;
		if (color !== undefined && color !== "")
			s["border" + side + "Color"] = color;
	});
	corners.forEach((corner) => {
		const r = borders["border" + corner + "Radius" + suffix];
		if (r !== undefined && r !== "")
			s["border" + corner + "Radius"] = isNumeric(r)
				? r + "px"
				: String(r);
	});
	return s;
}
