<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class FestivalWaiverFormTemplate
{
    /**
     * Get the festival waiver form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'festival_waiver_form',
            'name' => BackendStrings::getTemplateStrings()['festival_waiver_form'],
            'description' => BackendStrings::getTemplateStrings()['festival_waiver_form_desc'],
            'category' => 'waiver-forms',
            'subcategory' => 'participation-waiver-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'festival-participation-waiver-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['festival_waiver_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => [],
                'settings' => []
            ]
        ];
    }
}
