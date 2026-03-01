<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Sanitizer;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\Field\FieldType;
use IvyForms\Services\Translations\BackendStrings;

class Sanitizer
{
    /**
     * Sanitize HTML editor content with support for inline styles (colors, formatting)
     *
     * @param string $content
     *
     * @return string
     */
    /**
     * Sanitize CSS value to prevent XSS attacks while allowing colors
     *
     * @param string $css
     *
     * @return string
     */
    private static function sanitizeCssValue(string $css): string
    {
        // Remove any potential XSS vectors
        $dangerous_patterns = [
            '/expression\s*\(/i',           // IE CSS expressions
            '/javascript\s*:/i',             // javascript: protocol
            '/vbscript\s*:/i',              // vbscript: protocol
            '/<\s*script/i',                // <script> tags
            '/on\w+\s*=/i',                 // event handlers (onclick, etc)
            '/import\s+/i',                 // @import
            '/<!--/i',                      // HTML comments
            '/-->/i',                       // HTML comments
            '/behavior\s*:/i',              // IE behaviors
            '/-moz-binding\s*:/i',          // Firefox bindings
            '/\\\\/i',                      // Backslash escaping
        ];

        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $css)) {
                error_log('🚨 BLOCKED dangerous CSS pattern: ' . $pattern . ' in: ' . $css);
                return ''; // Block completely if dangerous pattern found
            }
        }

        // Validate url() values - only allow data: for images and https:/http: for safe URLs
        $css = preg_replace_callback(
            '/url\s*\(\s*["\']?([^"\')]+)["\']?\s*\)/i',
            function ($matches) {
                $url = trim($matches[1]);
                // Allow only safe protocols
                if (preg_match('/^(https?:|data:image\/)/i', $url)) {
                    return 'url("' . esc_url($url) . '")';
                }
                error_log('🚨 BLOCKED unsafe URL in CSS: ' . $url);
                return ''; // Block unsafe URLs
            },
            $css
        );

        // Escape remaining content but preserve CSS functions like rgb(), rgba(), etc.
        return esc_attr($css);
    }

    /**
     * Sanitize HTML editor content with support for inline styles (colors, formatting)
     *
     * @param string $content
     *
     * @return string
     */
    public static function sanitizeEditorContent(string $content): string
    {
        // Extract all style attributes before wp_kses processes them
        $style_placeholders = [];
        $placeholder_index = 0;

        $content = preg_replace_callback(
            '/(<[^>]+)\s+style="([^"]*)"/i',
            function ($matches) use (&$style_placeholders, &$placeholder_index) {
                $placeholder = '___STYLE_PLACEHOLDER_' . $placeholder_index . '___';
                // Sanitize the CSS value for security
                $style_placeholders[$placeholder] = self::sanitizeCssValue(
                    $matches[2]
                );
                $placeholder_index++;
                $html = ' data-style-placeholder="' . $placeholder . '"';
                return $matches[1] . $html;
            },
            $content
        );

        // Define allowed HTML tags and attributes for the editor
        $allowed_tags = wp_kses_allowed_html('post');

        // Add necessary attributes to tags
        $tags_with_style = [
            'span',
            'p',
            'div',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'li',
            'ul',
            'ol',
            'blockquote',
            'strong',
            'em',
            'u',
            'a'
        ];

        foreach ($tags_with_style as $tag) {
            if (!isset($allowed_tags[$tag])) {
                $allowed_tags[$tag] = [];
            }
            if (!is_array($allowed_tags[$tag])) {
                $allowed_tags[$tag] = [];
            }
            $allowed_tags[$tag]['data-style-placeholder'] = true;
            $allowed_tags[$tag]['class'] = true;
        }

        // Ensure 'a' tag has all necessary attributes
        if (!isset($allowed_tags['a'])) {
            $allowed_tags['a'] = [];
        }
        $allowed_tags['a']['href'] = true;
        $allowed_tags['a']['target'] = true;
        $allowed_tags['a']['rel'] = true;
        $allowed_tags['a']['class'] = true;
        $allowed_tags['a']['data-style-placeholder'] = true;

        // Sanitize HTML structure (but style content is preserved in placeholders)
        $result = wp_kses($content, $allowed_tags);

        // Restore style attributes from placeholders
        $result = preg_replace_callback(
            '/data-style-placeholder="(___STYLE_PLACEHOLDER_\d+___)"/i',
            function ($matches) use ($style_placeholders) {
                $placeholder = $matches[1];
                if (isset($style_placeholders[$placeholder])) {
                    $sanitized_style = $style_placeholders[$placeholder];
                    // Only add style attribute if there's actual content
                    if (!empty($sanitized_style)) {
                        return 'style="' . $sanitized_style . '"';
                    }
                }
                return '';
            },
            $result
        );


        return $result;
    }

    /**
     * Sanitize form action buttons settings
     *
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    public static function sanitizeFormActionButtons(array $settings): array
    {
        $submitButtonSettings = $settings['submitButtonSettings'] ?? [];
        $validPositions = ['default', 'left', 'center', 'right'];
        $position = $submitButtonSettings['position'] ?? 'default';

        // Validate position
        if (!in_array($position, $validPositions, true)) {
            $position = 'default';
        }

        return [
            'submitButtonSettings' => [
                'label' => sanitize_text_field(
                    $submitButtonSettings['label'] ?? BackendStrings::getCommonStrings()['submit']
                ),
                'position' => $position
            ]
        ];
    }

    /**
     * Sanitize all form data
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     * @throws InvalidArgumentException
     */
    public static function sanitizeFormData(array $data): array
    {
        $fields = $data['fields'] ?? [];
        return [
            'id'                    => (int)($data['id'] ?? 0),
            'name'                  => sanitize_text_field($data['name'] ?? ''),
            'description'           => sanitize_text_field($data['description'] ?? ''),
            'showTitle'             => (bool)($data['showTitle'] ?? false),
            'published'             => (bool)($data['published'] ?? false),
            'showDescription'       => (bool)($data['showDescription'] ?? false),
            'storeEntries'          => (bool)($data['storeEntries'] ?? false),
            'fields'                => self::sanitizeFields($fields),
            'integrationSettings'   => self::sanitizeIntegrationSettings(
                $data['integrationSettings'] ?? []
            ),
            'formActionButtons'     => self::sanitizeFormActionButtons(
                $data['formActionButtons'] ?? []
            )
        ];
    }
    /**
     * Sanitize all notification data
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public static function sanitizeNotificationData(array $data): array
    {
        return [
            'id'          => (int)($data['id'] ?? 0),
            'name'        => sanitize_text_field($data['name'] ?? ''),
            'sender'      => self::sanitizeEmailOrPlaceholder($data['sender'] ?? ''),
            'replyTo'     => self::sanitizeEmailOrPlaceholder($data['replyTo'] ?? ''),
            'receiver'    => self::sanitizeEmailOrPlaceholder($data['receiver'] ?? ''),
            'enabled'     => (bool)($data['enabled'] ?? true),
            'subject'     => sanitize_text_field($data['subject'] ?? ''),
            'message'     => self::sanitizeEditorContent($data['message'] ?? ''),
            'smartLogic'  => (bool)($data['smartLogic'] ?? false),
            'formId'      => (int)($data['formId'] ?? 0),
        ];
    }
    /**
     * Sanitize all confirmation data
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public static function sanitizeConfirmationData(array $data): array
    {
        return [
            'id'        => (int)($data['id'] ?? 0),
            'formId'    => (int)($data['formId'] ?? 0),
            'type'      => sanitize_text_field($data['type'] ?? ''),
            'enabled'   => (bool)($data['enabled'] ?? true),
            'showForm'  => (bool)($data['showForm'] ?? false),
            'message'   => self::sanitizeEditorContent($data['message'] ?? ''),
            'url'       => sanitize_url(
                isset($data['url']) ? urldecode($data['url']) : ''
            ),
            'page'      => sanitize_text_field($data['page'] ?? ''),
        ];
    }

    /**
     * Sanitize form fields
     *
     * @param mixed[] $fields
     *
     * @return mixed[]
     * @throws InvalidArgumentException
     */
    private static function sanitizeFields(array $fields): array
    {
        return array_map(function ($field) {
            // Validate field type
            $fieldType = sanitize_text_field($field['type'] ?? '');
            if (!FieldType::isValid($field['type'])) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_field_type']
                );
            }

            $sanitizedField = [
                'id'                        => (int)($field['id'] ?? 0),
                'fieldIndex'                => (int)($field['fieldIndex'] ?? 0),
                'type'                      => $fieldType,
                'label'                     => sanitize_text_field($field['label'] ?? ''),
                'required'                  => (bool)($field['required'] ?? false),
                'defaultValue'              => sanitize_text_field($field['defaultValue'] ?? ''),
                'placeholder'               => sanitize_text_field($field['placeholder'] ?? ''),
                'position'                  => (int)($field['position'] ?? 0),
                'rowIndex'                  => max(0, (int)($field['rowIndex'] ?? 0)),
                'columnIndex'               => max(0, min(4, (int)($field['columnIndex'] ?? 0))),
                'width'                     => max(20, min(100, (int)($field['width'] ?? 100))),
                'parentId'                  => isset($field['parentId']) ? max(0, (int)$field['parentId']) : null,
                'hideLabel'                 => (bool)($field['hideLabel'] ?? false),
                'readOnly'                  => (bool)($field['readOnly'] ?? false),
                'description'               => sanitize_text_field($field['description'] ?? ''),
                'requiredMessage'           => sanitize_text_field($field['requiredMessage'] ?? ''),
                'cssClasses'                => sanitize_text_field($field['cssClasses'] ?? ''),
                'fieldOptions'              => self::sanitizeFieldOptions($field['fieldOptions'] ?? []),
                'shuffleOptions'            => (bool)($field['shuffleOptions'] ?? false),
                'showValues'                => (bool)($field['showValues'] ?? false),
                'enableSearch'              => (bool)($field['enableSearch'] ?? false),
                'rows'                      => (int)($field['rows'] ?? 0),
                'confirmFieldEnabled'       => (bool)($field['confirmFieldEnabled'] ?? false),
                'confirmFieldLabel'         => sanitize_text_field($field['confirmFieldLabel'] ?? ''),
                'confirmFieldPlaceholder'   => sanitize_text_field($field['confirmFieldPlaceholder'] ?? ''),
                'confirmFieldHideLabel'     => (bool)($field['confirmFieldHideLabel'] ?? false),
                'phoneFormat'               => self::sanitizePhoneFormat($field['phoneFormat'] ?? ''),
                'phoneAutoDetect'           => (bool)($field['phoneAutoDetect'] ?? false),
                'minValue'                  => self::sanitizeNumericValue($field['minValue'] ?? null),
                'maxValue'                  => self::sanitizeNumericValue($field['maxValue'] ?? null),
                'step'                      => (float)($field['step'] ?? 1.0),
                'numberFormat'              => sanitize_text_field($field['numberFormat'] ?? ''),
                'visible'                   => (bool)($field['visible'] ?? true),
                'limitMaxLength'            => (bool)($field['limitMaxLength'] ?? false),
                'maxLength'                 => (int)($field['maxLength'] ?? 255),
                'customValidationMessage'   => sanitize_text_field($field['customValidationMessage'] ?? ''),
                'labelPosition'             => sanitize_text_field($field['labelPosition'] ?? 'default'),
                'noDuplicates'              => (bool)($field['noDuplicates'] ?? false),
                'inputPrefix'               => sanitize_text_field($field['inputPrefix'] ?? ''),
                'inputSuffix'               => sanitize_text_field($field['inputSuffix'] ?? ''),
                'timeFieldType'             => sanitize_text_field($field['timeFieldType'] ?? ''),
                'timeFormat'                => sanitize_text_field($field['timeFormat'] ?? ''),
                'dateFieldType'             => sanitize_text_field($field['dateFieldType'] ?? ''),
                'dateFormat'                => sanitize_text_field($field['dateFormat'] ?? ''),
                'minDateValue'              => sanitize_text_field($field['minDateValue'] ?? ''),
                'maxDateValue'              => sanitize_text_field($field['maxDateValue'] ?? ''),
                'showRatingText'            => (bool)($field['showRatingText'] ?? false),
                'ratingIcon'                => sanitize_text_field($field['ratingIcon'] ?? 'star'),
            ];

            /**
             * This filter allows to sanitize form fields properties
             * for fields that are not part of the core plugin.
             *
             * @since 0.1.0
             *
             * @param mixed[] $sanitizedField The sanitized field data so far.
             * @param mixed[] $field The original field data before sanitization.
             * @return mixed[] The modified sanitized field data.
             */
            return apply_filters('ivyforms/sanitizer/field_properties', $sanitizedField, $field);
        }, $fields);
    }

    /**
     * Sanitize field options
     *
     * @param mixed[] $options
     *
     * @return mixed[]
     */
    private static function sanitizeFieldOptions(array $options): array
    {
        return array_map(function ($option) {
            return [
                'id'        => (int)($option['id'] ?? 0),
                'fieldId'   => (int)($option['fieldId'] ?? 0),
                'label'     => sanitize_text_field($option['label'] ?? ''),
                'value'     => sanitize_text_field($option['value'] ?? ''),
                'isDefault' => (bool)($option['isDefault'] ?? false),
                'position'  => (int)($option['position'] ?? 1),
            ];
        }, $options);
    }

    private static function sanitizePhoneFormat(string $format): string
    {
        $allowed = ['international','national','e164'];
        $formatLower = strtolower($format);
        return in_array($formatLower, $allowed, true) ? $formatLower : '';
    }

    /**
     * Sanitize entry data
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public static function sanitizeEntryData(array $data): array
    {
        return [
            'id'            => (int)($data['id'] ?? 0),
            'formId'        => (int)($data['formId'] ?? 0),
            'userId'        => (int)($data['userId'] ?? 0),
            'status'        => sanitize_text_field($data['status'] ?? ''),
            'ipAddress'     => sanitize_text_field($data['ipAddress'] ?? ''),
            'userAgent'     => sanitize_text_field($data['userAgent'] ?? ''),
            'sourceURL'     => sanitize_text_field($data['sourceURL'] ?? ''),
            'dateCreated'   => sanitize_text_field($data['dateCreated'] ?? ''),
            'dateEdited'    => sanitize_text_field($data['dateEdited'] ?? ''),
            'starred'       => (bool)($data['starred'] ?? false),
        ];
    }

    /**
     * Sanitize ID
     *
     * @param int $id
     *
     * @return int
     *
     * @throws InvalidArgumentException
     */
    public static function sanitizeId(int $id): int
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_id']
            );
        }

        return $id;
    }

    /**
     * Sanitize multiple IDs
     *
     * @param int[] $ids
     *
     * @return int[]
     *
     */
    public static function sanitizeIds(array $ids): array
    {
        return array_map(function ($id) {
            return (int)$id;
        }, $ids);
    }

    /**
     * Sanitize search parameters
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public static function sanitizeSearchParams(array $params): array
    {
        $filters = [
            'starred'   => array_key_exists('starred', $params['filters'] ?? [])
                ? (int)filter_var($params['filters']['starred'], FILTER_VALIDATE_BOOLEAN)
                : null,
            'published' => array_key_exists('published', $params['filters'] ?? [])
                ? (int)filter_var($params['filters']['published'], FILTER_VALIDATE_BOOLEAN)
                : null,
            'status' => sanitize_text_field($params['filters']['read'] ?? ''),
        ];
        // Add formId to filters if present and valid
        if (
            isset($params['filters']['formId']) &&
            is_numeric($params['filters']['formId']) &&
            (int)$params['filters']['formId'] > 0
        ) {
            $filters['formId'] = self::sanitizeId((int)$params['filters']['formId']);
        }
        return [
            'page'      => max((int)($params['page'] ?? 1), 1),
            'perPage'   => max((int)($params['perPage'] ?? 10), 1),
            'search'    => sanitize_text_field($params['search'] ?? ''),
            'orderBy'   => sanitize_text_field($params['orderBy'] ?? 'id'),
            'order'     => strtolower(sanitize_text_field($params['order'] ?? 'asc')) === 'desc' ? 'desc' : 'asc',
            'filters'   => $filters,
            'dateRange' => [
                sanitize_text_field($params['dateRange'][0] ?? ''),
                sanitize_text_field($params['dateRange'][1] ?? ''),
            ],
            'shouldGetCount' => (bool)($params['shouldGetCount'] ?? false),
            'searchFieldValue' => filter_var($params['searchFieldValue'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Sanitize form submission data based on field types and IDs
     *
     * @param mixed[] $data Form submission data
     * @param mixed[] $formFields Form fields configuration
     *
     * @return mixed[] Sanitized submission data
     */
    public static function sanitizeFormSubmissionData(array $data, array $formFields): array
    {
        $sanitizedData = [];
        list($fieldIdMap, $typeIndexMap) = self::buildFieldMaps($formFields);

        foreach ($data['values'] as $key => $value) {
            if (self::isInternalField($key)) {
                $sanitizedData[$key] = $value;
                continue;
            }
            if (isset($fieldIdMap[$key])) {
                $sanitizedData[$key] = self::sanitizeByFieldType($fieldIdMap[$key], $value);
                continue;
            }
            if (isset($typeIndexMap[$key])) {
                $fieldId = $typeIndexMap[$key]['id'];
                $fieldType = $typeIndexMap[$key]['type'];
                $sanitizedData[$fieldId] = self::sanitizeByFieldType($fieldType, $value);
            }
        }

        // Add form ID to sanitized data if present
        if (isset($data['formId'])) {
            $sanitizedData['formId'] = (int)$data['formId'];
        }

        // Add post ID and referer if present (for placeholders)
        if (isset($data['postId'])) {
            $sanitizedData['postId'] = (int)($data['postId']);
        }
        if (isset($data['referer'])) {
            $sanitizedData['referer'] = sanitize_text_field($data['referer']);
        }

        return $sanitizedData;
    }

    /**
     * Build field ID and type-index maps from form fields
     *
     * @param mixed[] $formFields Form fields configuration
     * @return mixed[]
     */
    private static function buildFieldMaps(array $formFields): array
    {
        $fieldIdMap = [];
        $typeIndexMap = [];
        foreach ($formFields as $field) {
            $fieldId = $field->getId() ?? null;
            $fieldType = $field->getType() ?? null;
            $fieldIndex = $field->getIndex() ?? null;
            if ($fieldId !== null) {
                $fieldIdMap[$fieldId] = $fieldType;
                if ($fieldType !== null && $fieldIndex !== null) {
                    $typeIndexMap[$fieldType . '_' . $fieldIndex] = [
                        'id' => $fieldId,
                        'type' => $fieldType
                    ];
                }
            }
        }
        return [$fieldIdMap, $typeIndexMap];
    }

    /**
     * Check if a key is an internal field
     *
     * @param string $key
     * @return bool
     */
    private static function isInternalField(string $key): bool
    {
        return in_array($key, ['nonce', 'formId']);
    }

    /**
     * Sanitize a value based on its field type
     *
     * @param string $fieldType The type of field
     * @param mixed $value The value to sanitize
     *
     * @return mixed Sanitized value
     */
    private static function sanitizeByFieldType(string $fieldType, $value)
    {
        // Recursively sanitize arrays/objects (parent-child fields)
        if (is_array($value)) {
            return array_map(function ($v) use ($fieldType) {
                return self::sanitizeByFieldType($fieldType, $v);
            }, $value);
        }
        if (is_object($value)) {
            $sanitized = array_map(function ($v) use ($fieldType) {
                return self::sanitizeByFieldType($fieldType, $v);
            }, get_object_vars($value));
            return (object)$sanitized;
        }
        switch ($fieldType) {
            case 'email':
                return sanitize_email($value);

            case 'number':
                // Preserve decimals
                return is_numeric($value) ? (float)$value : 0.0;

            case 'phone':
                // Remove everything except digits, +, and spaces
                return preg_replace('/[^\d\s+]/', '', $value);

            case 'paragraph':
                return sanitize_textarea_field($value);

            case 'website':
                return sanitize_url($value);

            case 'time':
                $sanitizedTime = preg_replace('/[^0-9: ]/i', '', (string)$value);
                return sanitize_text_field($sanitizedTime);

            case 'date':
                return sanitize_text_field($value);

            case 'text':
            default:
                // Default to text field sanitization
                return self::sanitizeText($value);
        }
    }

    /**
     * Verify nonce with custom error handling
     *
     * @param string|null $nonce
     *
     * @throws ForbiddenException
     */
    public static function verifyNonce(?string $nonce): void
    {
        $nonce = sanitize_text_field($nonce);

        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            throw new ForbiddenException(
                BackendStrings::getExceptionStrings()['invalid_nonce']
            );
        }
    }
    /**
     * Verify submission nonce with custom error handling
     *
     * @param string|null $nonce
     * @param int $formId
     *
     * @throws ForbiddenException
     */
    public static function verifySubmissionNonce(?string $nonce, int $formId): void
    {
        $nonce = sanitize_text_field($nonce);

        // Verify the form-specific nonce
        if (!wp_verify_nonce($nonce, 'ivyformsFrontSubmissionNonce_' . $formId)) {
            throw new ForbiddenException(
                BackendStrings::getExceptionStrings()['invalid_nonce']
            );
        }
    }

    /**
     * Sanitize text
     *
     * @param string|null $text
     *
     * @return string
     */
    public static function sanitizeText(?string $text): ?string
    {
        if (is_null($text)) {
            return '';
        }

        return wp_kses_post(sanitize_text_field($text));
    }

    /**
     * Sanitize settings fields
     *
     * @param mixed $value
     *
     * @return mixed Sanitized value
     */
    public static function sanitizeSettingsFields($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'sanitizeSettingsFields'], $value);
        }

        if (is_object($value)) {
            return (object)array_map([self::class, 'sanitizeSettingsFields'], (array)$value);
        }

        if (is_string($value)) {
            return self::sanitizeText($value);
        }

        if (is_int($value)) {
            return intval($value);
        }

        if (is_bool($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return '';
    }

    /**
     * Sanitize email or placeholder
     *
     * @param string $value
     *
     * @return string
     */
    public static function sanitizeEmailOrPlaceholder($value): string
    {
        $value = trim($value);
        // Allow placeholders
        if (preg_match('/^{{[^}]+}}$/', $value)) {
            return $value;
        }
        // Otherwise, sanitize as email
        return sanitize_email($value);
    }

    /**
     * Sanitize numeric values for min/max fields
     * Convert empty/unselected to null, but allow 0 as a valid value
     *
     * @param mixed $value
     * @return float|null
     */
    private static function sanitizeNumericValue($value): ?float
    {
        // If value is null or empty string, return null (not selected)
        if ($value === null || $value === '') {
            return null;
        }

        // If value is numeric (including 0), convert to float
        if (is_numeric($value)) {
            return (float)$value;
        }

        // If not numeric, return null
        return null;
    }

    /**
     * Validate email address.
     *
     * @param string $email
     * @return bool
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Normalize field value to string (comma-separated if array)
     * @param mixed $value
     * @return string
     */
    public static function normalizeFieldValue($value): string
    {
        if (is_array($value)) {
            return implode(', ', $value);
        }
        return (string)$value;
    }

    /**
     * Sanitize integration settings
     * Recursively handles nested structures and various data types.
     *
     * @param mixed $integrationSettings
     *
     * @return array<string, array<string, mixed>>
     */
    public static function sanitizeIntegrationSettings($integrationSettings): array
    {
        // Handle null or non-array inputs
        if (!is_array($integrationSettings)) {
            return [];
        }

        $cleanedSettings = [];

        foreach ($integrationSettings as $integration => $settings) {
            // Skip if not a valid integration name or settings structure
            if (!is_string($integration) || !is_array($settings)) {
                continue;
            }

            $cleanedSettings[sanitize_text_field($integration)] = self::sanitizeIntegrationSettingsRecursive($settings);
        }

        return $cleanedSettings;
    }

    /**
     * Recursively sanitize integration settings values
     *
     * @param mixed $value
     * @return mixed
     */
    public static function sanitizeIntegrationSettingsRecursive($value)
    {
        // Handle arrays recursively
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $val) {
                $sanitizedKey = is_string($key) ? sanitize_text_field($key) : $key;
                $sanitized[$sanitizedKey] = self::sanitizeIntegrationSettingsRecursive($val);
            }
            return $sanitized;
        }

        // Handle different scalar types
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return $value;
        }
        if (is_string($value)) {
            return sanitize_text_field($value);
        }

        return '';
    }

    /**
     * Get allowed HTML tags and attributes for wp_kses sanitization.
     * Includes support for signature images with data: URIs.
     *
     * @return array<string, array<string, bool>>
     */
    public static function getAllowedHtmlTags(): array
    {
        return [
            'img' => [
                'src' => true,
                'alt' => true,
                'style' => true,
                'class' => true,
                'width' => true,
                'height' => true,
            ],
            'a' => [
                'href' => true,
                'title' => true,
                'target' => true,
                'rel' => true,
            ],
            'p' => ['class' => true, 'style' => true],
            'br' => [],
            'strong' => [],
            'b' => [],
            'em' => [],
            'i' => [],
            'u' => [],
            'span' => ['class' => true, 'style' => true],
            'div' => ['class' => true, 'style' => true],
            'ul' => ['class' => true],
            'ol' => ['class' => true],
            'li' => ['class' => true],
            'h1' => ['class' => true, 'style' => true],
            'h2' => ['class' => true, 'style' => true],
            'h3' => ['class' => true, 'style' => true],
            'h4' => ['class' => true, 'style' => true],
            'h5' => ['class' => true, 'style' => true],
            'h6' => ['class' => true, 'style' => true],
        ];
    }

    /**
     * Get allowed protocols for wp_kses sanitization.
     * Includes 'data' protocol for signature images.
     *
     * @return array<int, string>
     */
    public static function getAllowedProtocols(): array
    {
        return ['http', 'https', 'mailto', 'data'];
    }

    /**
     * Sanitize HTML content using wp_kses with support for signature images.
     *
     * @param string $content
     * @return string
     */
    public static function sanitizeHtmlContent(string $content): string
    {
        return wp_kses($content, self::getAllowedHtmlTags(), self::getAllowedProtocols());
    }
}
