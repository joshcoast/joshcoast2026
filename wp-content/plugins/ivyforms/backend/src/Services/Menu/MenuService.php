<?php

namespace IvyForms\Services\Menu;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Routes\Routes;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Renders menu pages
 */
class MenuService
{
    /**
     * Submenu page render function
     * @param string $page
     */
    public function render(string $page): void
    {
        $page = trim($page);
        $scriptId = IVYFORMS_DEV ? 'ivyforms_scripts_dev_vite' : 'ivyforms_script_index';   // @phpstan-ignore-line
        $scriptSrc = IVYFORMS_DEV ? // @phpstan-ignore-line
                'http://localhost:5173/src/assets/js/admin/admin.ts' :
            IVYFORMS_URL . 'frontend/dist/admin.js';
        $styleSrc = IVYFORMS_DEV ? '' : IVYFORMS_URL . 'frontend/dist/index.css';// @phpstan-ignore-line
        $scriptVersion = IVYFORMS_DEV ? null : IVYFORMS_VERSION;// @phpstan-ignore-line

        wp_enqueue_script(
            $scriptId,
            $scriptSrc,
            [],
            $scriptVersion,
            true
        );

        // TODO: Check including fonts on build
        // @phpstan-ignore-next-line
        if ($styleSrc) {
            wp_enqueue_style(
                'ivyforms_style_admin',
                IVYFORMS_URL . 'frontend/dist/admin.css',
                [],
                $scriptVersion
            );
            wp_enqueue_style(
                'ivyforms_style_index',
                $styleSrc,
                [],
                $scriptVersion
            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_regular_woff2',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Regular.woff2',
//                [],
//                $scriptVersion
//            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_regular_woff',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Regular.woff',
//                [],
//                $scriptVersion
//            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_medium_woff2',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Medium.woff2',
//                [],
//                $scriptVersion
//            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_medium_woff',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Medium.woff',
//                [],
//                $scriptVersion
//            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_bold_woff2',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Bold.woff2',
//                [],
//                $scriptVersion
//            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_bold_woff',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Bold.woff',
//                [],
//                $scriptVersion
//            );
        }

        // Localize scripts
        $settingsStorage = new SettingsService();

        wp_localize_script(
            $scriptId,
            'wpIvySettings',
            $settingsStorage->getFrontendSettings()
        );

        $backendLabels = array_merge(
            BackendStrings::getNewFormStrings(),
            BackendStrings::getSettingsFormBuilderStrings(),
            BackendStrings::getResultsFormBuilderStrings(),
            BackendStrings::getEmptyStatesStrings(),
            BackendStrings::getEntityFormStrings(),
            BackendStrings::getAllFormsStrings(),
            BackendStrings::getDashboardStrings(),
            BackendStrings::getCommonStrings(),
            BackendStrings::getComponentsStrings(),
            BackendStrings::getEntriesStrings(),
            BackendStrings::getIntegrationsStrings(),
            BackendStrings::getExceptionStrings(),
            BackendStrings::getTemplateStrings(),
            BackendStrings::getSettingsStrings(),
            BackendStrings::getSecurityStrings(),
            BackendStrings::getProUpgradeDialogStrings(),
            BackendStrings::getChangelogStrings()
        );

        /**
         * Filter backend labels before localizing
         * Allows Pro version to merge additional strings
         *
         * @since 0.1.0
         * @param array $backendLabels Array of backend label strings
         */
        $backendLabels = apply_filters('ivyforms/admin/backend_labels', $backendLabels);

        wp_localize_script(
            $scriptId,
            'wpIvyLabels',
            $backendLabels
        );

        wp_localize_script(
            $scriptId,
            'wpIvyUrls',
            [
                'pluginURL' => IVYFORMS_URL,
                'siteURL'   => esc_url_raw(site_url()),
            ]
        );
        wp_localize_script(
            $scriptId,
            'wpIvyApiSettings',
            [
                'root'      => esc_url_raw(rest_url()),
                'nonce'     => wp_create_nonce('wp_rest'),
                'namespace' => Routes::$routeNamespace,
            ]
        );
        wp_localize_script(
            $scriptId,
            'wpIvyDateFormat',
            [
                'dateFormat'     => get_option('date_format'),
                'timeFormat'     => get_option('time_format'),
                'dateTimeFormat' => get_option('date_format') . ' ' . get_option('time_format'),
                'locale'         => get_locale(),
            ]
        );

        /**
         * Hook to enqueue additional scripts and styles in admin area
         * @since 0.1.0
         *
         * Arguments:
         *  - string $scriptId The main script handle ID (for dependencies)
         */
        do_action('ivyforms/admin/enqueue_scripts', $scriptId);

        include IVYFORMS_PATH . '/view/backend/view.php';
    }
}
