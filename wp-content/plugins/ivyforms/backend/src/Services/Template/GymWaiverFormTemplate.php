<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class GymWaiverFormTemplate
{
    /**
     * Get the gym liability waiver form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'gym_waiver_form',
            'name' => BackendStrings::getTemplateStrings()['gym_waiver_form'],
            'description' => BackendStrings::getTemplateStrings()['gym_waiver_form_desc'],
            'category' => 'waiver-forms',
            'subcategory' => 'liability-waiver-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'gym-liability-waiver-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['gym_waiver_form'],
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
