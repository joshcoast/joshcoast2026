<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class InstallationWorkOrderFormTemplate
{
    /**
     * Get the installation work order form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'installation_work_order_form',
            'name' => BackendStrings::getTemplateStrings()['installation_work_order_form'],
            'description' => BackendStrings::getTemplateStrings()['installation_work_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'work-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'installation-work-order-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['installation_work_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getContactInformationFields(),
                    self::getInstallationDetailsFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
        ];
    }

    /**
     * Get contact information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getContactInformationFields(): array
    {
        return [
            // Full Name
            [
                'id' => 0,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['full_name'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'required' => true,
                'readonly' => false,
                'position' => 1,
            ],
            // First Name - Child of Name Field
            [
                'id' => 0,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['first_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_name'],
                'required' => true,
                'parentId' => 0,
                'fieldIndex' => 1,
                'defaultValue' => '',
                'position' => 1,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField1']),
            ],
            // Last Name - Child of Name Field
            [
                'id' => 0,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['last_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_last_name'],
                'required' => true,
                'parentId' => 0,
                'fieldIndex' => 1,
                'defaultValue' => '',
                'position' => 2,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField2']),
            ],
            // Email Address
            [
                'id' => 0,
                'fieldIndex' => 2,
                'type' => 'email',
                'label' => BackendStrings::getTemplateStrings()['email_address'],
                'placeholder' => 'name@example.com',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
                'validation' => [
                    'required' => true,
                    'email' => true
                ],
            ],
        ];
    }

    /**
     * Get installation details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getInstallationDetailsFields(): array
    {
        return [
            // Equipment/Device Name
            [
                'id' => 0,
                'fieldIndex' => 3,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['equipment_device_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_device_name'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
            ],
            // Room/Area
            [
                'id' => 0,
                'fieldIndex' => 4,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['room_area'],
                'placeholder' => BackendStrings::getTemplateStrings()['specify_installation_location'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
            // Installation Type Radio
            [
                'id' => 0,
                'fieldIndex' => 5,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['installation_type'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['standard'],
                        'value' => 'standard',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['custom'],
                        'value' => 'custom',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['scheduled'],
                        'value' => 'scheduled',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                ],
            ],
            // Requested Installation Date
            [
                'id' => 0,
                'fieldIndex' => 6,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['requested_installation_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_requested_installation_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
            ],
            // Installer Required Dropdown
            [
                'id' => 0,
                'fieldIndex' => 7,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['installer_required'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_installer'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['internal_team'],
                        'value' => 'internal_team',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['external_vendor'],
                        'value' => 'external_vendor',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['both'],
                        'value' => 'both',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get additional information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalInformationFields(): array
    {
        return [
            // Installation Instructions
            [
                'id' => 0,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['installation_instructions'],
                'placeholder' => BackendStrings::getTemplateStrings()['provide_special_requirements'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'rows' => 4,
            ],
            // Mark as Urgent
            [
                'id' => 0,
                'fieldIndex' => 9,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['mark_as_urgent'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                ],
            ],
        ];
    }
}
