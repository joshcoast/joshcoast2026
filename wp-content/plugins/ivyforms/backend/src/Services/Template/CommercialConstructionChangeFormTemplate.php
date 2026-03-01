<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class CommercialConstructionChangeFormTemplate
{
    /**
     * Get the commercial construction change form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'commercial_construction_change_form',
            'name' => BackendStrings::getTemplateStrings()['commercial_construction_change_form'],
            'description' => BackendStrings::getTemplateStrings()['commercial_construction_change_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'change-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'commercial-construction-change-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['commercial_construction_change_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getContactInformationFields(),
                    self::getProjectDetailsFields(),
                    self::getChangeRequestFields()
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
            // Requestor Name
            [
                'id' => 0,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['requestor_name'],
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
            // Contact Number
            [
                'id' => 0,
                'fieldIndex' => 3,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['contact_number'],
                'placeholder' => '+1 (_) _-_',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Get project details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProjectDetailsFields(): array
    {
        return array_merge(
            self::getProjectLocationFields(),
            self::getProjectInfoFields()
        );
    }

    /**
     * Get project location address fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProjectLocationFields(): array
    {
        return [
            // Project Location - Parent
            [
                'id' => 0,
                'fieldIndex' => 4,
                'type' => 'address',
                'label' => BackendStrings::getTemplateStrings()['project_location'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'readonly' => false,
                'position' => 4,
            ],
            // Address Line 1 - Child of Address Field
            [
                'id' => 0,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_1'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_1'],
                'required' => true,
                'parentId' => 0,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 1,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'streetAddress',
                'settings' => json_encode([
                    'type' => 'streetAddress',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_1'],
                    'requiredMessage' => '',
                    'visible' => true,
                ]),
            ],
            // Address Line 2 - Child of Address Field
            [
                'id' => 0,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_2'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_2'],
                'required' => false,
                'parentId' => 0,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 2,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'addressLine2',
                'settings' => json_encode([
                    'type' => 'addressLine2',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_2'],
                    'requiredMessage' => '',
                    'visible' => true,
                ]),
            ],
            // City - Child of Address Field
            [
                'id' => 0,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['city'],
                'placeholder' => BackendStrings::getTemplateStrings()['city'],
                'required' => true,
                'parentId' => 0,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 3,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'city',
                'settings' => json_encode([
                    'type' => 'city',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['city'],
                    'requiredMessage' => '',
                    'visible' => true,
                ]),
            ],
            // State/Province - Child of Address Field
            [
                'id' => 0,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['state_province'],
                'placeholder' => BackendStrings::getTemplateStrings()['state'],
                'required' => true,
                'parentId' => 0,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 4,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'state',
                'settings' => json_encode([
                    'type' => 'state',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['state'],
                    'requiredMessage' => '',
                    'visible' => true,
                ]),
            ],
            // Zip/Postal Code - Child of Address Field
            [
                'id' => 0,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['zip_postal_code'],
                'placeholder' => BackendStrings::getTemplateStrings()['zip'],
                'required' => true,
                'parentId' => 0,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 5,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'zip',
                'settings' => json_encode([
                    'type' => 'zip',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['zip'],
                    'requiredMessage' => '',
                    'visible' => true,
                ]),
            ],
            // Country - Child of Address Field
            [
                'id' => 0,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['country'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_country'],
                'required' => true,
                'parentId' => 0,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 6,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'country',
                'settings' => json_encode([
                    'type' => 'country',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['select_country'],
                    'requiredMessage' => '',
                    'visible' => true,
                ]),
            ],
        ];
    }

    /**
     * Get project info fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProjectInfoFields(): array
    {
        return [
            // Company Name
            [
                'id' => 0,
                'fieldIndex' => 5,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['company_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_company_name'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
            ],
            // Commercial Space Type Dropdown
            [
                'id' => 0,
                'fieldIndex' => 6,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['commercial_space_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_commercial_space_type'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['office'],
                        'value' => 'office',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['retail'],
                        'value' => 'retail',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['warehouse'],
                        'value' => 'warehouse',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['industrial'],
                        'value' => 'industrial',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 5,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get change request fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getChangeRequestFields(): array
    {
        return [
            // Change Request Date
            [
                'id' => 0,
                'fieldIndex' => 7,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['change_request_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_change_request_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
            // Description of Change
            [
                'id' => 0,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['description_of_change'],
                'placeholder' => BackendStrings::getTemplateStrings()['describe_the_change_requested'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'rows' => 4,
            ],
            // Estimated Financial Impact
            [
                'id' => 0,
                'fieldIndex' => 9,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['estimated_financial_impact'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_estimated_financial_impact'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
            ],
            // Requires Management Approval? Radio
            [
                'id' => 0,
                'fieldIndex' => 10,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['requires_management_approval'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
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
