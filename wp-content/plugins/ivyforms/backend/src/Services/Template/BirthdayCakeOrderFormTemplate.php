<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class BirthdayCakeOrderFormTemplate
{
    /**
     * Get the birthday cake order form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'birthday_cake_order_form',
            'name' => BackendStrings::getTemplateStrings()['birthday_cake_order_form'],
            'description' => BackendStrings::getTemplateStrings()['birthday_cake_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'cake-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'birthday-cake-order-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['birthday_cake_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getCustomerInformationFields(),
                    self::getCakeDetailsFields(),
                    self::getDeliveryDetailsFields()
                ),
                'settings' => []
            ]
        ];
    }

    /**
     * Get customer information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCustomerInformationFields(): array
    {
        return [
            // Name Field - Parent
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
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_first_name'],
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
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_email'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
            ],
            // Phone Number
            [
                'id' => 0,
                'fieldIndex' => 3,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_phone_number'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Get cake details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCakeDetailsFields(): array
    {
        return [
            // Cake Flavor
            [
                'id' => 0,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['cake_flavor'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_cake_flavor'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['chocolate'],
                        'value' => 'chocolate',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['vanilla'],
                        'value' => 'vanilla',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['red_velvet'],
                        'value' => 'red_velvet',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['lemon'],
                        'value' => 'lemon',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['custom'],
                        'value' => 'custom',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ]
            ],
            // Cake Size
            [
                'id' => 0,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['cake_size'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_cake_size'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['small_6_inch'],
                        'value' => 'small_6',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['medium_8_inch'],
                        'value' => 'medium_8',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['large_10_inch'],
                        'value' => 'large_10',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Number of Guests
            [
                'id' => 0,
                'fieldIndex' => 6,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['number_of_guests'],
                'placeholder' => BackendStrings::getTemplateStrings()['eg_20'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'minValue' => 1,
                'maxValue' => 500,
            ],
            // Birthday Message on Cake
            [
                'id' => 0,
                'fieldIndex' => 7,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['birthday_message_on_cake'],
                'placeholder' => BackendStrings::getTemplateStrings()['happy_10th_birthday'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
            // Special Requests / Design Ideas
            [
                'id' => 0,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['special_requests_design_ideas'],
                'placeholder' => BackendStrings::getTemplateStrings()['describe_cake_design_decorations'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'rows' => 4,
            ],
        ];
    }

    /**
     * Get delivery details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDeliveryDetailsFields(): array
    {
        return [
            // Delivery Date
            [
                'id' => 0,
                'fieldIndex' => 9,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['delivery_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_delivery_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Delivery Address Field - Parent
            [
                'id' => 0,
                'fieldIndex' => 10,
                'type' => 'address',
                'label' => BackendStrings::getTemplateStrings()['delivery_address'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'readonly' => false,
                'position' => 10,
                'required' => false,
            ],
            // Address Line 1 - Child of Address Field
            [
                'id' => 0,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_1'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_1'],
                'required' => true,
                'parentId' => 0,
                'fieldIndex' => 10,
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
                    'visible' => true
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
                'fieldIndex' => 10,
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
                    'visible' => true
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
                'fieldIndex' => 10,
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
                    'visible' => true
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
                'fieldIndex' => 10,
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
                    'visible' => true
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
                'fieldIndex' => 10,
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
                    'visible' => true
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
                'fieldIndex' => 10,
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
                    'visible' => true
                ]),
            ],
        ];
    }
}
