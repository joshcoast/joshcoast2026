/**
 * Query – edit component.
 */

import { useEffect } from '@wordpress/element';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl, TextControl, RangeControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import InspectorTabs from '../../shared/InspectorTabs';
import StylesPanel from '../../shared/StylesPanel';
import { generateBlockCss } from '../../utils/generate-css';

export default function QueryEdit( { attributes, setAttributes, clientId } ) {
	const {
		uniqueId, styles, postType, postsPerPage, order, orderBy, offset,
		noResultsText, className,
	} = attributes;

	useEffect( () => {
		if ( ! uniqueId && clientId ) {
			setAttributes( { uniqueId: clientId.replace( /[^a-z0-9]/gi, '' ).slice( 0, 12 ) } );
		}
	}, [ clientId ] );

	// Fetch registered post types for the selector.
	const postTypes = useSelect( ( select ) => {
		const types = select( 'core' ).getPostTypes( { per_page: -1 } );
		if ( ! types ) return [];
		return types
			.filter( ( t ) => t.viewable && t.slug !== 'attachment' )
			.map( ( t ) => ( { value: t.slug, label: t.name } ) );
	}, [] );

	const css = uniqueId ? generateBlockCss( uniqueId, styles ) : '';
	const cls = [ 'tb-block', 'tb-query', uniqueId && `tb-${ uniqueId }`, className ]
		.filter( Boolean ).join( ' ' );
	const blockProps = useBlockProps( { className: cls } );

	const settings = (
		<>
			<PanelBody title={ __( 'Query' ) } initialOpen={ true }>
				<SelectControl
					label={ __( 'Post Type' ) }
					value={ postType }
					options={ postTypes.length ? postTypes : [ { value: 'post', label: __( 'Posts' ) } ] }
					onChange={ ( v ) => setAttributes( { postType: v } ) }
				/>
				<RangeControl
					label={ __( 'Posts per page' ) }
					value={ postsPerPage }
					onChange={ ( v ) => setAttributes( { postsPerPage: v } ) }
					min={ 1 } max={ 100 }
				/>
				<SelectControl
					label={ __( 'Order' ) }
					value={ order }
					options={ [
						{ value: 'DESC', label: __( 'Newest first' ) },
						{ value: 'ASC',  label: __( 'Oldest first' ) },
					] }
					onChange={ ( v ) => setAttributes( { order: v } ) }
				/>
				<SelectControl
					label={ __( 'Order by' ) }
					value={ orderBy }
					options={ [
						{ value: 'date',          label: __( 'Date' ) },
						{ value: 'title',         label: __( 'Title' ) },
						{ value: 'modified',      label: __( 'Modified date' ) },
						{ value: 'comment_count', label: __( 'Comment count' ) },
						{ value: 'menu_order',    label: __( 'Menu order' ) },
						{ value: 'rand',          label: __( 'Random' ) },
					] }
					onChange={ ( v ) => setAttributes( { orderBy: v } ) }
				/>
				<TextControl
					label={ __( 'Offset' ) }
					value={ String( offset ) }
					onChange={ ( v ) => setAttributes( { offset: parseInt( v, 10 ) || 0 } ) }
					type="number"
					min="0"
				/>
				<TextControl
					label={ __( 'No results text' ) }
					value={ noResultsText || '' }
					onChange={ ( v ) => setAttributes( { noResultsText: v } ) }
					placeholder={ __( 'No posts found.' ) }
				/>
			</PanelBody>
		</>
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
			<div { ...blockProps }>
				<div className="tb-query__label">
					{ __( 'Query Loop' ) } — { postType } ({ order } { orderBy })
				</div>
				<InnerBlocks
					templateLock={ false }
					template={ [
						[ 'toolbox-blocks/headline', { tagName: 'h2', content: __( 'Post title' ) } ],
						[ 'toolbox-blocks/text', { content: __( 'Post excerpt…' ) } ],
					] }
				/>
			</div>
		</>
	);
}
