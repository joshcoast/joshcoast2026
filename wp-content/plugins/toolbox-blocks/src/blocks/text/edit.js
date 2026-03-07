/**
 * Text – edit component.
 */

import { useEffect } from '@wordpress/element';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import InspectorTabs from '../../shared/InspectorTabs';
import StylesPanel from '../../shared/StylesPanel';
import { generateBlockCss } from '../../utils/generate-css';

const TAG_OPTIONS = [
	{ value: 'p',    label: 'p' },
	{ value: 'div',  label: 'div' },
	{ value: 'span', label: 'span' },
	{ value: 'li',   label: 'li' },
];

export default function TextEdit( { attributes, setAttributes, clientId } ) {
	const { uniqueId, styles, content, tagName: Tag = 'p', extraClasses } = attributes;

	useEffect( () => {
		if ( ! uniqueId && clientId ) {
			setAttributes( { uniqueId: clientId.replace( /[^a-z0-9]/gi, '' ).slice( 0, 12 ) } );
		}
	}, [ clientId ] );

	const css = uniqueId ? generateBlockCss( uniqueId, styles ) : '';
	const cls = [ 'tb-block', 'tb-text', uniqueId && `tb-${ uniqueId }`, extraClasses ]
		.filter( Boolean ).join( ' ' );
	const blockProps = useBlockProps( { className: cls } );

	const settings = (
		<>
			<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
				<SelectControl
					label={ __( 'Tag Name' ) }
					value={ Tag }
					options={ TAG_OPTIONS }
					onChange={ ( v ) => setAttributes( { tagName: v } ) }
				/>
			</PanelBody>
			<PanelBody title={ __( 'Advanced' ) } initialOpen={ false }>
				<TextControl label={ __( 'HTML Anchor' ) } value={ attributes.htmlAnchor || '' } onChange={ ( v ) => setAttributes( { htmlAnchor: v } ) } />
				<TextControl label={ __( 'Additional CSS Class(es)' ) } value={ extraClasses || '' } onChange={ ( v ) => setAttributes( { extraClasses: v } ) } />
			</PanelBody>
		</>
	);

	return (
		<>
			{ css && <style>{ css }</style> }
			<InspectorControls>
				<InspectorTabs
					settings={ settings }
					styles={ <StylesPanel attributes={ attributes } setAttributes={ setAttributes } sections={ [ 'sizing', 'spacing', 'typography', 'backgrounds', 'borders', 'position', 'effects', 'lists', 'pointerEvents', 'cursor' ] } /> }
				/>
			</InspectorControls>
			<RichText
				{ ...blockProps }
				tagName={ Tag }
				value={ content }
				onChange={ ( v ) => setAttributes( { content: v } ) }
				placeholder={ __( 'Write text…' ) }
			/>
		</>
	);
}
