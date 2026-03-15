/**
 * Image – edit component.
 */

import { useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, Button } from '@wordpress/components';
import InspectorTabs from '../../shared/InspectorTabs';
import StylesPanel from '../../shared/StylesPanel';
import { generateBlockCss } from '../../utils/generate-css';

export default function ImageEdit( { attributes, setAttributes, clientId } ) {
	const { uniqueId, styles, url, imageId, alt, caption, linkUrl, className } = attributes;

	useEffect( () => {
		if ( ! uniqueId && clientId ) {
			setAttributes( { uniqueId: clientId.replace( /[^a-z0-9]/gi, '' ).slice( 0, 12 ) } );
		}
	}, [ clientId ] );

	const css = uniqueId ? generateBlockCss( uniqueId, styles ) : '';
	const cls = [ 'tb-block', 'tb-image', uniqueId && `tb-${ uniqueId }`, className ]
		.filter( Boolean ).join( ' ' );
	const blockProps = useBlockProps( { className: cls } );

	const settings = (
		<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
			<TextControl
				label={ __( 'Alt Text' ) }
				help={ __( 'Describe the image for accessibility. Leave empty only if the image is purely decorative.' ) }
				value={ alt || '' }
				onChange={ ( v ) => setAttributes( { alt: v } ) }
			/>
			<TextControl
				label={ __( 'Link URL' ) }
				value={ linkUrl || '' }
				onChange={ ( v ) => setAttributes( { linkUrl: v } ) }
				placeholder="https://…"
				type="url"
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
					styles={ <StylesPanel attributes={ attributes } setAttributes={ setAttributes } sections={ [ 'sizing', 'spacing', 'backgrounds', 'borders', 'position', 'effects', 'media', 'pointerEvents', 'cursor' ] } /> }
				/>
			</InspectorControls>
			<figure { ...blockProps }>
				{ url ? (
					<>
						<img src={ url } alt={ alt } className="tb-image__img" style={ { maxWidth: '100%' } } />
						<div className="tb-image__toolbar" style={ { display: 'flex', gap: 8, marginTop: 4 } }>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => setAttributes( { url: media.url, imageId: media.id, alt: media.alt || '' } ) }
									allowedTypes={ [ 'image' ] }
									value={ imageId }
									render={ ( { open } ) => (
										<Button variant="secondary" size="small" onClick={ open }>
											{ __( 'Replace' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							<Button variant="secondary" size="small" isDestructive
								onClick={ () => setAttributes( { url: '', imageId: 0, alt: '' } ) }
							>
								{ __( 'Remove' ) }
							</Button>
						</div>
					</>
				) : (
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => setAttributes( { url: media.url, imageId: media.id, alt: media.alt || '' } ) }
							allowedTypes={ [ 'image' ] }
							render={ ( { open } ) => (
								<Button variant="primary" onClick={ open } className="tb-image__placeholder">
									{ __( 'Select Image' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
				) }
				<RichText
					tagName="figcaption"
					className="tb-image__caption"
					value={ caption }
					onChange={ ( v ) => setAttributes( { caption: v } ) }
					placeholder={ __( 'Add caption…' ) }
				/>
			</figure>
		</>
	);
}
