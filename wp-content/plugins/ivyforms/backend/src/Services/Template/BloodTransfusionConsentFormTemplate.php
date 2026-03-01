<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class BloodTransfusionConsentFormTemplate
{
    /**
     * Get the blood transfusion consent form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'blood_transfusion_consent_form',
            'name' => BackendStrings::getTemplateStrings()['blood_transfusion_consent_form'],
            'description' => BackendStrings::getTemplateStrings()['blood_transfusion_consent_form_desc'],
            'category' => 'consent-forms',
            'subcategory' => 'medical-consent-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'blood-transfusion-consent-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['blood_transfusion_consent_form'],
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
