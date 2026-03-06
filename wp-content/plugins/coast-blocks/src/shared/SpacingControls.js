/**
 * Shared Spacing controls: Margin and Padding with responsive tabs (All screens, Medium, Small).
 * Blocks opt in by passing attributes.spacing and setAttributes; attribute keys use suffix
 * '' for desktop, 'Tablet' for medium, 'Mobile' for small.
 */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, __experimentalUnitControl as UnitControl } from '@wordpress/components';

const SIDES = [
	{ key: 'Top', label: __('Top') },
	{ key: 'Right', label: __('Right') },
	{ key: 'Bottom', label: __('Bottom') },
	{ key: 'Left', label: __('Left') },
];

const BREAKPOINTS = [
	{ id: '', label: __('All screens'), icon: 'desktop' },
	{ id: 'Tablet', label: __('Medium'), icon: 'tablet' },
	{ id: 'Mobile', label: __('Small'), icon: 'smartphone' },
];

function getSpacingValue(spacing, type, side, suffix) {
	const key = type + side + suffix;
	return spacing?.[key] ?? '';
}

function setSpacingValue(setAttributes, spacing, type, side, suffix, value) {
	const key = type + side + suffix;
	setAttributes({
		spacing: {
			...spacing,
			[key]: value ?? '',
		},
	});
}

const UNITS = [
	{ value: 'px', label: 'px' },
	{ value: 'em', label: 'em' },
	{ value: 'rem', label: 'rem' },
	{ value: '%', label: '%' },
];

function SpacingInputs({ spacing, setAttributes, type, suffix }) {
	return (
		<div className="cb-spacing-inputs">
			{SIDES.map(({ key, label }) => {
				const value = getSpacingValue(spacing, type, key, suffix);
				const onChange = (v) => setSpacingValue(setAttributes, spacing, type, key, suffix, v);
				return (
					<div key={key}>
						{UnitControl ? (
							<UnitControl
								label={label}
								value={value}
								onChange={onChange}
								units={UNITS}
								min={0}
							/>
						) : (
							<TextControl
								label={label}
								value={value || ''}
								onChange={onChange}
								type="text"
								placeholder="0px"
							/>
						)}
					</div>
				);
			})}
		</div>
	);
}

export function SpacingControls({ attributes, setAttributes }) {
	const spacing = attributes.spacing || {};
	const [activeBreakpoint, setActiveBreakpoint] = useState('');

	return (
		<PanelBody title={__('Layout')} initialOpen={true}>
			<div className="cb-spacing-controls">
				<p className="cb-spacing-label">{__('Spacing')}</p>
				<div className="cb-responsive-tabs" role="tablist" aria-label={__('Screen size')}>
					{BREAKPOINTS.map((bp) => (
						<button
							key={bp.id}
							role="tab"
							aria-selected={activeBreakpoint === bp.id}
							className={'cb-responsive-tab' + (activeBreakpoint === bp.id ? ' is-active' : '')}
							onClick={() => setActiveBreakpoint(bp.id)}
						>
							{bp.label}
						</button>
					))}
				</div>
				<div className="cb-spacing-panels">
					<div className="cb-spacing-panel" role="tabpanel" hidden={activeBreakpoint !== ''}>
						<p className="cb-spacing-sublabel">{__('Padding')}</p>
						<SpacingInputs spacing={spacing} setAttributes={setAttributes} type="padding" suffix="" />
						<p className="cb-spacing-sublabel">{__('Margin')}</p>
						<SpacingInputs spacing={spacing} setAttributes={setAttributes} type="margin" suffix="" />
					</div>
					<div className="cb-spacing-panel" role="tabpanel" hidden={activeBreakpoint !== 'Tablet'}>
						<p className="cb-spacing-sublabel">{__('Padding (Medium)')}</p>
						<SpacingInputs spacing={spacing} setAttributes={setAttributes} type="padding" suffix="Tablet" />
						<p className="cb-spacing-sublabel">{__('Margin (Medium)')}</p>
						<SpacingInputs spacing={spacing} setAttributes={setAttributes} type="margin" suffix="Tablet" />
					</div>
					<div className="cb-spacing-panel" role="tabpanel" hidden={activeBreakpoint !== 'Mobile'}>
						<p className="cb-spacing-sublabel">{__('Padding (Small)')}</p>
						<SpacingInputs spacing={spacing} setAttributes={setAttributes} type="padding" suffix="Mobile" />
						<p className="cb-spacing-sublabel">{__('Margin (Small)')}</p>
						<SpacingInputs spacing={spacing} setAttributes={setAttributes} type="margin" suffix="Mobile" />
					</div>
				</div>
			</div>
		</PanelBody>
	);
}

export default SpacingControls;
