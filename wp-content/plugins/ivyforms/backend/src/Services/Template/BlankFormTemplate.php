<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class BlankFormTemplate
{
    /**
     * Get the blank form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'blank_form',
            'name' => BackendStrings::getSettingsFormBuilderStrings()['blank_form'],
            'description' => '',
            'category' => 'basic',
            'is_pro' => false,
            'screenshot' => '',
            'form_data' => [
                'name' => BackendStrings::getSettingsFormBuilderStrings()['blank_form'],
                'description' => '',
                'published'     => 1,
                'showTitle'     => 1,
                'storeEntries'  => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => [],
                'settings' => []
            ]
        ];
    }
}
