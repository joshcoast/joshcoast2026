/**
 * Toolbox Blocks – Query block registration.
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import QueryEdit from './edit';

registerBlockType( 'toolbox-blocks/query', {
	apiVersion: 3,
	title: __( 'Query' ),
	description: __( 'Loop posts with a custom query and template.' ),
	icon: 'list-view',
	category: 'toolbox-blocks',
	attributes: {
		uniqueId:      { type: 'string', default: '' },
		styles:        { type: 'object', default: {} },
		postType:      { type: 'string', default: 'post' },
		postsPerPage:  { type: 'number', default: 10 },
		order:         { type: 'string', default: 'DESC' },
		orderBy:       { type: 'string', default: 'date' },
		offset:        { type: 'number', default: 0 },
		noResultsText: { type: 'string', default: '' },
		htmlAnchor:    { type: 'string', default: '' },
		extraClasses:  { type: 'string', default: '' },
	},
	edit: QueryEdit,
	save: ( { attributes } ) => {
		const { uniqueId, extraClasses } = attributes;
		const cls = [ 'tb-block', 'tb-query', uniqueId && `tb-${ uniqueId }`, extraClasses ]
			.filter( Boolean ).join( ' ' );
		return (
			<div className={ cls }>
				<InnerBlocks.Content />
			</div>
		);
	},
} );
