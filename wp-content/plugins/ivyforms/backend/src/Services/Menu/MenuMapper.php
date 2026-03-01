<?php

namespace IvyForms\Services\Menu;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\API\IvyFormsAPI;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class Menu
 */
class MenuMapper
{
    /**
     * @return mixed[]
     */
    public function __invoke(): array
    {
        $menus =
            [
//                [
//                    'parentSlug' => 'ivyforms',
//                    'pageTitle'  => 'Dashboard',
//                    'menuTitle'  => BackendStrings::getDashboardStrings()['dashboard'],
//                    // TODO - Add IvyForms capability
//                    'capability' => 'read',
//                    'menuSlug'   => 'ivyforms-dashboard',
//                ],
                [
                    'parentSlug' => 'ivyforms',
                    'pageTitle'  => 'All Forms',
                    'menuTitle'  => BackendStrings::getCommonStrings()['all_forms'],
                    // TODO - Add IvyForms capability
                    'capability' => 'read',
                    'menuSlug'   => 'ivyforms-forms',
                ],
                [
                    'parentSlug' => 'ivyforms',
                    'pageTitle'  => 'New form',
                    'menuTitle'  => BackendStrings::getCommonStrings()['new_form'],
                    // TODO - Add IvyForms capability
                    'capability' => 'read',
                    'menuSlug'   => 'ivyforms-builder',
                ],
                [
                    'parentSlug' => 'ivyforms',
                    'pageTitle'  => 'Entries',
                    'menuTitle'  => BackendStrings::getEntriesStrings()['entries'],
                    // TODO -  Add IvyForms capability
                    'capability' => 'read',
                    'menuSlug'   => 'ivyforms-entries',
                ],
//                    // TODO Add Settings page
                [
                    'parentSlug' => 'ivyforms',
                    'pageTitle'  => 'Settings',
                    'menuTitle'  => BackendStrings::getCommonStrings()['settings'],
                    // TODO - Add IvyForms capability
                    'capability' => 'read',
                    'menuSlug'   => 'ivyforms-settings',
                ],
                [
                    'parentSlug' => 'ivyforms',
                    'pageTitle'  => 'Integrations',
                    'menuTitle'  => BackendStrings::getIntegrationsStrings()['integrations'],
                    // TODO - Add IvyForms capability
                    'capability' => 'read',
                    'menuSlug'   => 'ivyforms-integrations',
                ],
            ];

        // Add "Upgrade to PRO" menu only if Pro plugin is not installed/activated
        if (!IvyFormsAPI::isProPluginActive()) {
            $menus[] = [
                'parentSlug' => 'ivyforms',
                'pageTitle'  => 'Upgrade to PRO',
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'menuTitle'  => '<span class="ivyforms-upgrade-to-pro">' . __('Upgrade to PRO', 'ivyforms')  . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3.30739 5.78346L5.09363 3.36669C6.24826 1.80449 6.82558 1.02339 7.3642 1.18846C7.90283 1.35352 7.90283 2.31153 7.90283 4.22757V4.40823C7.90283 5.0993 7.90283 5.44483 8.12364 5.66157L8.13533 5.6728C8.36091 5.88496 8.72054 5.88496 9.4398 5.88496C10.7341 5.88496 11.3813 5.88496 11.6 6.27751C11.6037 6.28401 11.6072 6.29056 11.6106 6.29716C11.8171 6.69579 11.4424 7.20277 10.6929 8.21673L8.90667 10.6335C7.75202 12.1957 7.1747 12.9768 6.63607 12.8117C6.09744 12.6466 6.09746 11.6886 6.09748 9.77256L6.09749 9.59198C6.0975 8.9009 6.0975 8.55536 5.87669 8.33861L5.865 8.32739C5.63942 8.11522 5.27979 8.11522 4.56052 8.11522C3.26618 8.11522 2.61901 8.11522 2.40029 7.72267C2.39666 7.71617 2.39314 7.70962 2.38973 7.70302C2.18325 7.3044 2.55796 6.79742 3.30739 5.78346Z" fill="white"/></svg></span>',
                'capability' => 'read',
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'menuSlug'   => 'https://ivyforms.com/pricing?utm_source=ivy-lite&utm_medium=lite-upgrade&utm_content=ivy&utm_campaign=ivy',
                'isExternal' => true,
            ];
        }

        return $menus;
    }
}
