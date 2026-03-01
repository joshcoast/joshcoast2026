<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

use IvyForms\Services\Translations\BackendStrings;

class CharityRunWaiverFormTemplate
{
    /**
     * Get the charity run waiver form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'charity_run_waiver_form',
            'name' => BackendStrings::getTemplateStrings()['charity_run_waiver_form'],
            'description' => BackendStrings::getTemplateStrings()['charity_run_waiver_form_desc'],
            'category' => 'waiver-forms',
            'subcategory' => 'participation-waiver-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'charity-run-waiver-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['charity_run_waiver_form'],
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
