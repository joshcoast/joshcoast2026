<?php

namespace IvyForms\Common\Helpers;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Entity\FieldOptions\FieldOptions;
use IvyForms\Factory\FieldOptions\FieldOptionsFactory;
use IvyForms\Repository\FieldOptions\FieldOptionsRepositoryInterface;

/**
 * General helper utilities related to Field processing.
 */
class FieldHelper
{
    /**
     * Resolve parentId using fieldIndex -> parentId map if needed.
     *
     * @param array<string,mixed> $data Passed by reference; parentId modified in place.
     * @param array<int,int> $parentMap Map of fieldIndex => newly created parent field id.
     */
    public static function resolveParentId(array &$data, array $parentMap): void
    {
        if (!array_key_exists('parentId', $data)) {
            return;
        }

        $parentId = $data['parentId'];
        $needsResolve = ($parentId === 0 || $parentId === '0' || $parentId === null);
        if ($needsResolve && isset($data['fieldIndex']) && isset($parentMap[$data['fieldIndex']])) {
            $data['parentId'] = $parentMap[$data['fieldIndex']];
        }
    }

    /**
     * Remap parentId for duplicated field using old-to-new field ID map.
     *
     * @param array<string,mixed> $fieldData Passed by reference; parentId modified in place.
     * @param array<int,int> $fieldIdMap Map of old field ID => new field ID.
     */
    public static function remapParentIdForDuplication(array &$fieldData, array $fieldIdMap): void
    {
        if ($fieldData['parentId'] !== null && isset($fieldIdMap[$fieldData['parentId']])) {
            $fieldData['parentId'] = $fieldIdMap[$fieldData['parentId']];
        }
    }

    /**
     * Partition field options into new, update, and submitted IDs.
     *
     * @param array<string, mixed> $fieldData
     * @param array<int> $existingOptionIds
     * @return array{
     *   newOptions: array<int, FieldOptions>,
     *   updateOptions: array<int, FieldOptions>,
     *   submittedOptionIds: array<int, int>
     * }
     * @throws ValidationException
     */
    public static function partitionFieldOptions(array $fieldData, array $existingOptionIds): array
    {
        $newOptions = [];
        $updateOptions = [];
        $submittedOptionIds = [];
        foreach ($fieldData['fieldOptions'] as $optionData) {
            $option = FieldOptionsFactory::create($optionData);
            if (
                isset($optionData['id']) && $optionData['id'] > 0
                && in_array($optionData['id'], $existingOptionIds, true)
            ) {
                $updateOptions[] = $option;
                $submittedOptionIds[] = $optionData['id'];
                continue;
            }
            $newOptions[] = $option;
        }
        return [
            'newOptions' => $newOptions,
            'updateOptions' => $updateOptions,
            'submittedOptionIds' => $submittedOptionIds,
        ];
    }

    /**
     * Check if a field type has options (radio, checkbox, select, multi-select).
     *
     * @param string $fieldType
     * @return bool
     */
    public static function fieldTypeHasOptions(string $fieldType): bool
    {
        return in_array($fieldType, ['radio', 'checkbox', 'select', 'multi-select'], true);
    }

    /**
     * Duplicate field options from one field to another.
     *
     * @param int $oldFieldId
     * @param int $newFieldId
     * @param FieldOptionsRepositoryInterface $repository
     * @return void
     * @throws ValidationException
     */
    public static function duplicateFieldOptions(
        int $oldFieldId,
        int $newFieldId,
        FieldOptionsRepositoryInterface $repository
    ): void {
        $options = $repository->getByFieldId($oldFieldId);
        foreach ($options as $option) {
            $optionData = $option->toArray();
            unset($optionData['id']);
            $optionData['fieldId'] = $newFieldId;
            $repository->add(FieldOptionsFactory::create($optionData));
        }
    }
}
