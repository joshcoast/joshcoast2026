<?php

/**
 * Settings hook for settings
 */

namespace IvyForms\Services\Settings;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class SettingsService
 *
 * @package IvyForms\Services\Settings
 */
class SettingsService
{
    /** @var array|mixed */
    private $settingsCache;

    /**
     * @var array<string, string>  // Define that both keys and values are strings
     * */
    private static array $wpSettings = [
        'dateFormat'     => 'date_format',
        'timeFormat'     => 'time_format',
        'startOfWeek'    => 'start_of_week',
        'timeZoneString' => 'timezone_string',
        'gmtOffset'      => 'gmt_offset'
    ];

    /**
     * SettingsService constructor.
     */
    public function __construct()
    {
        $this->settingsCache = json_decode(get_option('ivyforms_settings'), true);

        foreach (self::$wpSettings as $setting => $wpSetting) {
            $this->settingsCache['wordpress'][$setting] = get_option($wpSetting);
        }
    }

    /**
     * @param string $settingCategoryKey
     * @param string $settingKey
     *
     * @return mixed
     */
    public function getSetting(string $settingCategoryKey, string $settingKey)
    {
        return $this->settingsCache[$settingCategoryKey][$settingKey] ?? null;
    }

    /**
     * @param string $settingCategoryKey
     *
     * @return mixed
     */
    public function getCategorySettings(string $settingCategoryKey)
    {
        return $this->settingsCache[$settingCategoryKey] ?? null;
    }

    /**
     * @return mixed[]
     */
    public function getAllSettings(): array
    {
        return $this->settingsCache;
    }

    /**
     * Return settings for frontend
     *
     * @return mixed[]
     */
    public function getFrontendSettings(): array
    {
//        $capabilities = [];
//
//        if (is_admin()) {
//            $currentScreenId = get_current_screen()->id;
//
//            $currentScreen = substr($currentScreenId, strrpos($currentScreenId, '-') + 1);
//
//            $capabilities = [
//                'canRead'        => current_user_can('ivyforms_read_' . $currentScreen),
//                'canReadOthers'  => current_user_can('ivyforms_read_others_' . $currentScreen),
//                'canWrite'       => current_user_can('ivyforms_write_' . $currentScreen),
//                'canWriteOthers' => current_user_can('ivyforms_write_others_' . $currentScreen),
//                'canDelete'      => current_user_can('ivyforms_delete_' . $currentScreen),
//                'canWriteStatus' => current_user_can('ivyforms_write_status_' . $currentScreen),
//            ];
//        }

        return [
            //'capabilities' => $capabilities,
            'general'      => [
                'fullscreen' => $this->getSetting('general', 'fullscreen'),
            ],
            'wordpress'    => [
                'dateFormat'  => $this->getSetting('wordpress', 'dateFormat'),
                'timeFormat'  => $this->getSetting('wordpress', 'timeFormat'),
                'startOfWeek' => (int)$this->getSetting('wordpress', 'startOfWeek'),
                'timezone'    => $this->getSetting('wordpress', 'timeZoneString'),
            ],
        ];
    }

    /**
     * @param string $settingCategoryKey
     * @param string $settingKey
     * @param mixed  $settingValue
     *
     * @return void
     */
    public function setSetting(string $settingCategoryKey, string $settingKey, $settingValue)
    {
        $this->settingsCache[$settingCategoryKey][$settingKey] = $settingValue;

        $settingsCopy = $this->settingsCache;

        unset($settingsCopy['wordpress']);

        update_option('ivyforms_settings', json_encode($settingsCopy));
    }

    /**
     * @param string $settingCategoryKey
     * @param mixed  $settingValue
     *
     * @return void
     */
    public function setCategorySettings(string $settingCategoryKey, $settingValue)
    {
        $this->settingsCache[$settingCategoryKey] = $settingValue;

        $settingsCopy = $this->settingsCache;

        unset($settingsCopy['wordpress']);

        update_option('ivyforms_settings', json_encode($settingsCopy));
    }

    /**
     * Get all settings
     *
     * Specify that $settings is an associative array with string keys and mixed values
     * @param array<string, mixed> $settings
     *
     */
    public function setAllSettings(array $settings): void
    {
        foreach ($settings as $settingCategoryKey => $settingValues) {
            $this->settingsCache[$settingCategoryKey] = $settingValues;
        }

        $settingsCopy = $this->settingsCache;

        unset($settingsCopy['wordpress']);

        update_option('ivyforms_settings', json_encode($settingsCopy));
    }
}
