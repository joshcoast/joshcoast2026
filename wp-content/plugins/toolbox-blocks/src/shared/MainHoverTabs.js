/**
 * Main | Hover state tabs for the Styles panel.
 */

import { __ } from '@wordpress/i18n';

export default function MainHoverTabs( { state, onChange } ) {
	return (
		<div className="tb-main-hover-tabs" role="tablist" aria-label={ __( 'Style state' ) }>
			<button
				type="button"
				role="tab"
				aria-selected={ state === 'main' }
				className={ 'tb-main-hover-tabs__tab' + ( state === 'main' ? ' is-active' : '' ) }
				onClick={ () => onChange( 'main' ) }
			>
				{ __( 'Main' ) }
			</button>
			<button
				type="button"
				role="tab"
				aria-selected={ state === 'hover' }
				className={ 'tb-main-hover-tabs__tab' + ( state === 'hover' ? ' is-active' : '' ) }
				onClick={ () => onChange( 'hover' ) }
			>
				{ __( 'Hover' ) }
			</button>
		</div>
	);
}
