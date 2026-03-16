/**
 * Reusable icon picker with inline SVG previews.
 */

import { __ } from "@wordpress/i18n";
import { TB_ICON_LIBRARY, ToolboxIcon } from "./icon-library";

export default function IconPicker({
	label = __("Icon", "toolbox-blocks"),
	value,
	onChange,
	icons = TB_ICON_LIBRARY,
}) {
	return (
		<div className="tb-icon-picker">
			<span className="tb-icon-picker__label">{label}</span>
			<div
				className="tb-icon-picker__grid"
				role="group"
				aria-label={label}
			>
				{icons.map((icon) => {
					const isActive = icon.key === value;
					return (
						<button
							key={icon.key}
							type="button"
							className={
								"tb-icon-picker__option" +
								(isActive ? " is-active" : "")
							}
							onClick={() => onChange(icon.key)}
							aria-pressed={isActive}
							title={icon.label}
						>
							<ToolboxIcon
								iconKey={icon.key}
								className="tb-icon-picker__svg"
								title={icon.label}
							/>
							<span className="tb-icon-picker__name">
								{icon.label}
							</span>
						</button>
					);
				})}
			</div>
		</div>
	);
}
