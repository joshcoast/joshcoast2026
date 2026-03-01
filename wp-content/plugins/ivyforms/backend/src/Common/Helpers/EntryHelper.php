<?php

namespace IvyForms\Common\Helpers;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Entity\Entry\Entry;

class EntryHelper
{
    /**
     * Get the client IP address.
     *
     * @return string|null
     */
    public static function getClientIpAddress(): ?string
    {
        $ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
        return $ip ?: null;
    }

    /**
     * Get the user agent string.
     *
     * @return string
     */
    public static function getUserAgent(): string
    {
        $userAgent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
        return $userAgent ?: '';
    }

    /**
     * Get the source URL.
     *
     * @return string|null
     */
    public static function getSourceURL(): ?string
    {
        $sourceURL = filter_input(INPUT_SERVER, 'HTTP_REFERER');
        return $sourceURL ?: '';
    }

    /**
     * Get the browser name from the user agent string.
     *
     * @param string $userAgent
     * @return string
     */
    public static function getBrowserName(string $userAgent): string
    {
        if (str_contains($userAgent, 'Firefox')) {
            return 'Mozilla Firefox';
        } elseif (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Edg')) {
            return 'Google Chrome';
        } elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) {
            return 'Apple Safari';
        } elseif (str_contains($userAgent, 'Edg')) {
            return 'Microsoft Edge';
        } elseif (str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident')) {
            return 'Internet Explorer';
        }
        return 'Unknown';
    }

    /**
     * Build entry data array for form submissions.
     *
     * @param int $formId
     * @return array<string, mixed>
     */
    public static function buildEntryData(int $formId): array
    {
        return [
            'formId'        => $formId,
            'userId'        => get_current_user_id() ?: null,
            'status'        => Entry::DEFAULT_STATUS,
            'ipAddress'     => self::getClientIpAddress(),
            'userAgent'     => self::getUserAgent(),
            'sourceURL'     => self::getSourceURL(),
        ];
    }

    /**
     * Get the value of a parent subfield.
     *
     * @param int $fieldId
     * @param int|null $parentId
     * @param array<int|string, mixed> $submissionData
     * @param array<int, string|null> $parentSubfieldKeys
     * @return mixed|null
     */
    public static function getParentSubfieldValue(
        int $fieldId,
        ?int $parentId,
        array $submissionData,
        array $parentSubfieldKeys
    ) {
        if ($parentId && isset($parentSubfieldKeys[$fieldId])) {
            $subKey = $parentSubfieldKeys[$fieldId];
            if (isset($submissionData[$parentId][$subKey])) {
                return $submissionData[$parentId][$subKey];
            }
        }
        return null;
    }

    /**
     * Get the compound field value by combining child field values.
     *
     * @param int $fieldId
     * @param array<int, array<int>> $parentChildrenMap
     * @param array<int, string|null> $parentSubfieldKeys
     * @param array<int|string, mixed> $submissionData
     * @return string
     */
    public static function getCompoundFieldValue(
        int $fieldId,
        array $parentChildrenMap,
        array $parentSubfieldKeys,
        array $submissionData
    ): string {
        $childValues = [];
        if (!empty($parentChildrenMap[$fieldId])) {
            foreach ($parentChildrenMap[$fieldId] as $childId) {
                $subKey = $parentSubfieldKeys[$childId] ?? null;
                if ($subKey && isset($submissionData[$fieldId][$subKey])) {
                    $val = $submissionData[$fieldId][$subKey];
                    if ($val !== '') {
                        $childValues[] = $val;
                    }
                }
            }
        }
        return implode(' ', $childValues);
    }

    /**
     * Get the default field value from submission data.
     *
     * @param int $fieldId
     * @param array<int|string, mixed> $submissionData
     * @return mixed|string
     */
    public static function getDefaultFieldValue(int $fieldId, array $submissionData)
    {
        return $submissionData[$fieldId] ?? '';
    }

    /**
     * Build parent-children map from form fields.
     *
     * @param array<int, object> $formFields
     * @return array<int, array<int>>
     */
    public static function buildParentChildrenMap(array $formFields): array
    {
        $parentChildrenMap = [];
        foreach ($formFields as $field) {
            $parentId = method_exists($field, 'getParentId') ? $field->getParentId() : null;
            if ($parentId && $parentId > 0) {
                $parentChildrenMap[$parentId][] = $field->getId();
            }
        }
        return $parentChildrenMap;
    }

    /**
     * Build subfield key map for each parent.
     *
     * @param array<int, array<int>> $parentChildrenMap
     * @param array<mixed> $submissionData
     * @return array<int, string|null>
     */
    public static function buildParentSubfieldKeys(array $parentChildrenMap, array $submissionData): array
    {
        $parentSubfieldKeys = [];
        foreach ($parentChildrenMap as $parentId => $childIds) {
            $parentValue = $submissionData[$parentId] ?? [];
            $keys = array_keys($parentValue);
            foreach ($childIds as $i => $childId) {
                $parentSubfieldKeys[$childId] = $keys[$i] ?? null;
            }
        }
        return $parentSubfieldKeys;
    }
}
