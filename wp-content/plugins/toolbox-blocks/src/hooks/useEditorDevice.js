/**
 * Hook: returns the current editor preview device type.
 * Returns 'desktop' | 'tablet' | 'mobile' (lowercase, matching styles keys).
 */

import { useSelect, useDispatch } from '@wordpress/data';

function rawToDevice( raw ) {
	if ( raw === 'Tablet' ) return 'tablet';
	if ( raw === 'Mobile' ) return 'mobile';
	return 'desktop';
}

function deviceToRaw( device ) {
	if ( device === 'tablet' ) return 'Tablet';
	if ( device === 'mobile' ) return 'Mobile';
	return 'Desktop';
}

export default function useEditorDevice() {
	const raw = useSelect( ( select ) => {
		const editor = select( 'core/editor' );
		if ( editor?.getDeviceType ) return editor.getDeviceType();
		const editPost = select( 'core/edit-post' );
		if ( editPost?.__experimentalGetPreviewDeviceType ) {
			return editPost.__experimentalGetPreviewDeviceType();
		}
		return 'Desktop';
	}, [] );

	const { setDeviceType } = useDispatch( 'core/editor' ) || {};
	const { __experimentalSetPreviewDeviceType } =
		useDispatch( 'core/edit-post' ) || {};

	const setDevice = ( device ) => {
		const rawDevice = deviceToRaw( device );
		if ( setDeviceType ) {
			setDeviceType( rawDevice );
		} else if ( __experimentalSetPreviewDeviceType ) {
			__experimentalSetPreviewDeviceType( rawDevice );
		}
	};

	return [ rawToDevice( raw ), setDevice ];
}
