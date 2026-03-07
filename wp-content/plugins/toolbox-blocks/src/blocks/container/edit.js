/**
 * Container – edit component.
 */

import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import InspectorTabs from '../../shared/InspectorTabs';
import StylesPanel from '../../shared/StylesPanel';
import ContainerSettings from './settings';
import { generateBlockCss } from '../../utils/generate-css';

const TEMPLATE = [ [ 'core/paragraph', { placeholder: 'Add content…' } ] ];

export default function ContainerEdit( { attributes, setAttributes, clientId } ) {
	const { uniqueId, styles, tagName: Tag = 'div', bgImageUrl, extraClasses } = attributes;

	// Generate a stable unique ID derived from the block's clientId.
	useEffect( () => {
		if ( ! uniqueId && clientId ) {
			setAttributes( { uniqueId: clientId.replace( /[^a-z0-9]/gi, '' ).slice( 0, 12 ) } );
		}
	}, [ clientId ] );

	const css = uniqueId ? generateBlockCss( uniqueId, styles ) : '';

	const cls = [ 'tb-block', 'tb-container', uniqueId && `tb-${ uniqueId }`, extraClasses ]
		.filter( Boolean ).join( ' ' );

	const blockProps = useBlockProps( { className: cls } );

	const bgStyle = bgImageUrl ? { backgroundImage: `url(${ bgImageUrl })` } : {};
	blockProps.style = { ...( blockProps.style || {} ), ...bgStyle };

	return (
		<>
			{ css && <style>{ css }</style> }
			<InspectorControls>
				<InspectorTabs
					settings={ <ContainerSettings attributes={ attributes } setAttributes={ setAttributes } /> }
					styles={ <StylesPanel attributes={ attributes } setAttributes={ setAttributes } /> }
				/>
			</InspectorControls>
			<Tag { ...blockProps }>
				<InnerBlocks template={ TEMPLATE } templateLock={ false } />
			</Tag>
		</>
	);
}
