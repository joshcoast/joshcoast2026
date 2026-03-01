<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Settings;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class GetAllSettingsController
 *
 * @package IvyForms\Controllers\Settings
 */
class GetAllSettingsController extends Controller
{
    private SettingsService $settingsService;

    public function __construct(
        SettingsService $settingsService
    ) {
        $this->settingsService = $settingsService;
    }

    /**
     * Get all settings organized by category
     *
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Get all settings from the service
        $allSettings = $this->settingsService->getAllSettings();

        // Organize settings structure for frontend (prepare for future extensions)
        $organizedSettings = [
            'security' => [
                'recaptcha' => $allSettings['security']['recaptcha'] ?? null,
                'turnstile' => $allSettings['security']['turnstile'] ?? null,
                'hcaptcha' => $allSettings['security']['hcaptcha'] ?? null,

                // Example: If you wanted to include security status info, you could inject SecurityService:
                // 'status' => [
                //     'activeCaptchaProvider' => $this->securityService->getActiveCaptchaProvider(),
                //     'isCaptchaConfigured' => $this->securityService->isCaptchaConfigured(),
                // ]
            ],
            'general' => [
                'wcagBackend' => $allSettings['general']['wcagBackend'] ?? false,
                'favoriteTemplates' => $allSettings['general']['favoriteTemplates'] ?? [],
                'delete_on_uninstall' => $allSettings['general']['delete_on_uninstall'] ?? false,
                // Future general settings can be added here:
                // 'site_settings' => $allSettings['general']['site_settings'] ?? null,
                // 'form_defaults' => $allSettings['general']['form_defaults'] ?? null,
                'changelog_version' => $allSettings['general']['changelog_version'] ?? '',
            ],
            'integrations' => [
                'wpdatatables' => array_merge(
                    $allSettings['integrations']['wpdatatables'] ?? ['enabled' => false],
                    [
                        'connected' => class_exists('WPDataTable') || defined('WDT_ROOT_PATH')
                    ]
                ),
            // Future categories can be added here
            ],
        ];

        /**
         * Filter to allow Pro plugin to add additional settings data
         *
         * @param array $organizedSettings The organized settings array
         * @param array $allSettings The raw settings from database
         *
         * @since 1.0.0
         */
        $organizedSettings = apply_filters('ivyforms/global/settings/get_all', $organizedSettings, $allSettings);

        return new WP_REST_Response([
            'message' => BackendStrings::getSettingsStrings()['all_settings_retrieved'],
            'data'    => $organizedSettings
        ], 200);
    }
}
