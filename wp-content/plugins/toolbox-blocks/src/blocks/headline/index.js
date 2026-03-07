/**
 * Toolbox Blocks – Headline block registration.
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import HeadlineEdit from './edit';

registerBlockType( 'toolbox-blocks/headline', {
	apiVersion: 3,
	title: __( 'Headline' ),
	description: __( 'A heading element with full style controls.' ),
	icon: 'heading',
	category: 'toolbox-blocks',
	attributes: {
		uniqueId:     { type: 'string', default: '' },
		styles:       { type: 'object', default: {} },
		content:      { type: 'string', default: '' },
		tagName:      { type: 'string', default: 'h2' },
		htmlAnchor:   { type: 'string', default: '' },
		extraClasses: { type: 'string', default: '' },
	},
	edit: HeadlineEdit,
	save: () => null,
} );
