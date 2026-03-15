/**
 * Toolbox Blocks – Image block registration.
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import ImageEdit from './edit';

registerBlockType( 'toolbox-blocks/image', {
	apiVersion: 3,
	title: __( 'Image' ),
	description: __( 'An image element with full style controls.' ),
	icon: 'format-image',
	category: 'toolbox-blocks',
	supports: { anchor: true, customClassName: true },
	attributes: {
		uniqueId:  { type: 'string', default: '' },
		styles:    { type: 'object', default: {} },
		url:       { type: 'string', default: '' },
		imageId:   { type: 'number', default: 0 },
		alt:       { type: 'string', default: '' },
		caption:   { type: 'string', default: '' },
		linkUrl:   { type: 'string', default: '' },
		anchor:    { type: 'string', default: '' },
		className: { type: 'string', default: '' },
	},
	edit: ImageEdit,
	save: () => null,
} );
