import { useEffect } from '@wordpress/element';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { SpacingControls } from '../../shared/SpacingControls';

const TEMPLATE = [['core/paragraph', { placeholder: 'Add content…' }]];

export default function ContainerEdit({ attributes, setAttributes, clientId }) {
	// Ensure a stable uniqueId for CSS (used in render_callback).
	useEffect(() => {
		if (!attributes.uniqueId && clientId) {
			setAttributes({ uniqueId: clientId.replace(/^[^a-z]+/i, 'c').slice(0, 12) });
		}
	}, [clientId]);

	const blockProps = useBlockProps({
		className: 'cb-container-editor',
		style: coastBlocksGetSpacingInlineStyle(attributes.spacing, ''),
	});

	return (
		<>
			<InspectorControls>
				<SpacingControls attributes={attributes} setAttributes={setAttributes} />
			</InspectorControls>
			<div {...blockProps}>
				<InnerBlocks template={TEMPLATE} templateLock={false} />
			</div>
		</>
	);
}


/**
 * Inline style for spacing (desktop only in editor for simplicity).
 */
function coastBlocksGetSpacingInlineStyle(spacing, suffix) {
	if (!spacing) return {};
	const s = {};
	const sides = ['Top', 'Right', 'Bottom', 'Left'];
	const types = ['padding', 'margin'];
	types.forEach((type) => {
		sides.forEach((side) => {
			const key = type + side + suffix;
			const v = spacing[key];
			// React style expects camelCase (e.g. paddingTop, marginLeft).
			const styleKey = type + side;
			if (v !== undefined && v !== '') {
				s[styleKey] = isNumeric(v) ? v + 'px' : String(v);
			}
		});
	});
	return s;
}

function isNumeric(v) {
	return typeof v === 'number' || (typeof v === 'string' && /^\d+(\.\d+)?$/.test(v));
}
