<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class VolunteerEventWaiverFormTemplate
{
    /**
     * Get the volunteer event participation waiver form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'volunteer_event_waiver_form',
            'name' => BackendStrings::getTemplateStrings()['volunteer_event_waiver_form'],
            'description' => BackendStrings::getTemplateStrings()['volunteer_event_waiver_form_desc'],
            'category' => 'waiver-forms',
            'subcategory' => 'participation-waiver-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'volunteer-event-participation-waiver-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['volunteer_event_waiver_form'],
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
