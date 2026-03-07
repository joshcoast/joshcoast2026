/**
 * Toolbox Blocks – Button block registration.
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import ButtonEdit from './edit';

registerBlockType( 'toolbox-blocks/button', {
	apiVersion: 3,
	title: __( 'Button' ),
	description: __( 'A link button with full style controls.' ),
	icon: 'button',
	category: 'toolbox-blocks',
	attributes: {
		uniqueId:     { type: 'string', default: '' },
		styles:       { type: 'object', default: {} },
		text:         { type: 'string', default: '' },
		url:          { type: 'string', default: '' },
		target:       { type: 'string', default: '_self' },
		rel:          { type: 'string', default: '' },
		htmlAnchor:   { type: 'string', default: '' },
		extraClasses: { type: 'string', default: '' },
	},
	edit: ButtonEdit,
	save: () => null,
} );
