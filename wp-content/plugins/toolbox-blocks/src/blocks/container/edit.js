/**
 * Container – edit component.
 */

import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import InspectorTabs from '../../shared/InspectorTabs';
import StylesPanel from '../../shared/StylesPanel';
import ContainerSettings from './settings';
import useEditorDevice from '../../hooks/useEditorDevice';
import { generateBlockCss, getStyleValue } from '../../utils/generate-css';

const TEMPLATE = [ [ 'core/paragraph', { placeholder: 'Add content…' } ] ];

export default function ContainerEdit( { attributes, setAttributes, clientId } ) {
	const { uniqueId, styles, tagName: Tag = 'div', bgImageUrl, className } = attributes;

	// Generate a stable unique ID derived from the block's clientId.
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
		'tb-container',
		needsLayoutPassthrough && 'tb-container--layout-passthrough',
		className,
	]
		.filter( Boolean ).join( ' ' );

	const blockProps = useBlockProps( { className: cls } );

	const bgStyle = bgImageUrl ? { backgroundImage: `url(${ bgImageUrl })` } : {};
	blockProps.style = { ...( blockProps.style || {} ), ...bgStyle };

	const innerClass = [
		'tb-container__inner',
		uniqueId && `tb-${ uniqueId }`,
	].filter( Boolean ).join( ' ' );

	return (
		<>
			{ css && <style>{ css }</style> }
			<InspectorControls>
				<InspectorTabs
					clientId={ clientId }
					settings={ <ContainerSettings attributes={ attributes } setAttributes={ setAttributes } /> }
					styles={ <StylesPanel attributes={ attributes } setAttributes={ setAttributes } /> }
				/>
			</InspectorControls>
			<Tag { ...blockProps }>
				<div className={ innerClass }>
					<InnerBlocks template={ TEMPLATE } templateLock={ false } />
				</div>
			</Tag>
		</>
	);
}
