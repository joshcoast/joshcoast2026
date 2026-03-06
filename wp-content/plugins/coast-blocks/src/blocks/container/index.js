import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/block-editor';
import ContainerEdit from './edit';
import { createElement } from '@wordpress/element';

const spacingDefaults = {
	paddingTop: '',
	paddingRight: '',
	paddingBottom: '',
	paddingLeft: '',
	marginTop: '',
	marginRight: '',
	marginBottom: '',
	marginLeft: '',
	paddingTopTablet: '',
	paddingRightTablet: '',
	paddingBottomTablet: '',
	paddingLeftTablet: '',
	marginTopTablet: '',
	marginRightTablet: '',
	marginBottomTablet: '',
	marginLeftTablet: '',
	paddingTopMobile: '',
	paddingRightMobile: '',
	paddingBottomMobile: '',
	paddingLeftMobile: '',
	marginTopMobile: '',
	marginRightMobile: '',
	marginBottomMobile: '',
	marginLeftMobile: '',
};

const borderKeys = [
	'borderTopWidth', 'borderRightWidth', 'borderBottomWidth', 'borderLeftWidth',
	'borderTopStyle', 'borderRightStyle', 'borderBottomStyle', 'borderLeftStyle',
	'borderTopColor', 'borderRightColor', 'borderBottomColor', 'borderLeftColor',
	'borderTopLeftRadius', 'borderTopRightRadius', 'borderBottomRightRadius', 'borderBottomLeftRadius',
];
const bordersDefaults = {};
borderKeys.forEach((k) => {
	bordersDefaults[k] = '';
	bordersDefaults[k + 'Tablet'] = '';
	bordersDefaults[k + 'Mobile'] = '';
});

registerBlockType('coast-blocks/container', {
	apiVersion: 2,
	title: __('Container'),
	description: __('A container with optional spacing (margin/padding) for All screens, Medium, and Small.'),
	icon: 'layout',
	category: 'design',
	attributes: {
		uniqueId: {
			type: 'string',
			default: '',
		},
		tagName: {
			type: 'string',
			default: 'div',
		},
		spacing: {
			type: 'object',
			default: spacingDefaults,
		},
		borders: {
			type: 'object',
			default: bordersDefaults,
		},
	},
	edit: ContainerEdit,
	// Save full wrapper + inner blocks so content appears on the front end (no render_callback).
	save: ({ attributes }) => {
		const id = attributes.uniqueId ? `cb-container-${attributes.uniqueId}` : undefined;
		return (
			<div
				id={id}
				className="cb-container wp-block-coast-blocks-container"
			>
				<InnerBlocks.Content />
			</div>
		);
	},
});
