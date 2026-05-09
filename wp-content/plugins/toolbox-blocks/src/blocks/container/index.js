/**
 * Toolbox Blocks – Container block registration.
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { toolboxBlockIcon } from '../../shared/block-icon-config';
import ContainerEdit from './edit';

registerBlockType( 'toolbox-blocks/container', {
	apiVersion: 3,
	title: __( 'Container' ),
	description: __( 'A wrapper element with full style controls.' ),
	icon: toolboxBlockIcon( 'layout' ),
	category: 'toolbox-blocks',
	supports: {
		anchor: true,
		customClassName: true,
	},
	attributes: {
		uniqueId:   { type: 'string', default: '' },
		styles:     { type: 'object', default: {} },
		tagName:    { type: 'string', default: 'div' },
		bgImageUrl: { type: 'string', default: '' },
		bgImageId:  { type: 'number', default: 0 },
		anchor:     { type: 'string', default: '' },
		className:  { type: 'string', default: '' },
	},
	edit: ContainerEdit,
	save: ( { attributes } ) => {
		const { uniqueId, tagName: Tag = 'div', className } = attributes;
		const cls = [ 'tb-block', 'tb-container', uniqueId && `tb-${ uniqueId }`, className ]
			.filter( Boolean ).join( ' ' );
		return (
			<Tag className={ cls }>
				<InnerBlocks.Content />
			</Tag>
		);
	},
} );
