/**
 * Toolbox Blocks – Text block registration.
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import TextEdit from './edit';

registerBlockType( 'toolbox-blocks/text', {
	apiVersion: 3,
	title: __( 'Text' ),
	description: __( 'A rich-text paragraph with full style controls.' ),
	icon: 'editor-paragraph',
	category: 'toolbox-blocks',
	supports: { anchor: true, customClassName: true },
	attributes: {
		uniqueId:  { type: 'string', default: '' },
		styles:    { type: 'object', default: {} },
		content:   { type: 'string', default: '' },
		tagName:   { type: 'string', default: 'p' },
		anchor:    { type: 'string', default: '' },
		className: { type: 'string', default: '' },
	},
	edit: TextEdit,
	save: () => null,
} );
