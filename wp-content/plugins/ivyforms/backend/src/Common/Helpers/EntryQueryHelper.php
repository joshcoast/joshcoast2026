<?php

namespace IvyForms\Common\Helpers;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Entry\EntriesTable;
use IvyForms\Services\InstallActions\DB\EntryField\EntryFieldsTable;
use IvyForms\Services\InstallActions\DB\Form\FormsTable;

class EntryQueryHelper
{
    /**
     * Helper to return search conditions for entries (for complexity reduction)
     *
     * @param array<string, mixed>|null $params
     * @return array<string>
     */
    public static function getEntrySearchConditions(?array $params = null): array
    {
        $conditions = [
            'e.id LIKE %s',
            'e.formId LIKE %s',
            'f.name LIKE %s',
        ];
        // Only include fieldValue if searchFieldValue is true (default true for BC)
        if (!isset($params['searchFieldValue']) || $params['searchFieldValue']) {
            $conditions[] = 'eft.fieldValue LIKE %s';
        }
        return $conditions;
    }

    /**
     * Get entry fields SQL and params for the given entries.
     *
     * @param array<int, array<string, mixed>> $entries
     * @return array{sql: string, params: array<int, int>}
     * @throws InvalidArgumentException
     */
    public static function getEntryFieldsQuery(array $entries): array
    {
        if (empty($entries)) {
            return ['sql' => '', 'params' => []];
        }
        $entryIds = array_column($entries, 'id');
        $placeholders = implode(',', array_fill(0, count($entryIds), '%d'));
        $entryFieldsTable = EntryFieldsTable::getTableName();
        $sql = "SELECT * FROM {$entryFieldsTable} WHERE entryId IN ($placeholders)";
        return ['sql' => $sql, 'params' => $entryIds];
    }

    /**
     * Get the count of entries for a specific form (SQL and params).
     *
     * @param string $table
     * @param int $formId
     * @return array{sql: string, params: array<int, int>}
     */
    public static function getCountByFormIdQuery(string $table, int $formId): array
    {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE formId = %d";
        return ['sql' => $sql, 'params' => [$formId]];
    }

    /**
     * Get the count of entries for the filter dropdown (SQL and params).
     *
     * @param string $table
     * @param array<string, mixed>|null $params
     * @return array{sql: string, params: array<int, int>}
     */
    public static function getFilterCountQuery(string $table, ?array $params = null): array
    {
        $where = '';
        $queryParams = [];
        if (!empty($params['filters']['formId'])) {
            $where = 'WHERE formId = %d';
            $queryParams[] = (int)$params['filters']['formId'];
        }
        $sql = "SELECT 
            SUM(status = 'read') as readTrueCount,
            SUM(status = 'unread') as readFalseCount,
            SUM(starred = 1) as starredTrueCount,
            SUM(starred = 0) as starredFalseCount
        FROM {$table} $where";
        return ['sql' => $sql, 'params' => $queryParams];
    }

    /**
     * Get the count of entries for multiple form IDs (SQL and params).
     *
     * @param int[] $formIds
     * @return array{sql: string, params: array<int, int>, formIds: array<int, int>}
     * @throws InvalidArgumentException
     */
    public static function getEntryCountByFormIdsQuery(array $formIds): array
    {
        if (empty($formIds)) {
            return ['sql' => '', 'params' => [], 'formIds' => []];
        }
        $formIds = array_map('intval', $formIds);
        $placeholders = implode(',', array_fill(0, count($formIds), '%d'));
        $tableName = EntriesTable::getTableName();
        $sql = "SELECT formId, COUNT(*) as count FROM {$tableName} WHERE formId IN ($placeholders) GROUP BY formId";
        return ['sql' => $sql, 'params' => $formIds, 'formIds' => $formIds];
    }

    /**
     * Build the SELECT query and parameters for entries, with or without pagination.
     * @param string $entriesTable
     * @param string $join
     * @param string $whereClauses
     * @param string $sortColumn
     * @param string $order
     * @param array<int|string, mixed> $queryParams
     * @param int|string $perPage
     * @param int $page
     * @return array{sql: string, params: array<int|string, mixed>}
     */
    public static function buildEntrySelectQuery(
        string $entriesTable,
        string $join,
        string $whereClauses,
        string $sortColumn,
        string $order,
        array $queryParams,
        $perPage,
        int $page
    ): array {
        $isAll = ($perPage === 'all' || $perPage === 0);
        if ($isAll) {
            $sql = "SELECT DISTINCT e.* FROM {$entriesTable} e $join
            WHERE {$whereClauses} ORDER BY {$sortColumn} {$order}";
            return ['sql' => $sql, 'params' => $queryParams];
        }
        $perPage = max((int)$perPage, 1);
        $offset = ($page - 1) * $perPage;
        $params = $queryParams;
        $params[] = $perPage;
        $params[] = $offset;
        $sql = "SELECT DISTINCT e.* FROM {$entriesTable} e $join
        WHERE {$whereClauses} ORDER BY {$sortColumn} {$order} LIMIT %d OFFSET %d";
        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Get the JOIN clause for entries, entry fields, and forms.
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getEntryJoinClause(): string
    {
        $entryFieldsTable = EntryFieldsTable::getTableName();
        $formsTable = FormsTable::getTableName();
        $join = " LEFT JOIN {$entryFieldsTable} eft ON e.id = eft.entryId ";
        $join .= " LEFT JOIN {$formsTable} f ON e.formId = f.id ";
        return $join;
    }

    /**
     * Build search WHERE clause and query parameters for entry search.
     * @param array<string, mixed>|null $params
     * @return array{where: string|null, params: array<int, string>}
     */
    public static function buildSearchWhereClause(?array $params): array
    {
        if (!empty($params['search'])) {
            global $wpdb;
            $searchEscaped = '%' . $wpdb->esc_like($params['search']) . '%';
            $searchConditions = self::getEntrySearchConditions($params);
            $where = '(' . implode(' OR ', $searchConditions) . ')';
            $queryParams = array_fill(0, count($searchConditions), $searchEscaped);
            return ['where' => $where, 'params' => $queryParams];
        }
        return ['where' => null, 'params' => []];
    }

    /**
     * Build filter WHERE clause and query parameters for entry filter.
     * @param array<string, mixed>|null $params
     * @param array<int, string> $filterableColumns
     * @return array{where: array<int, string>, params: array<int, string>}
     */
    public static function buildFilterWhereClause(?array $params, array $filterableColumns): array
    {
        $whereClauses = [];
        $queryParams = [];
        if (!empty($params['filters'])) {
            foreach ($params['filters'] as $key => $value) {
                if (in_array($key, $filterableColumns, true) && $value !== null && $value !== '') {
                    $whereClauses[] = "e.{$key} = %s";
                    $queryParams[] = sanitize_text_field($value);
                }
            }
        }
        return ['where' => $whereClauses, 'params' => $queryParams];
    }
}
