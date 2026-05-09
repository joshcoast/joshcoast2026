/**
 * Brand color for Toolbox block & category icons (block inserter).
 */
export const TOOLBOX_BLOCKS_ICON_FOREGROUND = '#2271b1';

/**
 * @param {string} dashiconSlug Dashicon slug (e.g. 'layout', 'button').
 * @return {{ src: string, foreground: string }}
 */
export function toolboxBlockIcon( dashiconSlug ) {
	return {
		src: dashiconSlug,
		foreground: TOOLBOX_BLOCKS_ICON_FOREGROUND,
	};
}
