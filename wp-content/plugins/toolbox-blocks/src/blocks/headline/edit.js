/**
 * Headline – edit component.
 */

import { useEffect } from '@wordpress/element';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import InspectorTabs from '../../shared/InspectorTabs';
import StylesPanel from '../../shared/StylesPanel';
import { generateBlockCss } from '../../utils/generate-css';

const TAG_OPTIONS = [
	{ value: 'h1', label: 'H1' },
	{ value: 'h2', label: 'H2' },
	{ value: 'h3', label: 'H3' },
	{ value: 'h4', label: 'H4' },
	{ value: 'h5', label: 'H5' },
	{ value: 'h6', label: 'H6' },
];

export default function HeadlineEdit( { attributes, setAttributes, clientId } ) {
	const { uniqueId, styles, content, tagName: Tag = 'h2', extraClasses } = attributes;

	useEffect( () => {
		if ( ! uniqueId && clientId ) {
			setAttributes( { uniqueId: clientId.replace( /[^a-z0-9]/gi, '' ).slice( 0, 12 ) } );
		}
	}, [ clientId ] );

	const css = uniqueId ? generateBlockCss( uniqueId, styles ) : '';
	const cls = [ 'tb-block', 'tb-headline', uniqueId && `tb-${ uniqueId }`, extraClasses ]
		.filter( Boolean ).join( ' ' );
	const blockProps = useBlockProps( { className: cls } );

	const settings = (
		<>
			<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
				<div className="tb-heading-level-picker">
					{ TAG_OPTIONS.map( ( { value, label } ) => (
						<button
							key={ value }
							type="button"
							className={ 'tb-heading-level-btn' + ( Tag === value ? ' is-active' : '' ) }
							onClick={ () => setAttributes( { tagName: value } ) }
						>
							{ label }
						</button>
					) ) }
				</div>
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
					styles={ <StylesPanel attributes={ attributes } setAttributes={ setAttributes } sections={ [ 'sizing', 'spacing', 'typography', 'backgrounds', 'borders', 'position', 'effects', 'pointerEvents', 'cursor' ] } /> }
				/>
			</InspectorControls>
			<RichText
				{ ...blockProps }
				tagName={ Tag }
				value={ content }
				onChange={ ( v ) => setAttributes( { content: v } ) }
				placeholder={ __( 'Write headline…' ) }
			/>
		</>
	);
}
