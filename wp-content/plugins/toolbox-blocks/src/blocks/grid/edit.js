/**
 * Grid – edit component.
 */

import { useEffect } from '@wordpress/element';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl } from '@wordpress/components';
import InspectorTabs from '../../shared/InspectorTabs';
import StylesPanel from '../../shared/StylesPanel';
import useEditorDevice from '../../hooks/useEditorDevice';
import { generateBlockCss, getStyleValue } from '../../utils/generate-css';

const TAG_OPTIONS = [
	{ value: 'div',     label: 'div' },
	{ value: 'section', label: 'section' },
	{ value: 'ul',      label: 'ul' },
	{ value: 'ol',      label: 'ol' },
];

/** Two Toolbox Containers with a paragraph each—no GenerateBlocks dependency. Applies when the grid is newly inserted empty. */
const GRID_TEMPLATE = [
	[
		'toolbox-blocks/container',
		{},
		[
			[ 'core/paragraph', { placeholder: __( 'Column 1…', 'toolbox-blocks' ) } ],
		],
	],
	[
		'toolbox-blocks/container',
		{},
		[
			[ 'core/paragraph', { placeholder: __( 'Column 2…', 'toolbox-blocks' ) } ],
		],
	],
];

export default function GridEdit( { attributes, setAttributes, clientId } ) {
	const { uniqueId, styles, tagName: Tag = 'div', className } = attributes;

	useEffect( () => {
		if ( ! uniqueId && clientId ) {
			setAttributes( { uniqueId: clientId.replace( /[^a-z0-9]/gi, '' ).slice( 0, 12 ) } );
		}
	}, [ clientId ] );

	const css = uniqueId ? generateBlockCss( uniqueId, styles ) : '';

	const [ device ] = useEditorDevice();
	const display = getStyleValue( styles, 'display', device, 'main' );
	const needsLayoutPassthrough = [ 'flex', 'inline-flex', 'grid' ].includes( display );

	const cls = [
		'tb-block',
		'tb-grid',
		needsLayoutPassthrough && 'tb-grid--layout-passthrough',
		className,
	]
		.filter( Boolean ).join( ' ' );
	const blockProps = useBlockProps( { className: cls } );

	const innerClass = [
		'tb-grid__inner',
		uniqueId && `tb-${ uniqueId }`,
	].filter( Boolean ).join( ' ' );

	const settings = (
		<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
			<SelectControl
				label={ __( 'Tag Name' ) }
				value={ Tag }
				options={ TAG_OPTIONS }
				onChange={ ( v ) => setAttributes( { tagName: v } ) }
			/>
		</PanelBody>
	);

	return (
		<>
			{ css && <style>{ css }</style> }
			<InspectorControls>
				<InspectorTabs
					clientId={ clientId }
					settings={ settings }
					styles={ <StylesPanel attributes={ attributes } setAttributes={ setAttributes } /> }
				/>
			</InspectorControls>
			<Tag { ...blockProps }>
				<div className={ innerClass }>
					<InnerBlocks
						template={ GRID_TEMPLATE }
						templateLock={ false }
					/>
				</div>
			</Tag>
		</>
	);
}
