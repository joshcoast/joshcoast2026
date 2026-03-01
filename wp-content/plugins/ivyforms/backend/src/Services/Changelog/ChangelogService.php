<?php

namespace IvyForms\Services\Changelog;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Settings\SettingsService;

class ChangelogService
{
    /**
     * Get changelog data with translated strings.
     *
     * @return array{
     *     version: string,
     *     release_date: string,
     *     features: array<array{text: string}>,
     *     improvements: array<array{text: string}>,
     *     bugfixes: array<array{text: string}>
     * }
     */
    public static function getChangelogData(): array
    {
        $changelogData = [
            'version' => IVYFORMS_VERSION,
            'release_date' => '11.02.2026.',
            'features' => [
                ['text' => __('Added a new field - 
                Rating field for collecting user ratings in your forms.', 'ivyforms')],

            ],
            'improvements' => [
                ['text' => __('UX/UI improvements for 
                better user experience and interface consistency.', 'ivyforms')],

            ],
            'bugfixes' => [
//                ['text' => __('Fixed issue with duplicate form name display
//                in Form Builder.', 'ivyforms')],
            ],
        ];

        /**
         * Filter changelog data to allow Pro plugin to add its own changelog entries
         *
         * @param array $changelogData The changelog data array
         * @return array Modified changelog data
         */
        return apply_filters('ivyforms/changelog/get_data', $changelogData);
    }
}
