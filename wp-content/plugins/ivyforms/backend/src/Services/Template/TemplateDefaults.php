<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TemplateDefaults
 * Holds default configuration values for form templates
 *
 * @package IvyForms\Services\Template
 */
class TemplateDefaults
{
    /**
     * Default integration settings for new forms
     *
     * @var array<string, array<string, mixed>>
     */
    public const DEFAULT_INTEGRATION_SETTINGS = [
        'wpdatatables' => [
            'enabled' => true
        ]
    ];

    /**
     * Get default integration settings
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getDefaultIntegrationSettings(): array
    {
        return self::DEFAULT_INTEGRATION_SETTINGS;
    }
}
