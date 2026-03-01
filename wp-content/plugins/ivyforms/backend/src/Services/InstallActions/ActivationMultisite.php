<?php

/**
 * Network activation
 */

namespace IvyForms\Services\InstallActions;

// phpcs:disable PSR1.Files.SideEffects
use IvyForms\Common\Exceptions\InvalidArgumentException;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class ActivationMultisite
 *
 * @package IvyForms\Services\InstallActions
 */
class ActivationMultisite
{
    /**
     * Activate the plugin for every sub-site separately
     * @throws InvalidArgumentException
     */
    public static function init(): void
    {
        global $wpdb;

        // Get current blog id
        $oldSite = $wpdb->blogid;
        // Get all blog ids
        $siteIds = $wpdb->get_col(
            $wpdb->prepare("SELECT blog_id FROM $wpdb->blogs")
        );

        foreach ($siteIds as $siteId) {
            switch_to_blog($siteId);
            // Create database table if not exists
            ActivationDatabaseHook::init();
        }
        // Returns to current blog
        switch_to_blog($oldSite);
    }
}
