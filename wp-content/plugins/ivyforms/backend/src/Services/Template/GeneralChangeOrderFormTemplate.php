<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class GeneralChangeOrderFormTemplate
{
    /**
     * Get the general change order form template (lite)
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'general_change_order_form',
            'name' => BackendStrings::getTemplateStrings()['general_change_order_form'],
            'description' => BackendStrings::getTemplateStrings()['general_change_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'change-order-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'general-change-order-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['general_change_order_form'],
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
