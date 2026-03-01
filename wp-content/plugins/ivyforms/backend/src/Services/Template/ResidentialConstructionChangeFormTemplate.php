<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class ResidentialConstructionChangeFormTemplate
{
    /**
     * Get the residential construction change form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'residential_construction_change_form',
            'name' => BackendStrings::getTemplateStrings()['residential_construction_change_form'],
            'description' => BackendStrings::getTemplateStrings()['residential_construction_change_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'change-order-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'residential-construction-change-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['residential_construction_change_form'],
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
