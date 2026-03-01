<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class PhotoVideoConsentFormTemplate
{
    /**
     * Get the photo & video consent form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'photo_video_consent_form',
            'name' => BackendStrings::getTemplateStrings()['photo_video_consent_form'],
            'description' => BackendStrings::getTemplateStrings()['photo_video_consent_form_desc'],
            'category' => 'consent-forms',
            'subcategory' => 'photo-consent-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'photo-video-consent-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['photo_video_consent_form'],
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
