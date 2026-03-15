/**
 * Button – edit component.
 */

import { useEffect } from '@wordpress/element';
import { useBlockProps, RichText, InspectorControls, LinkControl } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { PanelBody, ToggleControl, TextControl } from '@wordpress/components';
import InspectorTabs from '../../shared/InspectorTabs';
import StylesPanel from '../../shared/StylesPanel';
import { generateBlockCss } from '../../utils/generate-css';

export default function ButtonEdit( { attributes, setAttributes, clientId } ) {
	const { uniqueId, styles, text, url, target, rel, className } = attributes;

	useEffect( () => {
		if ( ! uniqueId && clientId ) {
			setAttributes( { uniqueId: clientId.replace( /[^a-z0-9]/gi, '' ).slice( 0, 12 ) } );
		}
	}, [ clientId ] );

	const css = uniqueId ? generateBlockCss( uniqueId, styles ) : '';
	const cls = [ 'tb-block', 'tb-button', uniqueId && `tb-${ uniqueId }`, className ]
		.filter( Boolean ).join( ' ' );
	const blockProps = useBlockProps( { className: cls } );

	const settings = (
		<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
			<TextControl
				label={ __( 'URL' ) }
				value={ url || '' }
				onChange={ ( v ) => setAttributes( { url: v } ) }
				placeholder="https://…"
				type="url"
			/>
			<ToggleControl
				label={ __( 'Open in new tab' ) }
				checked={ target === '_blank' }
				onChange={ ( v ) => setAttributes( { target: v ? '_blank' : '_self' } ) }
			/>
			<TextControl
				label={ __( 'Link rel' ) }
				value={ rel || '' }
				onChange={ ( v ) => setAttributes( { rel: v } ) }
				placeholder="noopener"
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
					styles={ <StylesPanel attributes={ attributes } setAttributes={ setAttributes } sections={ [ 'sizing', 'spacing', 'typography', 'backgrounds', 'borders', 'position', 'effects', 'pointerEvents', 'cursor' ] } /> }
				/>
			</InspectorControls>
			<div { ...blockProps }>
				<RichText
					tagName="a"
					className="tb-button__link"
					value={ text }
					onChange={ ( v ) => setAttributes( { text: v } ) }
					placeholder={ __( 'Button text…' ) }
					allowedFormats={ [] }
				/>
			</div>
		</>
	);
}
