/**
 * Button – edit component.
 */

import { useEffect } from "@wordpress/element";
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";
import {
	PanelBody,
	ToggleControl,
	TextControl,
	SelectControl,
} from "@wordpress/components";
import InspectorTabs from "../../shared/InspectorTabs";
import StylesPanel from "../../shared/StylesPanel";
import IconPicker from "../../shared/IconPicker";
import { ToolboxIcon } from "../../shared/icon-library";
import { generateCssFromStyles } from "../../utils/generate-css";
import { DEFAULT_BUTTON_ICON, DEFAULT_BUTTON_ICON_POSITION } from "./defaults";

export default function ButtonEdit({ attributes, setAttributes, clientId }) {
	const {
		uniqueId,
		styles,
		text,
		url,
		target,
		rel,
		showIcon,
		icon,
		iconPosition,
		className,
	} = attributes;

	useEffect(() => {
		if (!uniqueId && clientId) {
			setAttributes({
				uniqueId: clientId.replace(/[^a-z0-9]/gi, "").slice(0, 12),
			});
		}
	}, [clientId, uniqueId, setAttributes]);

	const css = uniqueId
		? generateCssFromStyles(`.tb-${uniqueId}.tb-button__link`, styles)
		: "";
	const linkClassName = [
		"tb-button__link",
		uniqueId && `tb-${uniqueId}`,
		className,
	]
		.filter(Boolean)
		.join(" ");
	const blockProps = useBlockProps({ className: "tb-block tb-button" });
	const currentIcon = icon || DEFAULT_BUTTON_ICON;
	const currentIconPosition = iconPosition || DEFAULT_BUTTON_ICON_POSITION;

	const iconMarkup = showIcon ? (
		<span className="tb-button__icon" aria-hidden="true">
			<ToolboxIcon
				iconKey={currentIcon}
				className="tb-button__icon-svg"
			/>
		</span>
	) : null;

	const settings = (
		<PanelBody title={__("Settings")} initialOpen={true}>
			<TextControl
				label={__("URL")}
				value={url || ""}
				onChange={(v) => setAttributes({ url: v })}
				placeholder="https://…"
				type="url"
			/>
			<ToggleControl
				label={__("Open in new tab")}
				checked={target === "_blank"}
				onChange={(v) =>
					setAttributes({ target: v ? "_blank" : "_self" })
				}
			/>
			<TextControl
				label={__("Link rel")}
				value={rel || ""}
				onChange={(v) => setAttributes({ rel: v })}
				placeholder="noopener"
			/>
			<ToggleControl
				label={__("Show icon", "toolbox-blocks")}
				checked={!!showIcon}
				onChange={(v) => setAttributes({ showIcon: v })}
			/>
			{showIcon && (
				<>
					<IconPicker
						label={__("Icon", "toolbox-blocks")}
						value={currentIcon}
						onChange={(v) => setAttributes({ icon: v })}
					/>
					<SelectControl
						label={__("Icon position", "toolbox-blocks")}
						value={currentIconPosition}
						onChange={(v) => setAttributes({ iconPosition: v })}
						options={[
							{
								value: "left",
								label: __("Left", "toolbox-blocks"),
							},
							{
								value: "right",
								label: __("Right", "toolbox-blocks"),
							},
						]}
					/>
				</>
			)}
		</PanelBody>
	);

	return (
		<>
			{css && <style>{css}</style>}
			<InspectorControls>
				<InspectorTabs
					clientId={clientId}
					settings={settings}
					styles={
						<StylesPanel
							attributes={attributes}
							setAttributes={setAttributes}
							sections={[
								"sizing",
								"spacing",
								"typography",
								"backgrounds",
								"borders",
								"position",
								"effects",
								"pointerEvents",
								"cursor",
							]}
						/>
					}
				/>
			</InspectorControls>
			<div {...blockProps}>
				<a
					className={linkClassName}
					href={url || "#"}
					target={target === "_blank" ? "_blank" : undefined}
					rel={
						target === "_blank"
							? "noopener noreferrer"
							: rel || undefined
					}
					onClick={(event) => event.preventDefault()}
				>
					{showIcon && currentIconPosition === "left"
						? iconMarkup
						: null}
					<RichText
						tagName="span"
						className="tb-button__text"
						value={text}
						onChange={(v) => setAttributes({ text: v })}
						placeholder={__("Button text…")}
						allowedFormats={[]}
					/>
					{showIcon && currentIconPosition === "right"
						? iconMarkup
						: null}
				</a>
			</div>
		</>
	);
}
