<?php

namespace IvyForms\Repository\EntryField;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Repository\BaseRepositoryInterface;

interface EntryFieldRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Check if an entry field exists in the database.
     *
     * @param int $entryFieldId
     * @return bool
     */
    public function exists(int $entryFieldId): bool;

    /**
     * @param array<mixed> $array
     * @return array<int, array<string, mixed>>
     */
    public function findBy(array $array): array;

    /**
     * Get all entry field IDs for the given entry IDs.
     *
     * @param int[] $entryIds
     * @return int[]
     */
    public function findIdsByEntryIds(array $entryIds): array;

    /**
     * Delete multiple entry fields by their IDs in a single query.
     *
     * @param int[] $formIds
     * @return int Number of deleted rows
     */
    public function deleteByFormIds(array $formIds): int;

    /**
     * Check if a value already exists in entry fields for a specific field.
     *
     * @param int $formId
     * @param int $fieldId
     * @param string $value
     * @return bool
     */
    public function checkDuplicateValue(int $formId, int $fieldId, string $value): bool;
}
