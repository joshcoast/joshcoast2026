/**
 * Container – Settings tab content.
 */

import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';

const TAG_OPTIONS = [
	{ value: 'div',     label: 'div' },
	{ value: 'section', label: 'section' },
	{ value: 'article', label: 'article' },
	{ value: 'aside',   label: 'aside' },
	{ value: 'header',  label: 'header' },
	{ value: 'footer',  label: 'footer' },
	{ value: 'main',    label: 'main' },
	{ value: 'nav',     label: 'nav' },
	{ value: 'span',    label: 'span' },
];

export default function ContainerSettings( { attributes, setAttributes } ) {
	const { tagName, bgImageUrl, bgImageId, htmlAnchor, extraClasses } = attributes;

	return (
		<>
			<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
				<SelectControl
					label={ __( 'Tag Name' ) }
					value={ tagName || 'div' }
					options={ TAG_OPTIONS }
					onChange={ ( v ) => setAttributes( { tagName: v } ) }
				/>
				<MediaUploadCheck>
					<div className="tb-bg-image-control">
						<p className="components-base-control__label">
							{ __( 'Inline Background Image' ) }
						</p>
						{ bgImageUrl && (
							<div className="tb-bg-image-preview">
								<img src={ bgImageUrl } alt="" style={ { maxWidth: '100%', height: 'auto', borderRadius: 4, marginBottom: 8 } } />
							</div>
						) }
						<div style={ { display: 'flex', gap: 8 } }>
							<MediaUpload
								onSelect={ ( media ) => {
									const url = media.sizes?.large?.url || media.url;
									setAttributes( { bgImageUrl: url, bgImageId: media.id } );
								} }
								allowedTypes={ [ 'image' ] }
								value={ bgImageId }
								render={ ( { open } ) => (
									<button type="button" className="components-button is-secondary is-small" onClick={ open }>
										{ bgImageUrl ? __( 'Replace Image' ) : __( 'Select Image' ) }
									</button>
								) }
							/>
							{ bgImageUrl && (
								<button
									type="button"
									className="components-button is-secondary is-small is-destructive"
									onClick={ () => setAttributes( { bgImageUrl: '', bgImageId: 0 } ) }
								>
									{ __( 'Remove' ) }
								</button>
							) }
						</div>
					</div>
				</MediaUploadCheck>
			</PanelBody>
			<PanelBody title={ __( 'Advanced' ) } initialOpen={ false }>
				<TextControl
					label={ __( 'HTML Anchor' ) }
					help={ __( 'Assigns an id attribute to the element. Use to link directly to this block.' ) }
					value={ htmlAnchor || '' }
					onChange={ ( v ) => setAttributes( { htmlAnchor: v } ) }
					placeholder="my-section"
				/>
				<TextControl
					label={ __( 'Additional CSS Class(es)' ) }
					help={ __( 'Separate multiple classes with spaces.' ) }
					value={ extraClasses || '' }
					onChange={ ( v ) => setAttributes( { extraClasses: v } ) }
				/>
			</PanelBody>
		</>
	);
}
