/**
 * Two-tab inspector layout: Settings | Styles.
 * Wraps the block inspector panel with GenerateBlocks-style tabs.
 */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default function InspectorTabs( { settings, styles } ) {
	const [ activeTab, setActiveTab ] = useState( 'settings' );

	return (
		<div className="tb-inspector-tabs">
			<div className="tb-inspector-tabs__tablist" role="tablist">
				<button
					type="button"
					role="tab"
					aria-selected={ activeTab === 'settings' }
					className={ 'tb-inspector-tabs__tab' + ( activeTab === 'settings' ? ' is-active' : '' ) }
					onClick={ () => setActiveTab( 'settings' ) }
				>
					{ __( 'Settings' ) }
				</button>
				<button
					type="button"
					role="tab"
					aria-selected={ activeTab === 'styles' }
					className={ 'tb-inspector-tabs__tab' + ( activeTab === 'styles' ? ' is-active' : '' ) }
					onClick={ () => setActiveTab( 'styles' ) }
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
