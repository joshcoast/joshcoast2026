<?php

namespace IvyForms\Services\Translations;

/**
 * Class FrontendStrings
 *
 * @package IvyForms\Translations
 *
 * @SuppressWarnings(ExcessiveMethodLength)
 * @SuppressWarnings(ExcessiveClassLength)
 * @phpcs:disable
 */
class FrontendStrings
{

    /**
     * Returns the array for the form render
     *
     * @return mixed[]
     */
    public static function getFormRenderStrings(): array
    {
        return [
            'all_form_fields'                    => __('All form fields:', 'ivyforms'),
            'am'                                 => __('AM', 'ivyforms'),
            'am_pm'                              => __('AM/PM', 'ivyforms'),
            'api_response_for_field'             => __('API response for field {fieldKey}:', 'ivyforms'),
            'checking_field_with_value'          => __('Checking field {fieldKey} (ID: {fieldId}) with value:', 'ivyforms'),
            'confirmation_label_placeholder'     => __('Confirm Email', 'ivyforms'),
            'day'                                => __('Day', 'ivyforms'),
            'duplicate_found_for_field'          => __('Duplicate found for field {fieldKey}:', 'ivyforms'),
            'duplicate_value_error'              => __('This value must be unique. Please enter a unique value.', 'ivyforms'),
            'duplicate_value_error_label'        => __('This {label} must be unique. Please use a different value.', 'ivyforms'),
            'do_not_match'                       => __(' do not match.', 'ivyforms'),
            'emails_do_not_match'                => __('Email addresses do not match', 'ivyforms'),
            'enter_email_address'                => __('Please enter a valid email address.', 'ivyforms'),
            'enter_valid_date'                   => __('Please enter a valid date.', 'ivyforms'),
            'enter_date_valid_format'            => __('Please enter date in valid format.', 'ivyforms'),
            'date_must_be_after'                 => __('Date must be after {min}.', 'ivyforms'),
            'date_must_be_before'                => __('Date must be before {max}.', 'ivyforms'),
            'enter_valid_number'                 => __('Please enter a valid number.', 'ivyforms'),
            'enter_phone_number'                 => __('Please enter a valid phone number.', 'ivyforms'),
            'enter_valid_time'                   => __('Please enter a valid time.', 'ivyforms'),
            'enter_valid_website'                => __('Please enter a valid website URL.', 'ivyforms'),
            'error_checking_duplicates'          => __('Error checking for duplicate values. Please try again.', 'ivyforms'),
            'error_checking_duplicates_for_field'=> __('Error checking duplicates for field {fieldKey}:', 'ivyforms'),
            'error_submitting_form'              => __('Error submitting form:', 'ivyforms'),
            'error_page_url'                     => __('Error fetching page URL:', 'ivyforms'),
            'fields_to_check_for_duplicates'     => __('Fields to check for duplicates:', 'ivyforms'),
            'final_duplicate_errors'             => __('Final duplicate errors:', 'ivyforms'),
            'flag'                               => __('Country flag icon', 'ivyforms'),
            'form_data_not_found'                => __('Form data not found (wpIvyFormData is undefined)', 'ivyforms'),
            'form_submitted'                     => __('Form submitted successfully!', 'ivyforms'),
            'hours'                              => __('Hours', 'ivyforms'),
            'invalid_form_data'                  => __('Invalid form data:', 'ivyforms'),
            'invalid_input'                      => __('Invalid input', 'ivyforms'),
            'making_api_call_for_field'          => __('Making API call for field {fieldKey}', 'ivyforms'),
            'minutes'                            => __('Minutes', 'ivyforms'),
            'month'                              => __('Month', 'ivyforms'),
            'no_fields_with_no_duplicates'       => __('No fields with noDuplicates enabled', 'ivyforms'),
            'not_selected'                       => __('Not Selected', 'ivyforms'),
            'number_out_of_range'                => __('Value must be between {min} and {max}.', 'ivyforms'),
            'pm'                                 => __('PM', 'ivyforms'),
            'number_cannot_be_smaller_than'      => __('Value cannot be smaller than {min}.', 'ivyforms'),
            'number_cannot_be_greater_than'      => __('Value cannot be greater than {max}.', 'ivyforms'),
            'phone_number'                       => __('Phone number', 'ivyforms'),
            'range_from'                         => __('Range from', 'ivyforms'),
            'range_to'                           => __('Range to', 'ivyforms'),
            'recaptcha_complete_verification'    => __('Please complete the reCAPTCHA verification', 'ivyforms'),
            'recaptcha_expired'                  => __('reCAPTCHA expired. Please try again.', 'ivyforms'),
            'recaptcha_invisible_failed'         => __('Failed to execute reCAPTCHA invisible', 'ivyforms'),
            'recaptcha_not_configured'           => __('reCAPTCHA is not configured. Please contact the site administrator.', 'ivyforms'),
            'recaptcha_v3_failed'                => __('Failed to execute reCAPTCHA v3', 'ivyforms'),
            'recaptcha_widget_not_initialized'   => __('reCAPTCHA widget not initialized', 'ivyforms'),
            'turnstile_not_configured_public'    => __('Turnstile is not configured. Please contact the site administrator.', 'ivyforms'),
            'turnstile_expired'                  => __('Turnstile verification expired. Please try again.', 'ivyforms'),
            'turnstile_init_failed'              => __('Failed to initialize Turnstile', 'ivyforms'),
            'turnstile_error'                    => __('Turnstile verification error. Please try again.', 'ivyforms'),
            'turnstile_complete_verification'    => __('Please complete the Turnstile verification.', 'ivyforms'),
            'turnstile_verification_failed'      => __('Turnstile verification failed, please try again.', 'ivyforms'),
            'hcaptcha_not_configured_public'     => __('hCaptcha is not configured. Please contact the site administrator.', 'ivyforms'),
            'hcaptcha_expired'                   => __('hCaptcha verification expired. Please try again.', 'ivyforms'),
            'hcaptcha_init_failed'               => __('Failed to initialize hCaptcha', 'ivyforms'),
            'hcaptcha_error'                     => __('hCaptcha verification error. Please try again.', 'ivyforms'),
            'hcaptcha_complete_verification'     => __('Please complete the hCaptcha verification.', 'ivyforms'),
            'hcaptcha_verification_failed'       => __('hCaptcha verification failed, please try again.', 'ivyforms'),
            'required'                           => __('is required.', 'ivyforms'),
            'select_country'                     => __('Select country', 'ivyforms'),
            'skipping_empty_field'               => __('Skipping empty field {fieldKey}', 'ivyforms'),
            'submit'                             => __('Submit', 'ivyforms'),
            'thank_you'                          => __('Thank you!', 'ivyforms'),
            'this_field'                         => __('This field', 'ivyforms'),
            'year'                               => __('Year', 'ivyforms'),
        ];
    }
}