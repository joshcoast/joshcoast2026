<?php

/**
 * Settings hook for activation
 */

namespace IvyForms\Services\InstallActions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Exception;
use IvyForms\Services\Settings\SettingsService;

/**
 * Class ActivationSettingsHook
 *
 * @package IvyForms\Services\InstallActions
 */
class ActivationSettingsHook
{
    /**
     * Initialize the plugin
     *
     * @throws Exception
     */
    public static function init(): void
    {
        self::initGeneralSettings();
        self::initIntegrationsSettings();
    }

    /**
     * @param string $category
     * @param mixed[] $settings
     */
    public static function initSettings(string $category, array $settings): void
    {
        $settingsStorage = new SettingsService();

        if (!$settingsStorage->getCategorySettings($category)) {
            $settingsStorage->setCategorySettings(
                $category,
                []
            );
        }

        foreach ($settings as $key => $value) {
            if (null === $settingsStorage->getSetting($category, $key)) {
                $settingsStorage->setSetting(
                    $category,
                    $key,
                    $value
                );
            }
        }
    }

    /**
     * Init General Settings
     */
    private static function initGeneralSettings(): void
    {

        $settings = [
            'version'           => IVYFORMS_VERSION,
            'changelog_version' => '',
            'fullscreen'        => false,
        ];

        self::initSettings('general', $settings);
    }

    /**
     * Init Integrations Settings
     */
    private static function initIntegrationsSettings(): void
    {
        $settings = [
            'wpdatatables' => [
                'enabled' => false,
            ],
            'mailchimp' => [
                'enabled' => false,
            ],
        ];

        self::initSettings('integrations', $settings);
    }
}
