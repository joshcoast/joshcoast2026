<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Changelog;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Changelog\ChangelogService;
use IvyForms\Services\Settings\SettingsService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class GetChangelogController
 *
 * @package IvyForms\Controllers\Changelog
 */
class GetChangelogController extends Controller
{
    private ChangelogService $changelogService;
    private SettingsService $settingsService;

    public function __construct(
        ChangelogService $changelogService,
        SettingsService $settingsService
    ) {
        $this->changelogService = $changelogService;
        $this->settingsService = $settingsService;
    }
    /**
     * Get changelog data with translated strings
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

        $changelogData = $this->changelogService->getChangelogData();

        $oldVersion = $this->settingsService->getSetting('general', 'changelog_version');
        $shouldShow = $oldVersion !== IVYFORMS_VERSION;

        if ($shouldShow) {
            $this->settingsService->setSetting('general', 'changelog_version', IVYFORMS_VERSION);
        }

        $responseData = [
            'oldVersion'   => $oldVersion,
            'version'      => IVYFORMS_VERSION,
            'release_date' => $changelogData['release_date'],
            'features'     => $changelogData['features'],
            'improvements' => $changelogData['improvements'],
            'bugfixes'     => $changelogData['bugfixes'],
            'shouldShow'   => $shouldShow,
        ];

        /**
         * Filter changelog response data to allow Pro plugin to modify version, release date, etc.
         *
         * @param array $responseData The response data array
         * @param array $changelogData The full changelog data from service
         * @param SettingsService $settingsService The settings service instance
         * @return array Modified response data
         */
        $responseData = apply_filters(
            'ivyforms/changelog/response_data',
            $responseData,
            $changelogData,
            $this->settingsService
        );

        return new WP_REST_Response([
            'message' => __('Changelog retrieved successfully', 'ivyforms'),
            'data' => $responseData
        ], 200);
    }
}
