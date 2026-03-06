/**
 * Shared: Borders panel (width, style, color per side; radius per corner).
 * Respects DeviceSwitcher: Desktop edits propagate to Tablet/Mobile; Tablet/Mobile edits stick to that breakpoint.
 */

import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import {
	PanelBody,
	SelectControl,
	__experimentalUnitControl as UnitControl,
	ColorPalette,
} from '@wordpress/components';

const SIDES = [
	{ key: 'Top', label: __('Top') },
	{ key: 'Right', label: __('Right') },
	{ key: 'Bottom', label: __('Bottom') },
	{ key: 'Left', label: __('Left') },
];

const CORNERS = [
	{ key: 'TopLeft', label: __('Top Left') },
	{ key: 'TopRight', label: __('Top Right') },
	{ key: 'BottomRight', label: __('Bottom Right') },
	{ key: 'BottomLeft', label: __('Bottom Left') },
];

const BORDER_STYLES = [
	{ value: 'none', label: __('None') },
	{ value: 'solid', label: __('Solid') },
	{ value: 'dashed', label: __('Dashed') },
	{ value: 'dotted', label: __('Dotted') },
	{ value: 'double', label: __('Double') },
];

const BREAKPOINT_SUFFIXES = ['', 'Tablet', 'Mobile'];

function deviceTypeToBreakpointId(deviceType) {
	if (deviceType === 'Tablet') return 'Tablet';
	if (deviceType === 'Mobile') return 'Mobile';
	return '';
}

const UNITS = [
	{ value: 'px', label: 'px' },
	{ value: 'em', label: 'em' },
	{ value: 'rem', label: 'rem' },
	{ value: '%', label: '%' },
];

function getBorderValue(borders, key, suffix) {
	const k = key + suffix;
	return borders?.[k] ?? '';
}

/**
 * When editing on Desktop (suffix ''), propagate value to Tablet and Mobile.
 * When editing on Tablet or Mobile, only update that breakpoint.
 */
function setBorderValue(setAttributes, borders, key, suffix, value) {
	const next = { ...(borders || {}) };
	if (suffix === '') {
		next[key] = value ?? '';
		next[key + 'Tablet'] = value ?? '';
		next[key + 'Mobile'] = value ?? '';
	} else {
		next[key + suffix] = value ?? '';
	}
	setAttributes({ borders: next });
}

/**
 * Border controls for current device. Device comes from editor DeviceSwitcher.
 */
export function BorderControls({ attributes, setAttributes }) {
	const borders = attributes.borders || {};
	const editorDeviceType = useSelect((select) => {
		const editor = select('core/editor');
		if (editor?.getDeviceType) return editor.getDeviceType();
		const editPost = select('core/edit-post');
		if (editPost?.__experimentalGetPreviewDeviceType) return editPost.__experimentalGetPreviewDeviceType();
		return 'Desktop';
	}, []);
	const activeSuffix = deviceTypeToBreakpointId(editorDeviceType);

	const getVal = (key) => getBorderValue(borders, key, activeSuffix);
	const setVal = (key, value) => setBorderValue(setAttributes, borders, key, activeSuffix, value);

	return (
		<PanelBody title={__('Borders')} initialOpen={false} className="cb-border-controls">
			<div className="cb-border-section">
				<p className="cb-border-label">
					<span className="dashicons dashicons-admin-links" aria-hidden />
					{__('BORDER')}
				</p>
				{SIDES.map(({ key, label }) => (
					<div key={key} className="cb-border-row">
						<span className={`cb-border-side-icon cb-border-side-icon--${key.toLowerCase()}`} aria-hidden />
						<div className="cb-border-row-inputs">
							{UnitControl ? (
								<UnitControl
									value={getVal('border' + key + 'Width')}
									onChange={(v) => setVal('border' + key + 'Width', v)}
									units={UNITS}
									min={0}
									label={label}
									hideLabelFromVision
								/>
							) : (
								<input
									type="text"
									className="components-text-control__input"
									value={getVal('border' + key + 'Width') || ''}
									onChange={(e) => setVal('border' + key + 'Width', e.target.value)}
									placeholder="0"
									aria-label={label + ' ' + __('Width')}
								/>
							)}
							<SelectControl
								value={getVal('border' + key + 'Style') || 'none'}
								options={BORDER_STYLES}
								onChange={(v) => setVal('border' + key + 'Style', v)}
								label={__('Style')}
								hideLabelFromVision
								className="cb-border-style-select"
							/>
							<ColorPalette
								value={getVal('border' + key + 'Color')}
								onChange={(v) => setVal('border' + key + 'Color', v)}
								clearable
								enableAlpha
							/>
						</div>
					</div>
				))}
			</div>
			<div className="cb-border-section">
				<p className="cb-border-label">
					<span className="dashicons dashicons-admin-links" aria-hidden />
					{__('BORDER RADIUS')}
				</p>
				<div className="cb-border-radius-grid">
					{CORNERS.map(({ key, label }) => (
						<div key={key} className="cb-border-radius-cell">
							{UnitControl ? (
								<UnitControl
									label={label}
									value={getVal('border' + key + 'Radius')}
									onChange={(v) => setVal('border' + key + 'Radius', v)}
									units={UNITS}
									min={0}
								/>
							) : (
								<label>
									<span className="cb-border-radius-label">{label}</span>
									<input
										type="text"
										className="components-text-control__input"
										value={getVal('border' + key + 'Radius') || ''}
										onChange={(e) => setVal('border' + key + 'Radius', e.target.value)}
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
