<?php

/**
 * Multisite hook on new site activation
 */

namespace IvyForms\Services\InstallActions;

// phpcs:disable PSR1.Files.SideEffects
use IvyForms\Common\Exceptions\InvalidArgumentException;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class ActivationNewSiteMultisite
 *
 * @package IvyForms\Services\InstallActions
 */
class ActivationNewSiteMultisite
{
    /**
     * Activate the plugin for every newly created site if the plugin is network activated
     *
     * @param int $siteId
     * @throws InvalidArgumentException
     */
    public static function init(int $siteId): void
    {
        if (! function_exists('is_plugin_active_for_network')) {
            if (file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
        }

        if (
            defined('IVYFORMS_PLUGIN_SLUG') &&
            is_plugin_active_for_network(IVYFORMS_PLUGIN_SLUG) // @phpstan-ignore argument.type
        ) {
            switch_to_blog($siteId);
            //Create database table if not exists
            ActivationDatabaseHook::init();
            restore_current_blog();
        }
    }
}
