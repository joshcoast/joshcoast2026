<?php

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Translations\BackendStrings;

class EducationConferenceRegistrationFormTemplate
{
    /**
     * Get the education conference registration form template
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return [
            'id' => 'education_conference_registration_form',
            'name' => BackendStrings::getTemplateStrings()['education_conference_registration_form'],
            'description' => BackendStrings::getTemplateStrings()['education_conference_registration_form_desc'],
            'category' => 'event-registration-forms',
            'subcategory' => 'conference-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'education-conference-registration-form.svg',
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['education_conference_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getParticipantInformationFields(),
                    self::getConferencePreferencesFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
        ];
    }

    /**
     * Get participant information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getParticipantInformationFields(): array
    {
        return [
            // Full Name
            [
                'id' => 0,
                'fieldIndex' => 1,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['full_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_full_name'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 1,
                'validation' => [
                    'required' => true,
                ],
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
     * Get conference preferences fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getConferencePreferencesFields(): array
    {
        return [
            // Role Dropdown
            [
                'id' => 0,
                'fieldIndex' => 3,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['role'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_role'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['teacher'],
                        'value' => 'teacher',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['principal'],
                        'value' => 'principal',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['researcher'],
                        'value' => 'researcher',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['student'],
                        'value' => 'student',
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
            // Sessions to Attend (Multidropdown)
            [
                'id' => 0,
                'fieldIndex' => 4,
                'type' => 'multi-select',
                'label' => BackendStrings::getTemplateStrings()['sessions_to_attend'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_sessions'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['session_1'],
                        'value' => 'session_1',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['session_2'],
                        'value' => 'session_2',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['session_3'],
                        'value' => 'session_3',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                ],
            ],
            // Participation Type (Radio)
            [
                'id' => 0,
                'fieldIndex' => 5,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['participation_type'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['in_person'],
                        'value' => 'in_person',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 0,
                        'label' => BackendStrings::getTemplateStrings()['virtual'],
                        'value' => 'virtual',
                        'isDefault' => false,
                        'position' => 2,
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
            // Questions or Comments
            [
                'id' => 0,
                'fieldIndex' => 6,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['questions_or_comments'],
                'placeholder' => BackendStrings::getTemplateStrings()['write_questions_or_comments_for_organizers'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'rows' => 4,
            ],
        ];
    }
}
