<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class VacationLeaveRequestFormTemplate
{
    /**
     * Get the vacation leave request form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'vacation_leave_request_form',
            'name' => BackendStrings::getTemplateStrings()['vacation_leave_request_form'],
            'description' => BackendStrings::getTemplateStrings()['vacation_leave_request_form_desc'],
            'category' => 'leave-request',
            'subcategory' => 'leave-request-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'vacation-leave-request-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['vacation_leave_request_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getEmployeeInformationFields(),
                    self::getLeaveDetailsFields(),
                    self::getApprovalFields()
                ),
                'settings' => []
            ]
        ];
    }

    /**
     * Get employee information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEmployeeInformationFields(): array
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
                'placeholder' => 'name@example.com',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
            ],
            // Department
            [
                'id' => 0,
                'fieldIndex' => 3,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['department'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_department'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'fieldOptions' => [
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
                        'label' => BackendStrings::getTemplateStrings()['marketing'],
                        'value' => 'marketing',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['finance'],
                        'value' => 'finance',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['sales'],
                        'value' => 'sales',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['operations'],
                        'value' => 'operations',
                        'isDefault' => false,
                        'position' => 6
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 7
                    ],
                ]
            ],
        ];
    }

    /**
     * Get leave details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getLeaveDetailsFields(): array
    {
        return [
            // Type of Leave
            [
                'id' => 0,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['type_of_leave'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_type_of_leave'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['annual_leave'],
                        'value' => 'annual_leave',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['sick_leave'],
                        'value' => 'sick_leave',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['personal_day'],
                        'value' => 'personal_day',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['maternity_leave'],
                        'value' => 'maternity_leave',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['paternity_leave'],
                        'value' => 'paternity_leave',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['study_leave'],
                        'value' => 'study_leave',
                        'isDefault' => false,
                        'position' => 6
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['family_care_leave'],
                        'value' => 'family_care_leave',
                        'isDefault' => false,
                        'position' => 7
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['unpaid_leave'],
                        'value' => 'unpaid_leave',
                        'isDefault' => false,
                        'position' => 8
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 9
                    ],
                ]
            ],
            // Leave Start Date
            [
                'id' => 0,
                'fieldIndex' => 5,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['leave_start_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_start_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
            ],
            // Leave End Date
            [
                'id' => 0,
                'fieldIndex' => 6,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['leave_end_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_end_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
            ],
            // Total Number of Days
            [
                'id' => 0,
                'fieldIndex' => 7,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['total_number_of_days'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_total_number_of_days'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'minValue' => 1,
                'maxValue' => 365,
            ],
            // Partial / Full Day
            [
                'id' => 0,
                'fieldIndex' => 8,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['partial_full_day'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_day_type'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['full_day'],
                        'value' => 'full_day',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['half_day_morning'],
                        'value' => 'half_day_morning',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['half_day_afternoon'],
                        'value' => 'half_day_afternoon',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Reason / Comments
            [
                'id' => 0,
                'fieldIndex' => 9,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['reason_comments'],
                'placeholder' => BackendStrings::getTemplateStrings()['provide_any_context_or_notes'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
            ],
        ];
    }

    /**
     * Get approval fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getApprovalFields(): array
    {
        return [
            // Supervisor Approval Required?
            [
                'id' => 0,
                'fieldIndex' => 10,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['supervisor_approval_required'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'fieldOptions' => [
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
                ]
            ],
            // Backup / Coverage Plan
            [
                'id' => 0,
                'fieldIndex' => 11,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['backup_coverage_plan'],
                'placeholder' => BackendStrings::getTemplateStrings()['name_of_colleague_covering_duties'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getComponentsStrings()['option_1'],
                        'value' => 'option1',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getComponentsStrings()['option_2'],
                        'value' => 'option2',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                ]
            ],
            // Additional Notes
            [
                'id' => 0,
                'fieldIndex' => 12,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['additional_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['any_extra_information'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 11,
            ],
        ];
    }
}
