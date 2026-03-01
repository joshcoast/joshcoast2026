<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class SickLeaveRequestFormTemplate
{
    /**
     * Get the sick leave request form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'sick_leave_request_form',
            'name' => BackendStrings::getTemplateStrings()['sick_leave_request_form'],
            'description' => BackendStrings::getTemplateStrings()['sick_leave_request_form_desc'],
            'category' => 'leave-request',
            'subcategory' => 'leave-request-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'sick-leave-request-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['sick_leave_request_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => self::getFormFields(),
                'settings' => []
            ]
        ];
    }

    /**
     * Get all form fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getFormFields(): array
    {
        return [
            self::getNameField(),
            self::getFirstNameField(),
            self::getLastNameField(),
            self::getEmailField(),
            self::getDepartmentField(),
            self::getSickLeaveStartDateField(),
            self::getSickLeaveEndDateField(),
            self::getDoctorsNoteField(),
            self::getNotifySupervisorField(),
        ];
    }

    /**
     * Get name field (parent)
     *
     * @return array<string, mixed>
     */
    private static function getNameField(): array
    {
        return [
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
        ];
    }

    /**
     * Get first name field (child of name field)
     *
     * @return array<string, mixed>
     */
    private static function getFirstNameField(): array
    {
        return [
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
        ];
    }

    /**
     * Get last name field (child of name field)
     *
     * @return array<string, mixed>
     */
    private static function getLastNameField(): array
    {
        return [
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
        ];
    }

    /**
     * Get email field
     *
     * @return array<string, mixed>
     */
    private static function getEmailField(): array
    {
        return [
            'id' => 0,
            'fieldIndex' => 2,
            'type' => 'email',
            'label' => BackendStrings::getTemplateStrings()['email_address'],
            'placeholder' => 'name@example.com',
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 2,
        ];
    }

    /**
     * Get department field
     *
     * @return array<string, mixed>
     */
    private static function getDepartmentField(): array
    {
        return [
            'id' => 0,
            'fieldIndex' => 3,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['department'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_department'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 3,
            'fieldOptions' => self::getDepartmentOptions(),
        ];
    }

    /**
     * Get department field options
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDepartmentOptions(): array
    {
        return [
            [
                'id' => 0,
                'label' => BackendStrings::getTemplateStrings()['hr'],
                'value' => 'hr',
                'isDefault' => false,
                'position' => 1
            ],
            [
                'id' => 0,
                'label' => BackendStrings::getTemplateStrings()['it'],
                'value' => 'it',
                'isDefault' => false,
                'position' => 2
            ],
            [
                'id' => 0,
                'label' => BackendStrings::getTemplateStrings()['sales'],
                'value' => 'sales',
                'isDefault' => false,
                'position' => 3
            ],
            [
                'id' => 0,
                'label' => BackendStrings::getTemplateStrings()['marketing'],
                'value' => 'marketing',
                'isDefault' => false,
                'position' => 4
            ],
            [
                'id' => 0,
                'label' => BackendStrings::getTemplateStrings()['other'],
                'value' => 'other',
                'isDefault' => false,
                'position' => 5
            ],
        ];
    }

    /**
     * Get sick leave start date field
     *
     * @return array<string, mixed>
     */
    private static function getSickLeaveStartDateField(): array
    {
        return [
            'id' => 0,
            'fieldIndex' => 4,
            'type' => 'date',
            'label' => BackendStrings::getTemplateStrings()['sick_leave_start_date'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_start_date'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 5,
        ];
    }

    /**
     * Get sick leave end date field
     *
     * @return array<string, mixed>
     */
    private static function getSickLeaveEndDateField(): array
    {
        return [
            'id' => 0,
            'fieldIndex' => 5,
            'type' => 'date',
            'label' => BackendStrings::getTemplateStrings()['sick_leave_end_date'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_end_date'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 6,
        ];
    }

    /**
     * Get doctor's note field
     *
     * @return array<string, mixed>
     */
    private static function getDoctorsNoteField(): array
    {
        return [
            'id' => 0,
            'fieldIndex' => 6,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['doctors_note'],
            'placeholder' => BackendStrings::getTemplateStrings()['attach_doctors_note_if_required'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 7,
        ];
    }

    /**
     * Get notify supervisor field
     *
     * @return array<string, mixed>
     */
    private static function getNotifySupervisorField(): array
    {
        return [
            'id' => 0,
            'fieldIndex' => 7,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['notify_supervisor'],
            'placeholder' => '',
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 8,
            'fieldOptions' => self::getNotifySupervisorOptions(),
        ];
    }

    /**
     * Get notify supervisor field options
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getNotifySupervisorOptions(): array
    {
        return [
            [
                'id' => 0,
                'label' => BackendStrings::getTemplateStrings()['yes'],
                'value' => 'yes',
                'isDefault' => false,
                'position' => 1
            ],
            [
                'id' => 0,
                'label' => BackendStrings::getTemplateStrings()['no'],
                'value' => 'no',
                'isDefault' => false,
                'position' => 2
            ],
        ];
    }
}
