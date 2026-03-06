/**
 * Shared: Borders panel (width, style, color per side; radius per corner).
 * Respects DeviceSwitcher: Desktop edits propagate to Tablet/Mobile; Tablet/Mobile edits stick to that breakpoint.
 */

import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { useSelect } from "@wordpress/data";
import {
	PanelBody,
	__experimentalUnitControl as UnitControl,
	ColorPalette,
	Popover,
} from "@wordpress/components";

const SIDES = [
	{ key: "Top", label: __("Top") },
	{ key: "Right", label: __("Right") },
	{ key: "Bottom", label: __("Bottom") },
	{ key: "Left", label: __("Left") },
];

const CORNERS = [
	{ key: "TopLeft", label: __("Top Left") },
	{ key: "TopRight", label: __("Top Right") },
	{ key: "BottomRight", label: __("Bottom Right") },
	{ key: "BottomLeft", label: __("Bottom Left") },
];

const BREAKPOINT_SUFFIXES = ["", "Tablet", "Mobile"];

function deviceTypeToBreakpointId(deviceType) {
	if (deviceType === "Tablet") return "Tablet";
	if (deviceType === "Mobile") return "Mobile";
	return "";
}

const UNITS = [
	{ value: "px", label: "px" },
	{ value: "em", label: "em" },
	{ value: "rem", label: "rem" },
	{ value: "%", label: "%" },
];

function getBorderValue(borders, key, suffix) {
	const k = key + suffix;
	return borders?.[k] ?? "";
}

/**
 * When editing on Desktop (suffix ''), propagate value to Tablet and Mobile.
 * When editing on Tablet or Mobile, only update that breakpoint.
 */
function setBorderValue(setAttributes, borders, key, suffix, value) {
	const next = { ...(borders || {}) };
	if (suffix === "") {
		next[key] = value ?? "";
		next[key + "Tablet"] = value ?? "";
		next[key + "Mobile"] = value ?? "";
	} else {
		next[key + suffix] = value ?? "";
	}
	setAttributes({ borders: next });
}

/**
 * Set border value for all sides at once (linked mode).
 */
function setBorderValueAll(
	setAttributes,
	borders,
	suffix,
	widthVal,
	styleVal,
	colorVal,
) {
	const next = { ...(borders || {}) };
	SIDES.forEach(({ key }) => {
		if (widthVal !== undefined) {
			const wKey = "border" + key + "Width" + suffix;
			next[wKey] = widthVal ?? "";
		}
		if (styleVal !== undefined) {
			const sKey = "border" + key + "Style" + suffix;
			next[sKey] = styleVal ?? "";
		}
		if (colorVal !== undefined) {
			const cKey = "border" + key + "Color" + suffix;
			next[cKey] = colorVal ?? "";
		}
	});
	setAttributes({ borders: next });
}

/**
 * Set border radius for all corners at once (linked mode).
 */
function setBorderRadiusValueAll(setAttributes, borders, suffix, radiusVal) {
	const next = { ...(borders || {}) };
	CORNERS.forEach(({ key }) => {
		const rKey = "border" + key + "Radius" + suffix;
		next[rKey] = radiusVal ?? "";
	});
	setAttributes({ borders: next });
}

/**
 * Visual border style selector with SVG previews.
 */
function BorderStylePicker({ value, onChange, label }) {
	const [isOpen, setIsOpen] = useState(false);
	const ref = useState(null)[1];

	const stylePresets = [
		{ value: "none", label: __("None") },
		{ value: "solid", label: __("Solid") },
		{ value: "dashed", label: __("Dashed") },
		{ value: "dotted", label: __("Dotted") },
		{ value: "double", label: __("Double") },
	];

	const renderPreview = (style) => {
		const lineProps = {
			x1: "2",
			y1: "8",
			x2: "30",
			y2: "8",
			stroke: "#1e1e1e",
			strokeWidth: "2",
		};
		let line;
		if (style === "none") {
			return <svg width="32" height="16" />;
		} else if (style === "solid") {
			line = <line {...lineProps} />;
		} else if (style === "dashed") {
			line = <line {...lineProps} strokeDasharray="4,2" />;
		} else if (style === "dotted") {
			line = (
				<line
					{...lineProps}
					strokeDasharray="1,3"
					strokeLinecap="round"
				/>
			);
		} else if (style === "double") {
			return (
				<svg width="32" height="16" viewBox="0 0 32 16">
					<line
						x1="2"
						y1="5"
						x2="30"
						y2="5"
						stroke="#1e1e1e"
						strokeWidth="1.5"
					/>
					<line
						x1="2"
						y1="11"
						x2="30"
						y2="11"
						stroke="#1e1e1e"
						strokeWidth="1.5"
					/>
				</svg>
			);
		}
		return (
			<svg width="32" height="16" viewBox="0 0 32 16">
				{line}
			</svg>
		);
	};

	const currentStyle = stylePresets.find((s) => s.value === value);

	return (
		<>
			<button
				ref={ref}
				type="button"
				className="cb-border-style-picker"
				onClick={() => setIsOpen(!isOpen)}
				title={label}
				aria-label={label}
			>
				{renderPreview(value)}
			</button>
			{isOpen && (
				<Popover
					placement="bottom-start"
					onClose={() => setIsOpen(false)}
					style={{ zIndex: 1000 }}
				>
					<div className="cb-style-popover">
						{stylePresets.map((preset) => (
							<button
								key={preset.value}
								type="button"
								className={`cb-style-option ${
									value === preset.value ? "is-active" : ""
								}`}
								onClick={() => {
									onChange(preset.value);
									setIsOpen(false);
								}}
								title={preset.label}
							>
								{renderPreview(preset.value)}
								<span>{preset.label}</span>
							</button>
						))}
					</div>
				</Popover>
			)}
		</>
	);
}

/**
 * Color picker button with popover.
 * Displays a circular button with the selected color; clicking opens the full color picker.
 */
function ColorPickerButton({ value, onChange, colors, label }) {
	const [isOpen, setIsOpen] = useState(false);
	const ref = useState(null)[1];

	return (
		<>
			<button
				ref={ref}
				type="button"
				className="cb-color-picker-button"
				style={{
					backgroundColor: value || "transparent",
				}}
				onClick={() => setIsOpen(!isOpen)}
				title={label}
				aria-label={label}
			/>
			{isOpen && (
				<Popover
					placement="bottom-start"
					onClose={() => setIsOpen(false)}
					style={{ zIndex: 1000 }}
				>
					<div className="cb-color-popover">
						<ColorPalette
							value={value}
							onChange={(v) => {
								onChange(v);
								setIsOpen(false);
							}}
							colors={colors}
							clearable
							enableAlpha
						/>
					</div>
				</Popover>
			)}
		</>
	);
}

/**
 * Border controls for current device. Device comes from editor DeviceSwitcher.
 */
export function BorderControls({ attributes, setAttributes }) {
	const borders = attributes.borders || {};
	const borderLinked = attributes.borderLinked !== false; // Default to true
	const borderRadiusLinked = attributes.borderRadiusLinked !== false; // Default to true

	const editorDeviceType = useSelect((select) => {
		const editor = select("core/editor");
		if (editor?.getDeviceType) return editor.getDeviceType();
		const editPost = select("core/edit-post");
		if (editPost?.__experimentalGetPreviewDeviceType)
			return editPost.__experimentalGetPreviewDeviceType();
		return "Desktop";
	}, []);

	const themeColors = useSelect((select) => {
		const settings = select("core/block-editor")?.getSettings?.();
		return settings?.colors || [];
	}, []);

	const activeSuffix = deviceTypeToBreakpointId(editorDeviceType);

	const getVal = (key) => getBorderValue(borders, key, activeSuffix);
	const setVal = (key, value) =>
		setBorderValue(setAttributes, borders, key, activeSuffix, value);

	return (
		<PanelBody
			title={__("Borders")}
			initialOpen={false}
			className="cb-border-controls"
		>
			<div className="cb-border-section">
				<div className="cb-border-header">
					<p className="cb-border-label">{__("BORDER")}</p>
					<button
						type="button"
						onClick={() =>
							setAttributes({ borderLinked: !borderLinked })
						}
						className={`cb-border-link-toggle ${
							borderLinked ? "is-linked" : ""
						}`}
						title={
							borderLinked
								? __("Click to unlink")
								: __("Click to link")
						}
						aria-label={
							borderLinked ? __("Linked") : __("Unlinked")
						}
					>
						<span
							className={`dashicons dashicons-admin-${
								borderLinked ? "links" : "link"
							}`}
						/>
					</button>
				</div>
				{SIDES.map(({ key, label }) => (
					<div key={key}>
						<div className="cb-border-row">
							<span
								className={`cb-border-side-icon cb-border-side-icon--${key.toLowerCase()}`}
								aria-hidden
							/>
							<div className="cb-border-row-inputs">
								{UnitControl ? (
									<UnitControl
										value={getVal("border" + key + "Width")}
										onChange={(v) => {
											if (borderLinked) {
												setBorderValueAll(
													setAttributes,
													borders,
													activeSuffix,
													v,
													undefined,
													undefined,
												);
											} else {
												setVal(
													"border" + key + "Width",
													v,
												);
											}
										}}
										units={UNITS}
										min={0}
										label={label}
										hideLabelFromVision
									/>
								) : (
									<input
										type="text"
										className="components-text-control__input"
										value={
											getVal("border" + key + "Width") ||
											""
										}
										onChange={(e) => {
											if (borderLinked) {
												setBorderValueAll(
													setAttributes,
													borders,
													activeSuffix,
													e.target.value,
													undefined,
													undefined,
												);
											} else {
												setVal(
													"border" + key + "Width",
													e.target.value,
												);
											}
										}}
										placeholder="0"
										aria-label={label + " " + __("Width")}
									/>
								)}
								<BorderStylePicker
									value={
										getVal("border" + key + "Style") ||
										"solid"
									}
									onChange={(v) => {
										if (borderLinked) {
											setBorderValueAll(
												setAttributes,
												borders,
												activeSuffix,
												undefined,
												v,
												undefined,
											);
										} else {
											setVal("border" + key + "Style", v);
										}
									}}
									label={__("Style")}
								/>
								<ColorPickerButton
									value={getVal("border" + key + "Color")}
									onChange={(v) => {
										if (borderLinked) {
											setBorderValueAll(
												setAttributes,
												borders,
												activeSuffix,
												undefined,
												undefined,
												v,
											);
										} else {
											setVal("border" + key + "Color", v);
										}
									}}
									colors={themeColors}
									label={label + " " + __("Color")}
								/>
							</div>
						</div>
					</div>
				))}
			</div>
			<div className="cb-border-section">
				<div className="cb-border-header">
					<p className="cb-border-label">{__("BORDER RADIUS")}</p>
					<button
						type="button"
						onClick={() =>
							setAttributes({
								borderRadiusLinked: !borderRadiusLinked,
							})
						}
						className={`cb-border-link-toggle ${
							borderRadiusLinked ? "is-linked" : ""
						}`}
						title={
							borderRadiusLinked
								? __("Click to unlink")
								: __("Click to link")
						}
						aria-label={
							borderRadiusLinked ? __("Linked") : __("Unlinked")
						}
					>
						<span
							className={`dashicons dashicons-admin-${
								borderRadiusLinked ? "links" : "link"
							}`}
						/>
					</button>
				</div>
				<div className="cb-border-radius-grid">
					{CORNERS.map(({ key, label }) => (
						<div key={key} className="cb-border-radius-cell">
							{UnitControl ? (
								<UnitControl
									label={label}
									value={getVal("border" + key + "Radius")}
									onChange={(v) => {
										if (borderRadiusLinked) {
											setBorderRadiusValueAll(
												setAttributes,
												borders,
												activeSuffix,
												v,
											);
										} else {
											setVal(
												"border" + key + "Radius",
												v,
											);
										}
									}}
									units={UNITS}
									min={0}
								/>
							) : (
								<label>
									<span className="cb-border-radius-label">
										{label}
									</span>
									<input
										type="text"
										className="components-text-control__input"
										value={
											getVal("border" + key + "Radius") ||
											""
										}
										onChange={(e) => {
											if (borderRadiusLinked) {
												setBorderRadiusValueAll(
													setAttributes,
													borders,
													activeSuffix,
													e.target.value,
												);
											} else {
												setVal(
													"border" + key + "Radius",
													e.target.value,
												);
											}
										}}
										placeholder="0"
									/>
								</label>
							)}
						</div>
					))}
				</div>
			</div>
		</PanelBody>
	);
}

export default BorderControls;
