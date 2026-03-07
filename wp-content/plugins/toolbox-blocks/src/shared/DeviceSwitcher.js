/**
 * Device switcher: Desktop | Tablet | Mobile icon tabs.
 * Syncs with the editor's preview device so the canvas resizes.
 */

import { __ } from '@wordpress/i18n';
import useEditorDevice from '../hooks/useEditorDevice';

const DEVICES = [
	{ key: 'desktop', label: __( 'Desktop' ), icon: 'desktop' },
	{ key: 'tablet',  label: __( 'Tablet' ),  icon: 'tablet' },
	{ key: 'mobile',  label: __( 'Mobile' ),  icon: 'smartphone' },
];

export default function DeviceSwitcher() {
	const [ device, setDevice ] = useEditorDevice();

	return (
		<div className="tb-device-switcher" role="tablist" aria-label={ __( 'Screen size' ) }>
			{ DEVICES.map( ( d ) => (
				<button
					key={ d.key }
					type="button"
					role="tab"
					aria-selected={ device === d.key }
					className={ 'tb-device-switcher__btn' + ( device === d.key ? ' is-active' : '' ) }
					onClick={ () => setDevice( d.key ) }
					title={ d.label }
				>
					<span className={ 'dashicons dashicons-' + d.icon } aria-hidden="true" />
				</button>
			) ) }
		</div>
	);
}
