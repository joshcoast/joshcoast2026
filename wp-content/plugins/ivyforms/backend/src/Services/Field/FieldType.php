<?php

namespace IvyForms\Services\Field;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class FieldType
{
    /**
     * Base allow-list of field types.
     */
    private const BASE_ALLOWED_TYPES = [
        'email',
        'text',
        'textarea',
        'number',
        'phone',
        'website',
        'recaptcha',
        'turnstile',
        'hcaptcha',
        'name',
        'radio',
        'checkbox',
        'address',
        'select',
        'multi-select',
        'time',
        'date',
        'rating',
    ];

    /**
     * Get the list of allowed field types
     *
     * @return array<string>
     */
    public static function getAllowedTypes(): array
    {
        $baseTypes = self::BASE_ALLOWED_TYPES;
        /**
         * Allows modification of the allowed field types.
         * @since 0.1.0
         *
         * @param mixed[] $baseTypes The current list of allowed field types.
         * @return mixed[] The modified list of allowed field types.
         */
        $types = apply_filters('ivyforms/field/filter_allowed_types', $baseTypes);

        // Normalize: ensure array of unique strings
        if (!($types)) {
            return self::BASE_ALLOWED_TYPES;
        }
        $filtered = [];
        foreach ($types as $t) {
            if ($t !== '') {
                $filtered[$t] = true;
            }
        }

        return array_keys($filtered);
    }

    /**
     * Check if a given type is valid
     *
     * @param string $type
     * @return bool
     */
    public static function isValid(string $type): bool
    {
        return in_array($type, self::getAllowedTypes(), true);
    }
}
