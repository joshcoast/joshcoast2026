/**
 * StylesPanel – responsive CSS controls (layout, spacing, typography, etc.).
 *
 * Sections:
 *   Layout, Sizing, Spacing, Typography, Backgrounds, Borders,
 *   Position, Effects, Media, Lists, Pointer Events, Cursor,
 *   Fill Color, Stroke Color
 */

import { useState, useRef, useCallback, useMemo, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	SelectControl,
	__experimentalUnitControl as UnitControl,
	RangeControl,
	ToggleControl,
	TextControl,
	ColorPalette,
	Popover,
	Button,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import DeviceSwitcher from './DeviceSwitcher';
import MainHoverTabs from './MainHoverTabs';
import useEditorDevice from '../hooks/useEditorDevice';
import { getStyleValue, setStyleValue } from '../utils/generate-css';

/** Dashicon element for panel section titles (improves visual scanning). */
function SectionIcon( { icon } ) {
	return (
		<span
			className={ 'dashicons dashicons-' + icon }
			style={ { fontSize: 20, width: 20, height: 20 } }
			aria-hidden="true"
		/>
	);
}

const UNITS = [
	{ value: 'px', label: 'px' },
	{ value: 'em', label: 'em' },
	{ value: 'rem', label: 'rem' },
	{ value: '%', label: '%' },
	{ value: 'vh', label: 'vh' },
	{ value: 'vw', label: 'vw' },
];

const SIDES = [
	{ key: 'Top', label: __( 'Top' ) },
	{ key: 'Right', label: __( 'Right' ) },
	{ key: 'Bottom', label: __( 'Bottom' ) },
	{ key: 'Left', label: __( 'Left' ) },
];

const CORNERS = [
	{ key: 'TopLeft', label: __( 'TL' ) },
	{ key: 'TopRight', label: __( 'TR' ) },
	{ key: 'BottomRight', label: __( 'BR' ) },
	{ key: 'BottomLeft', label: __( 'BL' ) },
];

// ─── Small helpers ────────────────────────────────────────────────────────────

function UCV( { label, value, onChange, hideLabelFromVision = false } ) {
	return UnitControl ? (
		<UnitControl
			label={ label }
			value={ value || '' }
			onChange={ ( v ) => onChange( v ?? '' ) }
			units={ UNITS }
			hideLabelFromVision={ hideLabelFromVision }
		/>
	) : (
		<TextControl label={ label } value={ value || '' } onChange={ onChange } placeholder="0" />
	);
}

/** Four-sided dimension control with link toggle */
function DimensionControl( { label, propPrefix, get, set, setMultiple } ) {
	const [ linked, setLinked ] = useState( true );
	return (
		<div className="tb-dimension-control">
			<div className="tb-dimension-control__header">
				<span className="tb-dimension-control__label">{ label }</span>
				<button
					type="button"
					className={ 'tb-dimension-control__link-toggle' + ( linked ? ' is-linked' : '' ) }
					onClick={ () => setLinked( ( v ) => ! v ) }
					title={ linked ? __( 'Unlink sides' ) : __( 'Link sides' ) }
					aria-label={ linked ? __( 'Linked' ) : __( 'Unlinked' ) }
				>
					<span className={ 'dashicons dashicons-admin-' + ( linked ? 'links' : 'link' ) } />
				</button>
			</div>
			<div className="tb-dimension-control__inputs">
				{ SIDES.map( ( { key, label: sideLabel } ) => (
					<UCV
						key={ key }
						label={ sideLabel }
						value={ get( propPrefix + key ) }
						onChange={ ( v ) => {
							if ( linked ) {
								setMultiple( SIDES.map( ( { key: k } ) => [ propPrefix + k, v ] ) );
							} else {
								set( propPrefix + key, v );
							}
						} }
						hideLabelFromVision={ false }
					/>
				) ) }
			</div>
		</div>
	);
}

/** Color swatch + popover picker */
function InlineColorPicker( { label, value, onChange, hideLabel = false } ) {
	const [ open, setOpen ] = useState( false );
	const colors = useSelect( ( select ) =>
		select( 'core/block-editor' )?.getSettings?.()?.colors || [], [] );

	return (
		<div className={ 'tb-inline-color' + ( hideLabel ? ' tb-inline-color--no-field-label' : '' ) }>
			{ ! hideLabel && <span className="tb-inline-color__label">{ label }</span> }
			<button
				type="button"
				className="tb-inline-color__swatch"
				style={ { backgroundColor: value || 'transparent' } }
				onClick={ () => setOpen( ( v ) => ! v ) }
				title={ label }
				aria-label={ label }
			/>
			{ open && (
				<Popover placement="bottom-start" onClose={ () => setOpen( false ) }>
					<div className="tb-color-popover">
						<ColorPalette
							value={ value }
							onChange={ ( v ) => { onChange( v || '' ); setOpen( false ); } }
							colors={ colors }
							clearable
							enableAlpha
						/>
					</div>
				</Popover>
			) }
		</div>
	);
}

/** Border style SVG picker */
const BORDER_STYLES = [
	{ value: 'none',   label: __( 'None' ) },
	{ value: 'solid',  label: __( 'Solid' ) },
	{ value: 'dashed', label: __( 'Dashed' ) },
	{ value: 'dotted', label: __( 'Dotted' ) },
	{ value: 'double', label: __( 'Double' ) },
];

function BorderStyleLine( { style } ) {
	if ( style === 'none' ) return <svg width="32" height="16" />;
	if ( style === 'double' ) return (
		<svg width="32" height="16" viewBox="0 0 32 16">
			<line x1="2" y1="5" x2="30" y2="5" stroke="#1e1e1e" strokeWidth="1.5" />
			<line x1="2" y1="11" x2="30" y2="11" stroke="#1e1e1e" strokeWidth="1.5" />
		</svg>
	);
	const da = style === 'dashed' ? '4,2' : style === 'dotted' ? '1,3' : undefined;
	return (
		<svg width="32" height="16" viewBox="0 0 32 16">
			<line x1="2" y1="8" x2="30" y2="8" stroke="#1e1e1e" strokeWidth="2"
				strokeDasharray={ da } strokeLinecap="round" />
		</svg>
	);
}

function BorderStylePicker( { value, onChange } ) {
	const [ open, setOpen ] = useState( false );
	return (
		<div className="tb-border-style-picker">
			<button type="button" className="tb-border-style-picker__trigger"
				onClick={ () => setOpen( ( v ) => ! v ) } title={ __( 'Border Style' ) }>
				<BorderStyleLine style={ value || 'none' } />
			</button>
			{ open && (
				<Popover placement="bottom-start" onClose={ () => setOpen( false ) }>
					<div className="tb-border-style-picker__popover">
						{ BORDER_STYLES.map( ( s ) => (
							<button key={ s.value } type="button"
								className={ 'tb-border-style-picker__option' + ( value === s.value ? ' is-active' : '' ) }
								onClick={ () => { onChange( s.value ); setOpen( false ); } }
								title={ s.label }>
								<BorderStyleLine style={ s.value } />
								<span>{ s.label }</span>
							</button>
						) ) }
					</div>
				</Popover>
			) }
		</div>
	);
}

// ─── Layout section helpers ────────────────────────────────────────────────────

/** Segmented button group (icon or text). */
function SegmentedGroup( { label, options, value, onChange, className = '' } ) {
	return (
		<div className={ 'tb-layout-control' + ( className ? ' ' + className : '' ) }>
			<span className="tb-layout-control__label">{ label }</span>
			<div className="tb-layout-control__segmented" role="group" aria-label={ label }>
				{ options.map( ( opt ) => (
					<button
						key={ opt.value }
						type="button"
						className={ 'tb-layout-control__btn' + ( value === opt.value ? ' is-active' : '' ) }
						onClick={ () => onChange( opt.value ) }
						title={ opt.label }
						aria-label={ opt.label }
						aria-pressed={ value === opt.value }
					>
						{ opt.icon ? opt.icon : opt.label }
					</button>
				) ) }
			</div>
		</div>
	);
}

// Align-items icons (cross-axis: start, center, end, stretch, baseline)
const ALIGN_ITEMS_OPTIONS = [
	{ value: 'flex-start', label: __( 'Align start' ), icon: (
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="2" width="5" height="5" rx="0.5"/><rect x="9" y="2" width="5" height="5" rx="0.5"/><rect x="16" y="2" width="2" height="5" rx="0.5"/></svg>
	) },
	{ value: 'center', label: __( 'Align center' ), icon: (
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="7.5" width="5" height="5" rx="0.5"/><rect x="9" y="7.5" width="5" height="5" rx="0.5"/><rect x="16" y="7.5" width="2" height="5" rx="0.5"/></svg>
	) },
	{ value: 'flex-end', label: __( 'Align end' ), icon: (
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="13" width="5" height="5" rx="0.5"/><rect x="9" y="13" width="5" height="5" rx="0.5"/><rect x="16" y="13" width="2" height="5" rx="0.5"/></svg>
	) },
	{ value: 'stretch', label: __( 'Stretch' ), icon: (
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="2" width="5" height="16" rx="0.5"/><rect x="9" y="2" width="5" height="16" rx="0.5"/><rect x="16" y="2" width="2" height="16" rx="0.5"/></svg>
	) },
	{ value: 'baseline', label: __( 'Baseline' ), icon: (
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"><line x1="2" y1="10" x2="7" y2="10"/><line x1="9" y1="10" x2="14" y2="10"/><line x1="16" y1="10" x2="18" y2="10"/><rect x="2" y="5" width="5" height="5" rx="0.5"/><rect x="9" y="7" width="5" height="5" rx="0.5"/><rect x="16" y="8" width="2" height="5" rx="0.5"/></svg>
	) },
];

// Justify-content icons (main-axis): vertical reference line(s) + solid rectangles
const JUSTIFY_OPTIONS = [
	{ value: 'flex-start', label: __( 'Justify start' ), icon: (
		<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><rect x="2" y="7" width="4" height="10" rx="1"/><rect x="8" y="7" width="4" height="10" rx="1"/><line x1="20" y1="4" x2="20" y2="20" stroke="currentColor" strokeWidth="1.5"/></svg>
	) },
	{ value: 'center', label: __( 'Justify center' ), icon: (
		<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><line x1="12" y1="4" x2="12" y2="20" stroke="currentColor" strokeWidth="1.5"/><rect x="4" y="7" width="4" height="10" rx="1"/><rect x="16" y="7" width="4" height="10" rx="1"/></svg>
	) },
	{ value: 'flex-end', label: __( 'Justify end' ), icon: (
		<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><line x1="4" y1="4" x2="4" y2="20" stroke="currentColor" strokeWidth="1.5"/><rect x="6" y="7" width="4" height="10" rx="1"/><rect x="12" y="7" width="4" height="10" rx="1"/></svg>
	) },
	{ value: 'space-between', label: __( 'Space between' ), icon: (
		<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><line x1="3" y1="4" x2="3" y2="20" stroke="currentColor" strokeWidth="1.5"/><line x1="21" y1="4" x2="21" y2="20" stroke="currentColor" strokeWidth="1.5"/><rect x="6" y="7" width="4" height="10" rx="1"/><rect x="14" y="7" width="4" height="10" rx="1"/></svg>
	) },
	{ value: 'space-around', label: __( 'Space around' ), icon: (
		<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><line x1="2" y1="4" x2="2" y2="20" stroke="currentColor" strokeWidth="1.5"/><line x1="12" y1="4" x2="12" y2="20" stroke="currentColor" strokeWidth="1.5"/><line x1="22" y1="4" x2="22" y2="20" stroke="currentColor" strokeWidth="1.5"/><rect x="5" y="7" width="3" height="10" rx="1"/><rect x="16" y="7" width="3" height="10" rx="1"/></svg>
	) },
];

// ─── Section: Layout ──────────────────────────────────────────────────────────

function LayoutSection( { get, set } ) {
	const display = get( 'display' );
	const isFlex = display === 'flex' || display === 'inline-flex';
	const flexDirection = get( 'flexDirection' ) || 'row';
	const flexWrap = get( 'flexWrap' ) || 'nowrap';
	const alignItems = get( 'alignItems' ) || '';
	const justifyContent = get( 'justifyContent' ) || '';

	const directionOptions = [
		{ value: 'row', label: __( 'Row' ) },
		{ value: 'column', label: __( 'Column' ) },
		{ value: 'row-reverse', label: __( 'Row reverse' ), icon: (
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M4 8h8M8 12l4-4-4-4"/></svg>
		) },
	];
	const wrapOptions = [
		{ value: 'nowrap', label: __( 'No Wrap' ) },
		{ value: 'wrap', label: __( 'Wrap' ) },
		{ value: 'wrap-reverse', label: __( 'Reverse Wrap' ) },
	];

	const overflowOptions = [
		{ value: '', label: __( 'Default' ) },
		{ value: 'visible', label: 'visible' },
		{ value: 'hidden', label: 'hidden' },
		{ value: 'scroll', label: 'scroll' },
		{ value: 'auto', label: 'auto' },
	];
	const positionOptions = [
		{ value: '', label: __( 'Default' ) },
		{ value: 'static', label: 'static' },
		{ value: 'relative', label: 'relative' },
		{ value: 'absolute', label: 'absolute' },
		{ value: 'fixed', label: 'fixed' },
		{ value: 'sticky', label: 'sticky' },
	];

	return (
		<PanelBody title={ __( 'Layout' ) } icon={ <SectionIcon icon="layout" /> } initialOpen={ false }>
			<div className="tb-layout-control">
				<span className="tb-layout-control__label">{ __( 'DISPLAY' ) }</span>
				<SelectControl
					value={ display || '' }
					options={ [
						{ value: '',             label: __( '—' ) },
						{ value: 'block',        label: 'Block' },
						{ value: 'flex',         label: 'Flex' },
						{ value: 'grid',         label: 'Grid' },
						{ value: 'inline',       label: 'inline' },
						{ value: 'inline-block', label: 'inline-block' },
						{ value: 'inline-flex',  label: 'inline-flex' },
						{ value: 'none',         label: 'none' },
					] }
					onChange={ ( v ) => set( 'display', v ) }
					hideLabelFromVision
					__nextHasNoMarginBottom
				/>
			</div>
			{ isFlex && (
				<>
					<SegmentedGroup
						label={ __( 'DIRECTION' ) }
						options={ directionOptions }
						value={ flexDirection }
						onChange={ ( v ) => set( 'flexDirection', v ) }
					/>
					<SegmentedGroup
						label={ __( 'ALIGN ITEMS' ) }
						options={ ALIGN_ITEMS_OPTIONS }
						value={ alignItems }
						onChange={ ( v ) => set( 'alignItems', v ) }
					/>
					<SegmentedGroup
						label={ __( 'JUSTIFY CONTENT' ) }
						options={ JUSTIFY_OPTIONS }
						value={ justifyContent }
						onChange={ ( v ) => set( 'justifyContent', v ) }
						className="tb-layout-control--justify"
					/>
					<SegmentedGroup
						label={ __( 'WRAP' ) }
						options={ wrapOptions }
						value={ flexWrap }
						onChange={ ( v ) => set( 'flexWrap', v ) }
					/>
					<div className="tb-layout-control tb-layout-control--gap-row">
						<div className="tb-layout-control__gap-label-row">
							<span className="tb-layout-control__label">{ __( 'COLUMN GAP' ) }</span>
							<span className="tb-layout-control__label">{ __( 'ROW GAP' ) }</span>
						</div>
						<div className="tb-layout-control__gap-inputs">
							<UCV
								label={ __( 'Column gap' ) }
								value={ get( 'columnGap' ) }
								onChange={ ( v ) => set( 'columnGap', v ) }
								hideLabelFromVision
							/>
							<UCV
								label={ __( 'Row gap' ) }
								value={ get( 'rowGap' ) }
								onChange={ ( v ) => set( 'rowGap', v ) }
								hideLabelFromVision
							/>
						</div>
					</div>
				</>
			) }
			{ display === 'grid' && (
				<div className="tb-layout-control tb-layout-control--gap-row">
					<div className="tb-layout-control__gap-label-row">
						<span className="tb-layout-control__label">{ __( 'COLUMN GAP' ) }</span>
						<span className="tb-layout-control__label">{ __( 'ROW GAP' ) }</span>
					</div>
					<div className="tb-layout-control__gap-inputs">
						<UCV
							label={ __( 'Column gap' ) }
							value={ get( 'columnGap' ) }
							onChange={ ( v ) => set( 'columnGap', v ) }
							hideLabelFromVision
						/>
						<UCV
							label={ __( 'Row gap' ) }
							value={ get( 'rowGap' ) }
							onChange={ ( v ) => set( 'rowGap', v ) }
							hideLabelFromVision
						/>
					</div>
				</div>
			) }
			<div className="tb-layout-control">
				<span className="tb-layout-control__label">{ __( 'POSITION' ) }</span>
				<SelectControl
					value={ get( 'position' ) || '' }
					options={ positionOptions }
					onChange={ ( v ) => set( 'position', v ) }
					hideLabelFromVision
					__nextHasNoMarginBottom
				/>
			</div>
			<div className="tb-layout-control tb-layout-control--overflow-row">
				<div className="tb-layout-control__gap-label-row">
					<span className="tb-layout-control__label">{ __( 'OVERFLOW-X' ) }</span>
					<span className="tb-layout-control__label">{ __( 'OVERFLOW-Y' ) }</span>
				</div>
				<div className="tb-layout-control__overflow-inputs">
					<SelectControl
						value={ get( 'overflowX' ) || '' }
						options={ overflowOptions }
						onChange={ ( v ) => set( 'overflowX', v ) }
						hideLabelFromVision
						__nextHasNoMarginBottom
					/>
					<SelectControl
						value={ get( 'overflowY' ) || '' }
						options={ overflowOptions }
						onChange={ ( v ) => set( 'overflowY', v ) }
						hideLabelFromVision
						__nextHasNoMarginBottom
					/>
				</div>
			</div>
			<div className="tb-layout-control">
				<span className="tb-layout-control__label">{ __( 'Z-INDEX' ) }</span>
				<TextControl
					value={ get( 'zIndex' ) || '' }
					onChange={ ( v ) => set( 'zIndex', v ) }
					type="number"
					placeholder="0"
					hideLabelFromVision
					__nextHasNoMarginBottom
				/>
			</div>
		</PanelBody>
	);
}

// ─── Section: Sizing ──────────────────────────────────────────────────────────

function SizingSection( { get, set } ) {
	return (
		<PanelBody title={ __( 'Sizing' ) } icon={ <SectionIcon icon="image-crop" /> } initialOpen={ false }>
			<div className="tb-sizing-control">
				<p className="tb-sizing-control__label">{ __( 'WIDTH & HEIGHT' ) }</p>
				<div className="tb-sizing-control__row">
					<UCV label={ __( 'Width' ) }  value={ get( 'width' ) }  onChange={ ( v ) => set( 'width', v ) } />
					<UCV label={ __( 'Height' ) } value={ get( 'height' ) } onChange={ ( v ) => set( 'height', v ) } />
				</div>
			</div>
			<div className="tb-sizing-control">
				<p className="tb-sizing-control__label">{ __( 'MIN' ) }</p>
				<div className="tb-sizing-control__row">
					<UCV label={ __( 'Min Width' ) }  value={ get( 'minWidth' ) }  onChange={ ( v ) => set( 'minWidth', v ) } />
					<UCV label={ __( 'Min Height' ) } value={ get( 'minHeight' ) } onChange={ ( v ) => set( 'minHeight', v ) } />
				</div>
			</div>
			<div className="tb-sizing-control">
				<p className="tb-sizing-control__label">{ __( 'MAX' ) }</p>
				<div className="tb-sizing-control__row">
					<UCV label={ __( 'Max Width' ) }  value={ get( 'maxWidth' ) }  onChange={ ( v ) => set( 'maxWidth', v ) } />
					<UCV label={ __( 'Max Height' ) } value={ get( 'maxHeight' ) } onChange={ ( v ) => set( 'maxHeight', v ) } />
				</div>
			</div>
		</PanelBody>
	);
}

// ─── Section: Spacing ─────────────────────────────────────────────────────────

function SpacingSection( { get, set, setMultiple } ) {
	return (
		<PanelBody title={ __( 'Spacing' ) } icon={ <SectionIcon icon="align-center" /> } initialOpen={ false }>
			<DimensionControl label={ __( 'Padding' ) } propPrefix="padding" get={ get } set={ set } setMultiple={ setMultiple } />
			<DimensionControl label={ __( 'Margin' ) }  propPrefix="margin"  get={ get } set={ set } setMultiple={ setMultiple } />
		</PanelBody>
	);
}

// ─── Section: Typography ──────────────────────────────────────────────────────

function TypographySection( { get, set } ) {
	return (
		<PanelBody title={ __( 'Typography' ) } icon={ <SectionIcon icon="editor-textcolor" /> } initialOpen={ false }>
			<TextControl
				label={ __( 'Font Family' ) }
				value={ get( 'fontFamily' ) || '' }
				onChange={ ( v ) => set( 'fontFamily', v ) }
				placeholder={ __( 'inherit' ) }
			/>
			<UCV label={ __( 'Font Size' ) }    value={ get( 'fontSize' ) }    onChange={ ( v ) => set( 'fontSize', v ) } />
			<SelectControl
				label={ __( 'Font Weight' ) }
				value={ get( 'fontWeight' ) || '' }
				options={ [
					{ value: '', label: __( '—' ) },
					{ value: '100', label: '100 – Thin' },
					{ value: '200', label: '200 – Extra Light' },
					{ value: '300', label: '300 – Light' },
					{ value: '400', label: '400 – Normal' },
					{ value: '500', label: '500 – Medium' },
					{ value: '600', label: '600 – Semi Bold' },
					{ value: '700', label: '700 – Bold' },
					{ value: '800', label: '800 – Extra Bold' },
					{ value: '900', label: '900 – Black' },
				] }
				onChange={ ( v ) => set( 'fontWeight', v ) }
			/>
			<SelectControl
				label={ __( 'Font Style' ) }
				value={ get( 'fontStyle' ) || '' }
				options={ [
					{ value: '', label: __( '—' ) },
					{ value: 'normal', label: 'normal' },
					{ value: 'italic', label: 'italic' },
				] }
				onChange={ ( v ) => set( 'fontStyle', v ) }
			/>
			<UCV label={ __( 'Line Height' ) }    value={ get( 'lineHeight' ) }    onChange={ ( v ) => set( 'lineHeight', v ) } />
			<UCV label={ __( 'Letter Spacing' ) } value={ get( 'letterSpacing' ) } onChange={ ( v ) => set( 'letterSpacing', v ) } />
			<SelectControl
				label={ __( 'Text Align' ) }
				value={ get( 'textAlign' ) || '' }
				options={ [
					{ value: '',        label: __( '—' ) },
					{ value: 'left',    label: 'left' },
					{ value: 'center',  label: 'center' },
					{ value: 'right',   label: 'right' },
					{ value: 'justify', label: 'justify' },
				] }
				onChange={ ( v ) => set( 'textAlign', v ) }
			/>
			<SelectControl
				label={ __( 'Text Transform' ) }
				value={ get( 'textTransform' ) || '' }
				options={ [
					{ value: '',           label: __( '—' ) },
					{ value: 'none',       label: 'none' },
					{ value: 'uppercase',  label: 'uppercase' },
					{ value: 'lowercase',  label: 'lowercase' },
					{ value: 'capitalize', label: 'capitalize' },
				] }
				onChange={ ( v ) => set( 'textTransform', v ) }
			/>
			<SelectControl
				label={ __( 'Text Decoration' ) }
				value={ get( 'textDecoration' ) || '' }
				options={ [
					{ value: '',             label: __( '—' ) },
					{ value: 'none',         label: 'none' },
					{ value: 'underline',    label: 'underline' },
					{ value: 'overline',     label: 'overline' },
					{ value: 'line-through', label: 'line-through' },
				] }
				onChange={ ( v ) => set( 'textDecoration', v ) }
			/>
			<InlineColorPicker label={ __( 'Color' ) } value={ get( 'color' ) } onChange={ ( v ) => set( 'color', v ) } />
			<SelectControl
				label={ __( 'White Space' ) }
				value={ get( 'whiteSpace' ) || '' }
				options={ [
					{ value: '',        label: __( '—' ) },
					{ value: 'normal',  label: 'normal' },
					{ value: 'nowrap',  label: 'nowrap' },
					{ value: 'pre',     label: 'pre' },
					{ value: 'pre-wrap', label: 'pre-wrap' },
					{ value: 'pre-line', label: 'pre-line' },
				] }
				onChange={ ( v ) => set( 'whiteSpace', v ) }
			/>
			<SelectControl
				label={ __( 'Word Break' ) }
				value={ get( 'wordBreak' ) || '' }
				options={ [
					{ value: '',           label: __( '—' ) },
					{ value: 'normal',     label: 'normal' },
					{ value: 'break-all',  label: 'break-all' },
					{ value: 'keep-all',   label: 'keep-all' },
					{ value: 'break-word', label: 'break-word' },
				] }
				onChange={ ( v ) => set( 'wordBreak', v ) }
			/>
		</PanelBody>
	);
}

// ─── Background image: CSS `url(...)` ↔ editable URL string ────────────────────

/**
 * @param {string} value Stored `backgroundImage` (e.g. url("https://…")).
 * @return {string} Raw URL for preview / text field, or ''.
 */
function getUrlFromBackgroundImageValue( value ) {
	if ( ! value || typeof value !== 'string' ) {
		return '';
	}
	const v = value.trim();
	const m = v.match( /^url\s*\(\s*(.+)\s*\)$/is );
	if ( m ) {
		let inner = m[ 1 ].trim();
		if (
			( inner.startsWith( '"' ) && inner.endsWith( '"' ) ) ||
			( inner.startsWith( "'" ) && inner.endsWith( "'" ) )
		) {
			inner = inner.slice( 1, -1 );
		}
		return inner;
	}
	if ( /^https?:\/\//i.test( v ) ) {
		return v;
	}
	return '';
}

/**
 * @param {string} rawUrl Absolute image URL.
 * @return {string} Safe `background-image` value.
 */
function toBackgroundImageDeclaration( rawUrl ) {
	if ( ! rawUrl || ! String( rawUrl ).trim() ) {
		return '';
	}
	const u = String( rawUrl ).trim();
	const escaped = u.replace( /\\/g, '\\\\' ).replace( /"/g, '\\"' );
	return `url("${ escaped }")`;
}

/**
 * Split a CSS-like argument list at top-level commas (respects parentheses).
 *
 * @param {string} str
 * @return {string[]}
 */
function splitTopLevelCommas( str ) {
	const parts = [];
	let depth = 0;
	let start = 0;
	for ( let i = 0; i < str.length; i += 1 ) {
		const ch = str[ i ];
		if ( ch === '(' ) {
			depth += 1;
		} else if ( ch === ')' ) {
			depth -= 1;
		} else if ( ch === ',' && depth === 0 ) {
			parts.push( str.slice( start, i ).trim() );
			start = i + 1;
		}
	}
	parts.push( str.slice( start ).trim() );
	return parts;
}

/**
 * Parse exactly `linear-gradient(NDEG, color1, color2)` produced by our builder or matching hand-entered syntax.
 *
 * @param {string} value Trimmed CSS `background` fragment.
 * @return {Object|null} With angle, color1, color2, or null.
 */
function parseSimpleTwoStopLinearGradient( value ) {
	if ( ! value || typeof value !== 'string' ) {
		return null;
	}
	const v = value.trim();
	if ( ! v.toLowerCase().startsWith( 'linear-gradient(' ) ) {
		return null;
	}
	const inner = v.slice( 'linear-gradient('.length ).trim();
	if ( ! inner.endsWith( ')' ) ) {
		return null;
	}
	const body = inner.slice( 0, -1 ).trim();
	const parts = splitTopLevelCommas( body );
	if ( parts.length !== 3 ) {
		return null;
	}
	const degM = parts[ 0 ].match( /^(-?[\d.]+)deg$/i );
	if ( ! degM ) {
		return null;
	}
	const angle = parseFloat( degM[ 1 ] );
	if ( Number.isNaN( angle ) ) {
		return null;
	}
	return {
		angle,
		color1: parts[ 1 ].trim(),
		color2: parts[ 2 ].trim(),
	};
}

/**
 * @param {number|string} angleDeg
 * @param {string}        color1
 * @param {string}        color2
 * @return {string} CSS `linear-gradient(...)` or '' when invisible.
 */
function buildSimpleTwoStopLinearGradient( angleDeg, color1, color2 ) {
	const c1 = ( color1 || '' ).trim() || 'transparent';
	const c2 = ( color2 || '' ).trim() || 'transparent';
	if ( c1 === 'transparent' && c2 === 'transparent' ) {
		return '';
	}
	const raw = typeof angleDeg === 'number' ? angleDeg : parseFloat( angleDeg );
	const clamped = Math.round( Number.isFinite( raw ) ? Math.min( 360, Math.max( 0, raw ) ) : 180 );
	return `linear-gradient(${ clamped }deg, ${ c1 }, ${ c2 })`;
}

/** Keyword pairs for matching two-keyword background-position (focal ring). */
const BACKGROUND_POSITION_PRESETS = [
	[ 'left top', 'center top', 'right top' ],
	[ 'left center', 'center center', 'right center' ],
	[ 'left bottom', 'center bottom', 'right bottom' ],
];

/**
 * Stable key for two-keyword positions: `top left` and `left top` both → `left top`.
 *
 * @param {string} phrase
 * @return {string}
 */
function backgroundPositionKeywordKey( phrase ) {
	return phrase
		.trim()
		.toLowerCase()
		.replace( /\s+/g, ' ' )
		.split( ' ' )
		.filter( Boolean )
		.sort()
		.join( ' ' );
}

/**
 * @param {string} value Current CSS background-position.
 * @return {string|null} Canonical keyword pair for focal ring, or null if not a two-keyword preset.
 */
function matchBackgroundPositionPreset( value ) {
	if ( ! value || typeof value !== 'string' ) {
		return null;
	}
	const t = value.trim().toLowerCase().replace( /\s+/g, ' ' );
	if ( ! t ) {
		return null;
	}
	/* Single "center" is valid CSS shorthand for center / center. */
	if ( t === 'center' ) {
		return 'center center';
	}
	/* Lengths, percentages, or calc — not a nine-point preset. */
	if ( /[%]|px|rem|em|vw|vh|deg|calc\s*\(/.test( t ) || /\d/.test( t ) ) {
		return null;
	}
	const key = backgroundPositionKeywordKey( t );
	if ( ! key ) {
		return null;
	}
	for ( let r = 0; r < 3; r++ ) {
		for ( let c = 0; c < 3; c++ ) {
			const preset = BACKGROUND_POSITION_PRESETS[ r ][ c ];
			if ( backgroundPositionKeywordKey( preset ) === key ) {
				return preset;
			}
		}
	}
	return null;
}

/** Keyword presets → approximate % for focal display (CSS alignment points). */
const PRESET_TO_PERCENT = {
	'left top': { x: 0, y: 0 },
	'center top': { x: 50, y: 0 },
	'right top': { x: 100, y: 0 },
	'left center': { x: 0, y: 50 },
	'center center': { x: 50, y: 50 },
	'right center': { x: 100, y: 50 },
	'left bottom': { x: 0, y: 100 },
	'center bottom': { x: 50, y: 100 },
	'right bottom': { x: 100, y: 100 },
};

/**
 * Parse background-position into 0–100 % pair for focal UI (best effort).
 *
 * @param {string} position
 * @return {{ x: number, y: number }}
 */
function parseBackgroundPositionToFocalPercent( position ) {
	if ( ! position || typeof position !== 'string' ) {
		return { x: 50, y: 50 };
	}
	const t = position.trim();
	const pair = t.match( /^([+-]?[\d.]+)%\s+([+-]?[\d.]+)%$/ );
	if ( pair ) {
		return {
			x: Math.round( Math.min( 100, Math.max( 0, parseFloat( pair[ 1 ] ) ) ) ),
			y: Math.round( Math.min( 100, Math.max( 0, parseFloat( pair[ 2 ] ) ) ) ),
		};
	}
	const tl = t.toLowerCase();
	if ( tl === 'center' ) {
		return { x: 50, y: 50 };
	}
	const matched = matchBackgroundPositionPreset( t );
	if ( matched && PRESET_TO_PERCENT[ matched ] ) {
		return PRESET_TO_PERCENT[ matched ];
	}
	return { x: 50, y: 50 };
}

/**
 * If value is two percentages, round each to a whole number (0–100).
 *
 * @param {string} value
 * @return {string}
 */
function roundPercentPairValue( value ) {
	if ( ! value || typeof value !== 'string' ) {
		return value;
	}
	const t = value.trim();
	const m = t.match( /^([+-]?[\d.]+)%\s+([+-]?[\d.]+)%$/ );
	if ( ! m ) {
		return value;
	}
	const x = Math.round( Math.min( 100, Math.max( 0, parseFloat( m[ 1 ] ) ) ) );
	const y = Math.round( Math.min( 100, Math.max( 0, parseFloat( m[ 2 ] ) ) ) );
	return `${ x }% ${ y }%`;
}

/**
 * Clickable image thumbnail with focal ring; writes percentage background-position.
 *
 * @param {Object} props
 * @param {string} props.imageUrl
 * @param {string} props.positionValue
 * @param {Function} props.onPickPercent (css: string) => void
 */
function BackgroundFocusThumbnail( { imageUrl, positionValue, onPickPercent } ) {
	const frameRef = useRef( null );
	const focal = useMemo(
		() => parseBackgroundPositionToFocalPercent( positionValue ),
		[ positionValue ]
	);

	const setFromEvent = useCallback(
		( clientX, clientY ) => {
			const el = frameRef.current;
			if ( ! el ) {
				return;
			}
			const rect = el.getBoundingClientRect();
			if ( rect.width < 1 || rect.height < 1 ) {
				return;
			}
			const nx = ( ( clientX - rect.left ) / rect.width ) * 100;
			const ny = ( ( clientY - rect.top ) / rect.height ) * 100;
			const clamp = ( v ) => Math.min( 100, Math.max( 0, v ) );
			const rx = Math.round( clamp( nx ) );
			const ry = Math.round( clamp( ny ) );
			onPickPercent( `${ rx }% ${ ry }%` );
		},
		[ onPickPercent ]
	);

	const onPointerDown = useCallback(
		( e ) => {
			if ( e.button !== 0 ) {
				return;
			}
			setFromEvent( e.clientX, e.clientY );
		},
		[ setFromEvent ]
	);

	return (
		<div className="tb-bg-focus">
			<p className="components-base-control__label tb-bg-focus__label">
				{ __( 'Focus point', 'toolbox-blocks' ) }
			</p>
			<div
				ref={ frameRef }
				className="tb-bg-focus__frame"
				role="button"
				tabIndex={ 0 }
				aria-label={ __(
					'Click to set background focus point on the image',
					'toolbox-blocks'
				) }
				onPointerDown={ onPointerDown }
				onKeyDown={ ( e ) => {
					if ( e.key !== 'Enter' && e.key !== ' ' ) {
						return;
					}
					e.preventDefault();
					const el = frameRef.current;
					if ( ! el ) {
						return;
					}
					const r = el.getBoundingClientRect();
					setFromEvent( r.left + r.width / 2, r.top + r.height / 2 );
				} }
			>
				<img
					src={ imageUrl }
					alt=""
					className="tb-bg-focus__img"
					draggable={ false }
					onDragStart={ ( e ) => e.preventDefault() }
				/>
				<span
					className="tb-bg-focus__ring"
					style={ {
						left: `${ focal.x }%`,
						top: `${ focal.y }%`,
					} }
					aria-hidden="true"
				/>
			</div>
			<p className="components-base-control__help tb-bg-focus__help">
				{ __(
					'Click where the image should be anchored. Updates percentage values below.',
					'toolbox-blocks'
				) }
			</p>
		</div>
	);
}

function BackgroundPositionControl( { get, set } ) {
	/* Do not trim the stored value for the text field: trimming on every render removes spaces the user is typing (e.g. between "50%" and "50%"). */
	const stored = get( 'backgroundPosition' ) || '';
	const trimmedForFocal = stored.trim();
	const thumbUrl = getUrlFromBackgroundImageValue( get( 'backgroundImage' ) || '' );
	const onPickPercent = useCallback(
		( css ) => set( 'backgroundPosition', css ),
		[ set ]
	);

	return (
		<div className="tb-bg-position">
			<p className="components-base-control__label">
				{ __( 'Background Position', 'toolbox-blocks' ) }
			</p>
			{ thumbUrl ? (
				<BackgroundFocusThumbnail
					imageUrl={ thumbUrl }
					positionValue={ trimmedForFocal }
					onPickPercent={ onPickPercent }
				/>
			) : null }
			<TextControl
				label={ __( 'Custom position', 'toolbox-blocks' ) }
				value={ stored }
				onChange={ ( v ) => set( 'backgroundPosition', v ) }
				onBlur={ ( e ) => {
					const current = ( e.target?.value ?? stored ).trim();
					const next = roundPercentPairValue( current );
					if ( next !== current ) {
						set( 'backgroundPosition', next );
					}
				} }
				help={ __(
					'Optional. You can type position keywords (e.g. center center, top left) or any valid CSS.',
					'toolbox-blocks'
				) }
				placeholder="50% 50%"
			/>
		</div>
	);
}

function BackgroundImageControl( { get, set } ) {
	const bg = get( 'backgroundImage' ) || '';
	const rawUrl = getUrlFromBackgroundImageValue( bg );
	const hasUrl = Boolean( rawUrl );

	return (
		<MediaUploadCheck>
			<div className="tb-bg-image-control">
				<p className="components-base-control__label">
					{ __( 'Background Image', 'toolbox-blocks' ) }
				</p>
				<div style={ { display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 8 } }>
					<MediaUpload
						onSelect={ ( media ) => {
							const url = media?.sizes?.large?.url || media?.sizes?.full?.url || media?.url;
							if ( url ) {
								set( 'backgroundImage', toBackgroundImageDeclaration( url ) );
							}
						} }
						allowedTypes={ [ 'image' ] }
						value={ undefined }
						render={ ( { open } ) => (
							<button type="button" className="components-button is-secondary" onClick={ open }>
								{ hasUrl ? __( 'Replace image', 'toolbox-blocks' ) : __( 'Select image', 'toolbox-blocks' ) }
							</button>
						) }
					/>
					{ hasUrl && (
						<button
							type="button"
							className="components-button is-secondary is-destructive"
							onClick={ () => set( 'backgroundImage', '' ) }
						>
							{ __( 'Remove', 'toolbox-blocks' ) }
						</button>
					) }
				</div>
				<TextControl
					label={ __( 'Image URL (optional)', 'toolbox-blocks' ) }
					value={ rawUrl }
					onChange={ ( v ) => set( 'backgroundImage', v ? toBackgroundImageDeclaration( v ) : '' ) }
					help={ __( 'Use media library above when possible, or paste an external image URL.', 'toolbox-blocks' ) }
					placeholder="https://…"
				/>
			</div>
		</MediaUploadCheck>
	);
}

/** Two-color linear gradient → `background`. Other values edit as raw CSS until you switch back. */
function GradientBackgroundControl( { get, set } ) {
	const rawBg = get( 'background' ) || '';
	const rawTrim = rawBg.trim();
	const parsed = useMemo( () => parseSimpleTwoStopLinearGradient( rawTrim ), [ rawTrim ] );
	const simple = parsed !== null;
	const custom = rawTrim !== '' && ! simple;

	const [ draftAngle, setDraftAngle ] = useState( 180 );
	useEffect( () => {
		if ( simple ) {
			setDraftAngle( Math.round( parsed.angle ) );
		} else if ( ! rawTrim && ! custom ) {
			setDraftAngle( 180 );
		}
	}, [ simple, parsed, rawTrim, custom ] );

	const applyBuilt = useCallback(
		( angle, color1, color2 ) => {
			set( 'background', buildSimpleTwoStopLinearGradient( angle, color1, color2 ) );
		},
		[ set ]
	);

	if ( custom ) {
		return (
			<div className="tb-gradient-control tb-gradient-control--custom">
				<p className="components-base-control__label">{ __( 'Gradient', 'toolbox-blocks' ) }</p>
				<p className="tb-gradient-control__help">
					{ __( 'With a background image, this gradient is drawn above the photo.', 'toolbox-blocks' ) }
				</p>
				<TextControl
					label={ __( 'Background value', 'toolbox-blocks' ) }
					value={ rawBg }
					onChange={ ( v ) => set( 'background', v ) }
					help={ __(
						'This value is not the simple two-color pattern. Paste any valid CSS background. Use the button to switch back.',
						'toolbox-blocks'
					) }
					placeholder="linear-gradient(…)"
				/>
				<Button
					variant="secondary"
					onClick={ () =>
						set( 'background', buildSimpleTwoStopLinearGradient( 180, '#6366f1', '#c084fc' ) )
					}
				>
					{ __( 'Use simple gradient', 'toolbox-blocks' ) }
				</Button>
			</div>
		);
	}

	const angleForApply = simple ? parsed.angle : draftAngle;
	const color1 = simple ? parsed.color1 : '';
	const color2 = simple ? parsed.color2 : '';

	const previewBg = buildSimpleTwoStopLinearGradient( angleForApply, color1, color2 );

	const onAngleChange = ( v ) => {
		let nextAng = typeof v === 'number' ? v : parseFloat( v );
		if ( ! Number.isFinite( nextAng ) ) {
			nextAng = 180;
		}
		nextAng = Math.round( Math.min( 360, Math.max( 0, nextAng ) ) );
		setDraftAngle( nextAng );
		if ( simple || color1 || color2 ) {
			applyBuilt( nextAng, color1, color2 );
		}
	};

	return (
		<div className="tb-gradient-control">
			<p className="components-base-control__label">{ __( 'Gradient', 'toolbox-blocks' ) }</p>
			<p className="tb-gradient-control__help">
				{ __( 'With a background image, this gradient is drawn above the photo.', 'toolbox-blocks' ) }
			</p>
			<div
				className="tb-gradient-preview"
				style={ previewBg ? { backgroundImage: previewBg } : undefined }
				role="img"
				aria-label={ __( 'Gradient preview', 'toolbox-blocks' ) }
			/>
			<RangeControl
				label={ __( 'Angle', 'toolbox-blocks' ) }
				value={ simple ? Math.round( parsed.angle ) : draftAngle }
				onChange={ onAngleChange }
				min={ 0 }
				max={ 360 }
				step={ 1 }
			/>
			<div className="tb-gradient-colors">
				<InlineColorPicker
					label={ __( 'Color 1', 'toolbox-blocks' ) }
					value={ color1 }
					onChange={ ( v ) => applyBuilt( angleForApply, v ?? '', color2 ) }
				/>
				<InlineColorPicker
					label={ __( 'Color 2', 'toolbox-blocks' ) }
					value={ color2 }
					onChange={ ( v ) => applyBuilt( angleForApply, color1, v ?? '' ) }
				/>
			</div>
			{ previewBg ? (
				<Button
					variant="secondary"
					isDestructive
					onClick={ () => {
						setDraftAngle( 180 );
						set( 'background', '' );
					} }
				>
					{ __( 'Clear gradient', 'toolbox-blocks' ) }
				</Button>
			) : null }
		</div>
	);
}

// ─── Section: Backgrounds ─────────────────────────────────────────────────────

function BackgroundsSection( { get, set } ) {
	const bgColorLabelId = 'tb-backgrounds-color-heading';

	return (
		<PanelBody title={ __( 'Backgrounds' ) } icon={ <SectionIcon icon="art" /> } initialOpen={ false }>
			<GradientBackgroundControl get={ get } set={ set } />
			<hr className="tb-backgrounds-divider" aria-hidden="true" />
			<BackgroundImageControl get={ get } set={ set } />
			{ get( 'backgroundImage' ) && (
				<>
					<SelectControl
						label={ __( 'Background Size' ) }
						value={ get( 'backgroundSize' ) || '' }
						options={ [
							{ value: '',        label: __( '—' ) },
							{ value: 'cover',   label: 'cover' },
							{ value: 'contain', label: 'contain' },
							{ value: 'auto',    label: 'auto' },
						] }
						onChange={ ( v ) => set( 'backgroundSize', v ) }
					/>
					<BackgroundPositionControl get={ get } set={ set } />
					<SelectControl
						label={ __( 'Background Repeat' ) }
						value={ get( 'backgroundRepeat' ) || '' }
						options={ [
							{ value: '',          label: __( '—' ) },
							{ value: 'no-repeat', label: 'no-repeat' },
							{ value: 'repeat',    label: 'repeat' },
							{ value: 'repeat-x',  label: 'repeat-x' },
							{ value: 'repeat-y',  label: 'repeat-y' },
						] }
						onChange={ ( v ) => set( 'backgroundRepeat', v ) }
					/>
					<SelectControl
						label={ __( 'Background Attachment' ) }
						value={ get( 'backgroundAttachment' ) || '' }
						options={ [
							{ value: '',       label: __( '—' ) },
							{ value: 'scroll', label: 'scroll' },
							{ value: 'fixed',  label: 'fixed' },
							{ value: 'local',  label: 'local' },
						] }
						onChange={ ( v ) => set( 'backgroundAttachment', v ) }
					/>
				</>
			) }
			<SelectControl
				label={ __( 'Background Blend Mode' ) }
				value={ get( 'backgroundBlendMode' ) || '' }
				options={ [
					{ value: '',            label: __( '—' ) },
					{ value: 'normal',      label: 'normal' },
					{ value: 'multiply',    label: 'multiply' },
					{ value: 'screen',      label: 'screen' },
					{ value: 'overlay',     label: 'overlay' },
					{ value: 'darken',      label: 'darken' },
					{ value: 'lighten',     label: 'lighten' },
					{ value: 'color-dodge', label: 'color-dodge' },
					{ value: 'color-burn',  label: 'color-burn' },
					{ value: 'hard-light',  label: 'hard-light' },
					{ value: 'soft-light',  label: 'soft-light' },
					{ value: 'difference',  label: 'difference' },
					{ value: 'exclusion',   label: 'exclusion' },
					{ value: 'hue',         label: 'hue' },
					{ value: 'saturation',  label: 'saturation' },
					{ value: 'color',       label: 'color' },
					{ value: 'luminosity',  label: 'luminosity' },
				] }
				onChange={ ( v ) => set( 'backgroundBlendMode', v ) }
			/>
			<hr className="tb-backgrounds-divider" aria-hidden="true" />
			<div className="tb-bg-color-subsection" role="group" aria-labelledby={ bgColorLabelId }>
				<p id={ bgColorLabelId } className="components-base-control__label">
					{ __( 'Background Color', 'toolbox-blocks' ) }
				</p>
				<InlineColorPicker
					hideLabel
					label={ __( 'Pick background color', 'toolbox-blocks' ) }
					value={ get( 'backgroundColor' ) }
					onChange={ ( v ) => set( 'backgroundColor', v ) }
				/>
			</div>
		</PanelBody>
	);
}

// ─── Section: Borders ─────────────────────────────────────────────────────────

function BordersSection( { get, set, setMultiple } ) {
	const [ borderLinked, setBorderLinked ] = useState( true );
	const [ radiusLinked, setRadiusLinked ] = useState( true );

	const setAllBorder = ( prop, value ) => {
		setMultiple( SIDES.map( ( { key } ) => [ 'border' + key + prop, value ] ) );
	};

	const setAllRadius = ( value ) => {
		setMultiple( CORNERS.map( ( { key } ) => [ 'border' + key + 'Radius', value ] ) );
	};

	return (
		<PanelBody title={ __( 'Borders' ) } icon={ <SectionIcon icon="editor-outdent" /> } initialOpen={ false }>
			<div className="tb-border-control">
				<div className="tb-border-control__header">
					<span className="tb-border-control__label">{ __( 'BORDER' ) }</span>
					<button type="button"
						className={ 'tb-border-control__link-toggle' + ( borderLinked ? ' is-linked' : '' ) }
						onClick={ () => setBorderLinked( ( v ) => ! v ) }
						title={ borderLinked ? __( 'Unlink' ) : __( 'Link' ) }
						aria-label={ borderLinked ? __( 'Linked' ) : __( 'Unlinked' ) }
					>
						<span className={ 'dashicons dashicons-admin-' + ( borderLinked ? 'links' : 'link' ) } />
					</button>
				</div>
				{ SIDES.map( ( { key, label } ) => (
					<div key={ key } className="tb-border-control__row">
						<span className={ `tb-border-control__side-icon tb-border-control__side-icon--${ key.toLowerCase() }` } aria-hidden="true" />
						<div className="tb-border-control__row-inputs">
							<UCV
								label={ label + ' ' + __( 'Width' ) }
								value={ get( 'border' + key + 'Width' ) }
								onChange={ ( v ) => {
									if ( borderLinked ) {
										const updates = SIDES.flatMap( ( { key: k } ) => [
											[ 'border' + k + 'Width', v ],
											...( v && ! get( 'border' + k + 'Style' ) ? [ [ 'border' + k + 'Style', 'solid' ] ] : [] ),
										] );
										setMultiple( updates );
									} else {
										const updates = [ [ 'border' + key + 'Width', v ] ];
										if ( v && ! get( 'border' + key + 'Style' ) ) updates.push( [ 'border' + key + 'Style', 'solid' ] );
										setMultiple( updates );
									}
								} }
								hideLabelFromVision
							/>
							<BorderStylePicker
								value={ get( 'border' + key + 'Style' ) || 'solid' }
								onChange={ ( v ) => borderLinked ? setAllBorder( 'Style', v ) : set( 'border' + key + 'Style', v ) }
							/>
							<InlineColorPicker
								label={ label + ' ' + __( 'Color' ) }
								value={ get( 'border' + key + 'Color' ) }
								onChange={ ( v ) => borderLinked ? setAllBorder( 'Color', v ) : set( 'border' + key + 'Color', v ) }
							/>
						</div>
					</div>
				) ) }
			</div>
			<div className="tb-border-control">
				<div className="tb-border-control__header">
					<span className="tb-border-control__label">{ __( 'BORDER RADIUS' ) }</span>
					<button type="button"
						className={ 'tb-border-control__link-toggle' + ( radiusLinked ? ' is-linked' : '' ) }
						onClick={ () => setRadiusLinked( ( v ) => ! v ) }
						title={ radiusLinked ? __( 'Unlink' ) : __( 'Link' ) }
						aria-label={ radiusLinked ? __( 'Linked' ) : __( 'Unlinked' ) }
					>
						<span className={ 'dashicons dashicons-admin-' + ( radiusLinked ? 'links' : 'link' ) } />
					</button>
				</div>
				<div className="tb-border-control__radius-grid">
					{ CORNERS.map( ( { key, label } ) => (
						<UCV
							key={ key }
							label={ label }
							value={ get( 'border' + key + 'Radius' ) }
							onChange={ ( v ) => radiusLinked ? setAllRadius( v ) : set( 'border' + key + 'Radius', v ) }
						/>
					) ) }
				</div>
			</div>
			<div className="tb-border-control">
				<p className="tb-border-control__label">{ __( 'OUTLINE' ) }</p>
				<div className="tb-border-control__row-inputs">
					<UCV label={ __( 'Width' ) }  value={ get( 'outlineWidth' ) }  onChange={ ( v ) => set( 'outlineWidth', v ) } />
					<BorderStylePicker value={ get( 'outlineStyle' ) || 'none' } onChange={ ( v ) => set( 'outlineStyle', v ) } />
					<InlineColorPicker label={ __( 'Outline Color' ) } value={ get( 'outlineColor' ) } onChange={ ( v ) => set( 'outlineColor', v ) } />
				</div>
				<UCV label={ __( 'Outline Offset' ) } value={ get( 'outlineOffset' ) } onChange={ ( v ) => set( 'outlineOffset', v ) } />
			</div>
		</PanelBody>
	);
}

// ─── Section: Position ────────────────────────────────────────────────────────

function PositionSection( { get, set } ) {
	const position = get( 'position' );
	return (
		<PanelBody title={ __( 'Position' ) } icon={ <SectionIcon icon="location" /> } initialOpen={ false }>
			<SelectControl
				label={ __( 'Position' ) }
				value={ position || '' }
				options={ [
					{ value: '',         label: __( '—' ) },
					{ value: 'static',   label: 'static' },
					{ value: 'relative', label: 'relative' },
					{ value: 'absolute', label: 'absolute' },
					{ value: 'fixed',    label: 'fixed' },
					{ value: 'sticky',   label: 'sticky' },
				] }
				onChange={ ( v ) => set( 'position', v ) }
			/>
			{ ( position === 'relative' || position === 'absolute' || position === 'fixed' || position === 'sticky' ) && (
				<>
					<UCV label={ __( 'Top' ) }    value={ get( 'top' ) }    onChange={ ( v ) => set( 'top', v ) } />
					<UCV label={ __( 'Right' ) }  value={ get( 'right' ) }  onChange={ ( v ) => set( 'right', v ) } />
					<UCV label={ __( 'Bottom' ) } value={ get( 'bottom' ) } onChange={ ( v ) => set( 'bottom', v ) } />
					<UCV label={ __( 'Left' ) }   value={ get( 'left' ) }   onChange={ ( v ) => set( 'left', v ) } />
				</>
			) }
			<TextControl
				label={ __( 'Z-Index' ) }
				value={ get( 'zIndex' ) || '' }
				onChange={ ( v ) => set( 'zIndex', v ) }
				type="number"
			/>
			<SelectControl
				label={ __( 'Float' ) }
				value={ get( 'float' ) || '' }
				options={ [
					{ value: '',      label: __( '—' ) },
					{ value: 'none',  label: 'none' },
					{ value: 'left',  label: 'left' },
					{ value: 'right', label: 'right' },
				] }
				onChange={ ( v ) => set( 'float', v ) }
			/>
		</PanelBody>
	);
}

// ─── Section: Effects ─────────────────────────────────────────────────────────

function EffectsSection( { get, set } ) {
	return (
		<PanelBody title={ __( 'Effects' ) } icon={ <SectionIcon icon="visibility" /> } initialOpen={ false }>
			<RangeControl
				label={ __( 'Opacity' ) }
				value={ get( 'opacity' ) !== '' ? parseFloat( get( 'opacity' ) ) : undefined }
				onChange={ ( v ) => set( 'opacity', v !== undefined ? String( v ) : '' ) }
				min={ 0 } max={ 1 } step={ 0.01 }
				allowReset
			/>
			<TextControl
				label={ __( 'Box Shadow' ) }
				value={ get( 'boxShadow' ) || '' }
				onChange={ ( v ) => set( 'boxShadow', v ) }
				placeholder="0 2px 8px rgba(0,0,0,.2)"
			/>
			<TextControl
				label={ __( 'Transform' ) }
				value={ get( 'transform' ) || '' }
				onChange={ ( v ) => set( 'transform', v ) }
				placeholder="rotate(10deg)"
			/>
			<TextControl
				label={ __( 'Transition' ) }
				value={ get( 'transition' ) || '' }
				onChange={ ( v ) => set( 'transition', v ) }
				placeholder="all 0.3s ease"
			/>
			<TextControl
				label={ __( 'Filter' ) }
				value={ get( 'filter' ) || '' }
				onChange={ ( v ) => set( 'filter', v ) }
				placeholder="blur(4px)"
			/>
			<TextControl
				label={ __( 'Backdrop Filter' ) }
				value={ get( 'backdropFilter' ) || '' }
				onChange={ ( v ) => set( 'backdropFilter', v ) }
				placeholder="blur(4px)"
			/>
			<SelectControl
				label={ __( 'Mix Blend Mode' ) }
				value={ get( 'mixBlendMode' ) || '' }
				options={ [
					{ value: '',            label: __( '—' ) },
					{ value: 'normal',      label: 'normal' },
					{ value: 'multiply',    label: 'multiply' },
					{ value: 'screen',      label: 'screen' },
					{ value: 'overlay',     label: 'overlay' },
					{ value: 'darken',      label: 'darken' },
					{ value: 'lighten',     label: 'lighten' },
					{ value: 'color-dodge', label: 'color-dodge' },
					{ value: 'color-burn',  label: 'color-burn' },
					{ value: 'hard-light',  label: 'hard-light' },
					{ value: 'soft-light',  label: 'soft-light' },
					{ value: 'difference',  label: 'difference' },
					{ value: 'exclusion',   label: 'exclusion' },
				] }
				onChange={ ( v ) => set( 'mixBlendMode', v ) }
			/>
		</PanelBody>
	);
}

// ─── Section: Media ───────────────────────────────────────────────────────────

function MediaSection( { get, set } ) {
	return (
		<PanelBody title={ __( 'Media' ) } icon={ <SectionIcon icon="format-image" /> } initialOpen={ false }>
			<SelectControl
				label={ __( 'Object Fit' ) }
				value={ get( 'objectFit' ) || '' }
				options={ [
					{ value: '',          label: __( '—' ) },
					{ value: 'fill',      label: 'fill' },
					{ value: 'contain',   label: 'contain' },
					{ value: 'cover',     label: 'cover' },
					{ value: 'none',      label: 'none' },
					{ value: 'scale-down', label: 'scale-down' },
				] }
				onChange={ ( v ) => set( 'objectFit', v ) }
			/>
			<TextControl
				label={ __( 'Object Position' ) }
				value={ get( 'objectPosition' ) || '' }
				onChange={ ( v ) => set( 'objectPosition', v ) }
				placeholder="center center"
			/>
		</PanelBody>
	);
}

// ─── Section: Lists ───────────────────────────────────────────────────────────

function ListsSection( { get, set } ) {
	return (
		<PanelBody title={ __( 'Lists' ) } icon={ <SectionIcon icon="editor-ul" /> } initialOpen={ false }>
			<SelectControl
				label={ __( 'List Style Type' ) }
				value={ get( 'listStyleType' ) || '' }
				options={ [
					{ value: '',           label: __( '—' ) },
					{ value: 'none',       label: 'none' },
					{ value: 'disc',       label: 'disc' },
					{ value: 'circle',     label: 'circle' },
					{ value: 'square',     label: 'square' },
					{ value: 'decimal',    label: 'decimal' },
					{ value: 'lower-roman', label: 'lower-roman' },
					{ value: 'upper-roman', label: 'upper-roman' },
					{ value: 'lower-alpha', label: 'lower-alpha' },
					{ value: 'upper-alpha', label: 'upper-alpha' },
				] }
				onChange={ ( v ) => set( 'listStyleType', v ) }
			/>
			<SelectControl
				label={ __( 'List Style Position' ) }
				value={ get( 'listStylePosition' ) || '' }
				options={ [
					{ value: '',        label: __( '—' ) },
					{ value: 'inside',  label: 'inside' },
					{ value: 'outside', label: 'outside' },
				] }
				onChange={ ( v ) => set( 'listStylePosition', v ) }
			/>
		</PanelBody>
	);
}

// ─── Section: Pointer Events ──────────────────────────────────────────────────

function PointerEventsSection( { get, set } ) {
	return (
		<PanelBody title={ __( 'Pointer Events' ) } icon={ <SectionIcon icon="admin-generic" /> } initialOpen={ false }>
			<SelectControl
				label={ __( 'Pointer Events' ) }
				value={ get( 'pointerEvents' ) || '' }
				options={ [
					{ value: '',     label: __( '—' ) },
					{ value: 'auto', label: 'auto' },
					{ value: 'none', label: 'none' },
					{ value: 'all',  label: 'all' },
				] }
				onChange={ ( v ) => set( 'pointerEvents', v ) }
			/>
		</PanelBody>
	);
}

// ─── Section: Cursor ──────────────────────────────────────────────────────────

function CursorSection( { get, set } ) {
	return (
		<PanelBody title={ __( 'Cursor' ) } icon={ <SectionIcon icon="admin-customizer" /> } initialOpen={ false }>
			<SelectControl
				label={ __( 'Cursor' ) }
				value={ get( 'cursor' ) || '' }
				options={ [
					{ value: '',            label: __( '—' ) },
					{ value: 'auto',        label: 'auto' },
					{ value: 'default',     label: 'default' },
					{ value: 'pointer',     label: 'pointer' },
					{ value: 'crosshair',   label: 'crosshair' },
					{ value: 'move',        label: 'move' },
					{ value: 'text',        label: 'text' },
					{ value: 'wait',        label: 'wait' },
					{ value: 'help',        label: 'help' },
					{ value: 'not-allowed', label: 'not-allowed' },
					{ value: 'none',        label: 'none' },
					{ value: 'grab',        label: 'grab' },
					{ value: 'grabbing',    label: 'grabbing' },
					{ value: 'zoom-in',     label: 'zoom-in' },
					{ value: 'zoom-out',    label: 'zoom-out' },
				] }
				onChange={ ( v ) => set( 'cursor', v ) }
			/>
		</PanelBody>
	);
}

// ─── Section: Fill / Stroke Color ─────────────────────────────────────────────

function FillStrokeSection( { get, set } ) {
	return (
		<>
			<PanelBody title={ __( 'Fill Color' ) } icon={ <SectionIcon icon="art" /> } initialOpen={ false }>
				<InlineColorPicker label={ __( 'Fill' ) } value={ get( 'fill' ) } onChange={ ( v ) => set( 'fill', v ) } />
			</PanelBody>
			<PanelBody title={ __( 'Stroke Color' ) } icon={ <SectionIcon icon="edit" /> } initialOpen={ false }>
				<InlineColorPicker label={ __( 'Stroke' ) } value={ get( 'stroke' ) } onChange={ ( v ) => set( 'stroke', v ) } />
				<UCV label={ __( 'Stroke Width' ) } value={ get( 'strokeWidth' ) } onChange={ ( v ) => set( 'strokeWidth', v ) } />
			</PanelBody>
		</>
	);
}

// ─── StylesPanel ──────────────────────────────────────────────────────────────

/**
 * Full styles panel. Renders DeviceSwitcher + MainHoverTabs + all sections.
 *
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes setAttributes.
 * @param {string[]} props.sections      Which sections to show (defaults to all).
 */
export default function StylesPanel( { attributes, setAttributes, sections } ) {
	const { styles = {} } = attributes;
	const [ state, setState ] = useState( 'main' );
	const [ device ] = useEditorDevice();

	const ALL_SECTIONS = [
		'layout', 'sizing', 'spacing', 'typography',
		'backgrounds', 'borders', 'position', 'effects',
		'media', 'lists', 'pointerEvents', 'cursor', 'fillStroke',
	];
	const enabled = sections || ALL_SECTIONS;

	const get = ( property ) => getStyleValue( styles, property, device, state );

	const set = ( property, value ) => {
		setAttributes( { styles: setStyleValue( styles, property, value, device, state ) } );
	};

	const setMultiple = ( updates ) => {
		let newStyles = styles;
		updates.forEach( ( [ property, value ] ) => {
			newStyles = setStyleValue( newStyles, property, value, device, state );
		} );
		setAttributes( { styles: newStyles } );
	};

	const show = ( key ) => enabled.includes( key );

	const screenSizeMessages = {
		desktop: __( 'Style settings for all screen sizes' ),
		tablet:  __( 'Medium screen size overrides' ),
		mobile:  __( 'Small screen size overrides' ),
	};
	const screenSizeMessage = screenSizeMessages[ device ] || screenSizeMessages.desktop;

	return (
		<div className="tb-styles-panel">
			<DeviceSwitcher />
			<p className="tb-styles-panel__screen-size-message" role="status">
				{ screenSizeMessage }
			</p>
			<MainHoverTabs state={ state } onChange={ setState } />
			{ show( 'layout' )       && <LayoutSection       get={ get } set={ set } /> }
			{ show( 'sizing' )       && <SizingSection       get={ get } set={ set } /> }
			{ show( 'spacing' )      && <SpacingSection      get={ get } set={ set } setMultiple={ setMultiple } /> }
			{ show( 'typography' )   && <TypographySection   get={ get } set={ set } /> }
			{ show( 'backgrounds' )  && <BackgroundsSection  get={ get } set={ set } /> }
			{ show( 'borders' )      && <BordersSection      get={ get } set={ set } setMultiple={ setMultiple } /> }
			{ show( 'position' )     && <PositionSection     get={ get } set={ set } /> }
			{ show( 'effects' )      && <EffectsSection      get={ get } set={ set } /> }
			{ show( 'media' )        && <MediaSection        get={ get } set={ set } /> }
			{ show( 'lists' )        && <ListsSection        get={ get } set={ set } /> }
			{ show( 'pointerEvents' ) && <PointerEventsSection get={ get } set={ set } /> }
			{ show( 'cursor' )       && <CursorSection       get={ get } set={ set } /> }
			{ show( 'fillStroke' )   && <FillStrokeSection   get={ get } set={ set } /> }
		</div>
	);
}
