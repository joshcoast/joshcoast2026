/**
 * Shared: device switcher (above block title) + spacing controls.
 * Screen size affects all block settings and the editor canvas width (like GenerateBlocks).
 */

import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { PanelBody, TextControl, __experimentalUnitControl as UnitControl } from '@wordpress/components';

const SIDES = [
	{ key: 'Top', label: __('Top') },
	{ key: 'Right', label: __('Right') },
	{ key: 'Bottom', label: __('Bottom') },
	{ key: 'Left', label: __('Left') },
];

const BREAKPOINTS = [
	{ id: '', label: __('Desktop'), icon: 'desktop' },
	{ id: 'Tablet', label: __('Tablet'), icon: 'tablet' },
	{ id: 'Mobile', label: __('Mobile'), icon: 'smartphone' },
];

function breakpointIdToDeviceType(id) {
	if (id === 'Tablet') return 'Tablet';
	if (id === 'Mobile') return 'Mobile';
	return 'Desktop';
}

function deviceTypeToBreakpointId(deviceType) {
	if (deviceType === 'Tablet') return 'Tablet';
	if (deviceType === 'Mobile') return 'Mobile';
	return '';
}

/**
 * Device switcher: icon-only row above block settings. Changes editor preview width and which breakpoint all settings apply to.
 */
export function DeviceSwitcher() {
	const editorDeviceType = useSelect((select) => {
		const editor = select('core/editor');
		if (editor?.getDeviceType) return editor.getDeviceType();
		const editPost = select('core/edit-post');
		if (editPost?.__experimentalGetPreviewDeviceType) return editPost.__experimentalGetPreviewDeviceType();
		return 'Desktop';
	}, []);
	const editorDispatch = useDispatch('core/editor');
	const editPostDispatch = useDispatch('core/edit-post');
	const setEditorDeviceType = editorDispatch?.setDeviceType ?? editPostDispatch?.__experimentalSetPreviewDeviceType;
	const activeBreakpoint = deviceTypeToBreakpointId(editorDeviceType);

	return (
		<div className="cb-device-switcher" role="tablist" aria-label={__('Screen size')}>
			{BREAKPOINTS.map((bp) => (
				<button
					key={bp.id || 'desktop'}
					type="button"
					role="tab"
					aria-selected={activeBreakpoint === bp.id}
					className={'cb-device-switcher__button' + (activeBreakpoint === bp.id ? ' is-active' : '')}
					onClick={() => setEditorDeviceType && setEditorDeviceType(breakpointIdToDeviceType(bp.id))}
					title={bp.label}
				>
					<span className={'dashicons dashicons-' + bp.icon} aria-hidden />
				</button>
			))}
		</div>
	);
}

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

/**
 * Spacing panel for the current device only. Device is set by DeviceSwitcher above.
 */
export function SpacingControls({ attributes, setAttributes }) {
	const spacing = attributes.spacing || {};
	const editorDeviceType = useSelect((select) => {
		const editor = select('core/editor');
		if (editor?.getDeviceType) return editor.getDeviceType();
		const editPost = select('core/edit-post');
		if (editPost?.__experimentalGetPreviewDeviceType) return editPost.__experimentalGetPreviewDeviceType();
		return 'Desktop';
	}, []);
	const activeBreakpoint = deviceTypeToBreakpointId(editorDeviceType);
	const suffix = activeBreakpoint; // '' | 'Tablet' | 'Mobile'

	return (
		<PanelBody title={__('Layout')} initialOpen={true}>
			<div className="cb-spacing-controls">
				<p className="cb-spacing-sublabel">{__('Padding')}</p>
				<SpacingInputs spacing={spacing} setAttributes={setAttributes} type="padding" suffix={suffix} />
				<p className="cb-spacing-sublabel">{__('Margin')}</p>
				<SpacingInputs spacing={spacing} setAttributes={setAttributes} type="margin" suffix={suffix} />
			</div>
		</PanelBody>
	);
}

export default SpacingControls;
