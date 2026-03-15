/**
 * Two-tab inspector layout: Settings | Styles.
 * Wraps the block inspector panel with GenerateBlocks-style tabs.
 *
 * Persists active tab per block so switching screen size (device) does not reset to Settings.
 */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const activeTabByBlock = {};

export default function InspectorTabs( { settings, styles, clientId } ) {
	const [ activeTab, setActiveTab ] = useState( () =>
		( clientId && activeTabByBlock[ clientId ] ) ? activeTabByBlock[ clientId ] : 'settings'
	);

	const setTab = ( tab ) => {
		setActiveTab( tab );
		if ( clientId ) {
			activeTabByBlock[ clientId ] = tab;
		}
	};

	return (
		<div className="tb-inspector-tabs">
			<div className="tb-inspector-tabs__tablist" role="tablist" aria-label={ __( 'Block settings' ) }>
				<button
					type="button"
					role="tab"
					aria-selected={ activeTab === 'settings' }
					className={ 'tb-inspector-tabs__tab' + ( activeTab === 'settings' ? ' is-active' : '' ) }
					onClick={ () => setTab( 'settings' ) }
				>
					{ __( 'Settings' ) }
				</button>
				<button
					type="button"
					role="tab"
					aria-selected={ activeTab === 'styles' }
					className={ 'tb-inspector-tabs__tab' + ( activeTab === 'styles' ? ' is-active' : '' ) }
					onClick={ () => setTab( 'styles' ) }
				>
					{ __( 'Styles' ) }
				</button>
			</div>
			<div className="tb-inspector-tabs__content">
				{ activeTab === 'settings' ? settings : styles }
			</div>
		</div>
	);
}
