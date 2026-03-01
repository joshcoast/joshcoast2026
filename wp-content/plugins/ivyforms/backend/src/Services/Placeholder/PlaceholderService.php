<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Placeholder;

// phpcs:disable PSR1.Files.SideEffects
use IvyForms\Common\Helpers\EntryHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PlaceholderService
{
    /**
     * Replace placeholders in a template string with actual values.
     *
     * @param string $template
     * @param array<string, string|array<string, string>> $fieldData
     * @param array<string, string|int> $generalData
     * @param array<string, string> $fieldLabels
     * @return string
     */
    public static function replacePlaceholders(
        string $template,
        array $fieldData = [],
        array $generalData = [],
        array $fieldLabels = []
    ): string {
        $placeholders = [];

        // Field placeholders and all_data
        $allData = [];
        $addedLabels = [];
        foreach ($fieldData as $key => $value) {
            // If value is an array (parent-child fields), convert to string
            $valueString = is_array($value)
                ? implode(' ', array_filter(array_map(function ($val) {
                    return $val;
                }, $value)))
                : $value;
            $placeholders['{{' . $key . '}}'] = $valueString;
            if (isset($fieldLabels[$key]) && !in_array($fieldLabels[$key], $addedLabels, true)) {
                $label = $fieldLabels[$key];
                $allData[] = $label . ': ' . $valueString;
                $addedLabels[] = $label;
            }
        }
        if (!empty($fieldData)) {
            $placeholders['{{all_data}}'] = implode("\n", $allData);
        }

        // General placeholders
        foreach ($generalData as $key => $value) {
            $placeholders['{{wp.' . $key . '}}'] = $value;
        }

        // Replace all placeholders
        $result = strtr($template, $placeholders);

        // Remove any unreplaced placeholders
        $result = preg_replace('/{{[^}]+}}/', '', $result);

        return $result;
    }

    /**
     * Build field data for placeholders from form fields and submission data.
     *
     * @param array<int, object> $formFields
     * @param array<string, string> $submissionData
     * @return array<string, string>
     */
    public static function buildFieldData(
        array $formFields,
        array $submissionData
    ): array {
        $fieldData = [];
        foreach ($formFields as $field) {
            $typeIndex = $field->getType() . '_' . $field->getIndex();
            $id = $field->getId();
            if (isset($submissionData[$id])) {
                $value = $submissionData[$id];

                /**
                 * Filter to convert fields values to HTML representation
                 *
                 * @since 1.0.0
                 *
                 * @param string $value The field value
                 * @param object $field The field object
                 * @return string The converted value
                 */
                $value = apply_filters('ivyforms/placeholder/filter_field_value', $value, $field);

                $fieldData[$typeIndex] = $value;
                $fieldData[$id] = $value;
            }
        }
        return $fieldData;
    }

    /**
     * Build general data for placeholders from environment and submission data.
     *
     * @param int $entryId
     * @param array<string, mixed> $submissionData
     * @return array<string, string|int>
     */
    public static function buildGeneralData(int $entryId, array $submissionData = []): array
    {
        $generalData = [
            'site_url' => get_bloginfo('url'),
            'site_title' => get_bloginfo('name'),
            'admin_email' => get_bloginfo('admin_email'),
            'date' => date(get_option('date_format')),
            'user_ip' => EntryHelper::getClientIpAddress() ?? '',
            'user_agent' => EntryHelper::getUserAgent(),
            'referer_url' => $submissionData['referer'] ?? '',
        ];

        if (!empty($submissionData['postId'])) {
            $post = get_post($submissionData['postId']);
            if ($post) {
                $generalData['post_id'] = $post->ID;
                $generalData['post_title'] = $post->post_title;
                $generalData['post_permalink'] = get_permalink($post->ID);
            }
        }

        if (is_user_logged_in()) {
            $currentUser = wp_get_current_user();
            $generalData['user_id'] = $currentUser->ID;
            $generalData['user_name'] = $currentUser->user_login;
            $generalData['user_email'] = $currentUser->user_email;
            $generalData['user_first_name'] = $currentUser->user_firstname;
            $generalData['user_last_name'] = $currentUser->user_lastname;
        }
        if ($entryId) {
            $generalData['entry_id'] = $entryId;
        }
        return $generalData;
    }

    /**
     * Build field labels mapping for placeholders from form fields.
     *
     * @param array<int, object> $formFields
     * @return array<string, string>
     */
    public static function buildFieldLabels(array $formFields): array
    {
        $fieldLabels = [];
        foreach ($formFields as $field) {
            $typeIndex = $field->getType() . '_' . $field->getIndex();
            $fieldLabels[$typeIndex] = $field->getFieldGeneralSettings()->getLabel();
            $fieldLabels[$field->getId()] = $field->getFieldGeneralSettings()->getLabel();
        }
        return $fieldLabels;
    }
}
