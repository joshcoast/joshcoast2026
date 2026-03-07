/**
 * Toolbox Blocks – Grid block registration.
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import GridEdit from './edit';

registerBlockType( 'toolbox-blocks/grid', {
	apiVersion: 3,
	title: __( 'Grid' ),
	description: __( 'A grid or flex layout container.' ),
	icon: 'grid-view',
	category: 'toolbox-blocks',
	attributes: {
		uniqueId:     { type: 'string', default: '' },
		styles:       { type: 'object', default: {} },
		tagName:      { type: 'string', default: 'div' },
		htmlAnchor:   { type: 'string', default: '' },
		extraClasses: { type: 'string', default: '' },
	},
	edit: GridEdit,
	save: ( { attributes } ) => {
		const { uniqueId, tagName: Tag = 'div', extraClasses } = attributes;
		const cls = [ 'tb-block', 'tb-grid', uniqueId && `tb-${ uniqueId }`, extraClasses ]
			.filter( Boolean ).join( ' ' );
		return (
			<Tag className={ cls }>
				<InnerBlocks.Content />
			</Tag>
		);
	},
} );
