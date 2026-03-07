/**
 * Toolbox Blocks – Container block registration.
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import ContainerEdit from './edit';

registerBlockType( 'toolbox-blocks/container', {
	apiVersion: 3,
	title: __( 'Container' ),
	description: __( 'A wrapper element with full style controls.' ),
	icon: 'layout',
	category: 'toolbox-blocks',
	attributes: {
		uniqueId:    { type: 'string', default: '' },
		styles:      { type: 'object', default: {} },
		tagName:     { type: 'string', default: 'div' },
		bgImageUrl:  { type: 'string', default: '' },
		bgImageId:   { type: 'number', default: 0 },
		htmlAnchor:  { type: 'string', default: '' },
		extraClasses: { type: 'string', default: '' },
	},
	edit: ContainerEdit,
	save: ( { attributes } ) => {
		const { uniqueId, tagName: Tag = 'div', extraClasses } = attributes;
		const cls = [ 'tb-block', 'tb-container', uniqueId && `tb-${ uniqueId }`, extraClasses ]
			.filter( Boolean ).join( ' ' );
		return (
			<Tag className={ cls }>
				<InnerBlocks.Content />
			</Tag>
		);
	},
} );
