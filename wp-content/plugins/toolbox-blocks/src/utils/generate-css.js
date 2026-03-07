/**
 * CSS generation utilities for Toolbox Blocks.
 *
 * Styles are stored as:
 *   styles.desktop / styles.desktopHover
 *   styles.tablet  / styles.tabletHover
 *   styles.mobile  / styles.mobileHover
 *
 * Each value is a flat object of camelCase CSS property names.
 */

const BREAKPOINTS = [
	{ key: 'desktop',      query: null },
	{ key: 'tablet',       query: '(max-width:1024px)' },
	{ key: 'mobile',       query: '(max-width:767px)' },
];

/** camelCase → kebab-case */
function camelToKebab( str ) {
	return str.replace( /([A-Z])/g, ( m ) => '-' + m.toLowerCase() );
}

/** Convert a style object to a CSS declarations string. */
export function objectToDeclarations( obj ) {
	if ( ! obj || typeof obj !== 'object' ) return '';
	return Object.entries( obj )
		.filter( ( [ , v ] ) => v !== '' && v !== null && v !== undefined )
		.map( ( [ prop, value ] ) => `${ camelToKebab( prop ) }:${ value }` )
		.join( ';' );
}

/**
 * Generate complete CSS for a block from its styles object.
 *
 * @param {string} uniqueId Block unique ID.
 * @param {Object} styles   Block styles attribute.
 * @return {string}
 */
export function generateBlockCss( uniqueId, styles ) {
	if ( ! uniqueId || ! styles ) return '';
	return generateCssFromStyles( `.tb-${ uniqueId }`, styles );
}

/**
 * Generate CSS from a styles object for an arbitrary selector.
 *
 * @param {string} selector CSS selector.
 * @param {Object} styles   Styles object.
 * @return {string}
 */
export function generateCssFromStyles( selector, styles ) {
	if ( ! selector || ! styles ) return '';

	let css = '';

	for ( const { key: bpKey, query } of BREAKPOINTS ) {
		// Main state
		const mainDecls = objectToDeclarations( styles[ bpKey ] );
		if ( mainDecls ) {
			const rule = `${ selector }{${ mainDecls }}`;
			css += query ? `@media ${ query }{${ rule }}` : rule;
		}

		// Hover state
		const hoverDecls = objectToDeclarations( styles[ bpKey + 'Hover' ] );
		if ( hoverDecls ) {
			const rule = `${ selector }:hover{${ hoverDecls }}`;
			css += query ? `@media ${ query }{${ rule }}` : rule;
		}
	}

	return css;
}

/**
 * Get a style value for a given device and state.
 *
 * @param {Object} styles   Block styles attribute.
 * @param {string} property CSS property in camelCase.
 * @param {string} device   'desktop' | 'tablet' | 'mobile'
 * @param {string} state    'main' | 'hover'
 * @return {string}
 */
export function getStyleValue( styles, property, device = 'desktop', state = 'main' ) {
	const bpKey = state === 'hover' ? device + 'Hover' : device;
	return styles?.[ bpKey ]?.[ property ] ?? '';
}

/**
 * Return a new styles object with one property updated.
 *
 * @param {Object} styles   Current styles attribute.
 * @param {string} property CSS property in camelCase.
 * @param {string} value    New value (empty string removes the property).
 * @param {string} device   'desktop' | 'tablet' | 'mobile'
 * @param {string} state    'main' | 'hover'
 * @return {Object}
 */
export function setStyleValue( styles, property, value, device = 'desktop', state = 'main' ) {
	const bpKey = state === 'hover' ? device + 'Hover' : device;
	const current = { ...( styles?.[ bpKey ] || {} ) };

	if ( value === '' || value === null || value === undefined ) {
		delete current[ property ];
	} else {
		current[ property ] = value;
	}

	return { ...( styles || {} ), [ bpKey ]: current };
}
