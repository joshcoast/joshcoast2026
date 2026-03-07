/**
 * Small color swatch button that opens a popover color picker.
 */

import { useState } from '@wordpress/element';
import { ColorPalette, Popover } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

export default function ColorPickerButton( { value, onChange, label } ) {
	const [ isOpen, setIsOpen ] = useState( false );

	const themeColors = useSelect( ( select ) => {
		return select( 'core/block-editor' )?.getSettings?.()?.colors || [];
	}, [] );

	return (
		<>
			<button
				type="button"
				className="tb-color-btn"
				style={ { backgroundColor: value || 'transparent' } }
				onClick={ () => setIsOpen( ( v ) => ! v ) }
				title={ label }
				aria-label={ label }
			/>
			{ isOpen && (
				<Popover placement="bottom-start" onClose={ () => setIsOpen( false ) }>
					<div className="tb-color-popover">
						<ColorPalette
							value={ value }
							onChange={ ( v ) => {
								onChange( v || '' );
								setIsOpen( false );
							} }
							colors={ themeColors }
							clearable
							enableAlpha
						/>
					</div>
				</Popover>
			) }
		</>
	);
}
