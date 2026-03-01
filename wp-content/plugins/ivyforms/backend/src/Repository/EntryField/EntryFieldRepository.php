<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository\EntryField;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Exception;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Factory\EntryField\EntryFieldFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Services\InstallActions\DB\Entry\EntriesTable;
use IvyForms\Services\InstallActions\DB\EntryField\EntryFieldsTable as EntryFieldsTable;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;
use IvyForms\Services\Translations\BackendStrings;

class EntryFieldRepository extends AbstractRepository implements EntryFieldRepositoryInterface
{
    public const FACTORY = EntryFieldFactory::class;

    /**
     * Check if an entry field exists in the database.
     *
     * @param int $entryFieldId
     * @return bool
     * @throws QueryExecutionException
     */
    public function exists(int $entryFieldId): bool
    {
        $query = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE id = %d",
            $entryFieldId
        );
        $count = $this->wpdb->get_var($query);
        if ($count === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }
        return (int)$count > 0;
    }

    /**
     * @throws Exception
     */
    public function add(object $entity): int
    {
        $data = $entity->toArray();

        $result = $this->wpdb->insert(
            $this->table,
            [
                'id'        => $data['id'] ?? null,
                'entryId'  => $data['entryId'],
                'fieldId'   => $data['fieldId'],
                'fieldValue' => $data['fieldValue'],
            ],
            [
                '%d',
                '%d',
                '%d',
                '%s',
            ],
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        return  $this->wpdb->insert_id;
    }

    /**
     * @throws Exception
     */
    public function update(int $id, object $entity): bool
    {
        $data = $entity->toArray();

        $result = $this->wpdb->update(
            $this->table,
            [
                'fieldId' => $data['fieldId'],
                'fieldValue' => $data['fieldValue'],
            ],
            ['id' => $id],
            [
                '%d',
                '%s',
            ],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_update_data'] . __CLASS__
            );
        }

        return (bool) $result;
    }

    /**
     * Find an entry field by its ID.
     *
     * @param array<mixed> $array
     * @return array<mixed>
     * @throws QueryExecutionException
     */
    public function findBy(array $array): array
    {
        $sql = $this->selectQuery() . " {$this->table}";
        $where = [];
        $values = [];

        foreach ($array as $field => $value) {
            $where[] = "{$field} = %s";
            $values[] = $value;
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $query = $this->wpdb->prepare($sql, ...$values);
        $rows = $this->wpdb->get_results($query, ARRAY_A);
        if ($rows === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }
        $results = [];
        foreach ($rows as $row) {
            $results[] = call_user_func([static::FACTORY, 'create'], $row);
        }

        return $results;
    }

    /**
     * Get all entry field IDs for the given entry IDs in a single query.
     *
     * @param int[] $entryIds
     * @return int[]
     * @throws QueryExecutionException
     */
    public function findIdsByEntryIds(array $entryIds): array
    {
        if (empty($entryIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($entryIds), '%d'));
        $query = "SELECT id FROM {$this->table} WHERE entryId IN ($placeholders)";
        $results = $this->wpdb->get_results($this->wpdb->prepare($query, ...$entryIds), ARRAY_A);
        if ($results === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }
        return array_map(fn($row) => (int)$row['id'], $results);
    }

    /**
     * Delete entry fields by form IDs using a subquery for fieldId.
     *
     * @param int[] $formIds
     * @return int Number of deleted entry_fields
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function deleteByFormIds(array $formIds): int
    {
        if (empty($formIds)) {
            return 0;
        }
        $formIds = array_map('intval', $formIds);
        $placeholders = implode(',', array_fill(0, count($formIds), '%d'));
        $fieldsTable = FieldsTable::getTableName();
        $query = "DELETE FROM {$this->table} WHERE fieldId IN 
                        (SELECT id FROM {$fieldsTable} WHERE formId IN ($placeholders))";
        $result = $this->wpdb->query($this->wpdb->prepare($query, ...$formIds));
        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['failed_delete_by_foreign_keys']
            );
        }
        return $result;
    }

    /**
     * Check if a field value already exists in the database
     *
     * @param int $formId
     * @param int $fieldId
     * @param string $value
     * @return bool
     * @throws QueryExecutionException
     */
    public function checkDuplicateValue(int $formId, int $fieldId, string $value): bool
    {
        global $wpdb;

        $entriesTable = EntriesTable::getTableName();

        $sql = "SELECT 1 
            FROM {$entriesTable} e
            INNER JOIN {$this->table} ef ON e.id = ef.entryId
            WHERE e.formId = %d
              AND ef.fieldId = %d
              AND ef.fieldValue = %s
            LIMIT 1";

        $params = [$formId, $fieldId, $value];

        $exists = $wpdb->get_var($wpdb->prepare($sql, $params));

        if ($exists === false) {
            throw new QueryExecutionException(
                sprintf(
                    BackendStrings::getExceptionStrings()['failed_check_duplicates'],
                    $wpdb->last_error ?: BackendStrings::getExceptionStrings()['unknown_error']
                )
            );
        }

        return !empty($exists);
    }
}
