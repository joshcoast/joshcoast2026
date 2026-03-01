<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class SportsTeamPhotoConsentFormTemplate
{
    /**
     * Get the sports photo consent form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'sports_team_photo_consent_form',
            'name' => BackendStrings::getTemplateStrings()['sports_team_photo_consent_form'],
            'description' => BackendStrings::getTemplateStrings()['sports_team_photo_consent_form_desc'],
            'category' => 'consent-forms',
            'subcategory' => 'photo-consent-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'sports-team-photo-consent-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['sports_team_photo_consent_form'],
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
