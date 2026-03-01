<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository\Form;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Entity\Form\Form;
use IvyForms\Factory\Form\FormFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Services\InstallActions\DB\Notification\NotificationsTable;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\ValueObjects\Form\IntegrationSettings;

class FormRepository extends AbstractRepository implements FormRepositoryInterface
{
    public const FACTORY = FormFactory::class;

    /**
     * Add form to the database
     *
     * @param Form $entity
     *
     * @return int
     *
     * @throws QueryExecutionException
     */
    public function add($entity): int
    {
        $data = $entity->toArray();

        $result = $this->wpdb->insert(
            $this->table,
            [
                'name'                => $data['name'],
                'author'              => wp_get_current_user()->display_name,
                'starred'             => (int) $data['starred'],
                'published'           => (int) $data['published'],
                'dateCreated'         => current_time('mysql'),
                'dateEdited'          => current_time('mysql'),
                'description'         => $data['description'],
                'settings'            => json_encode([
                    'showTitle'            => (int) $data['showTitle'],
                    'showDescription'      => (int) $data['showDescription'],
                    'storeEntries'         => (int) $data['storeEntries'],
                    'formActionButtons'    => $data['formActionButtons'] ?? [
                        'submitButtonSettings' => [
                            'label'    => BackendStrings::getCommonStrings()['submit'],
                            'position' => 'default'
                        ]
                    ],
                ]),
                'integrationSettings' => json_encode($data['integrationSettings']),
            ],
            ['%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Update form in the database
     *
     * @param int  $id
     * @param Form $entity
     *
     * @return bool
     *
     * @throws QueryExecutionException
     */
    public function update(int $id, $entity): bool
    {
        $data = $entity->toArray();

        $result = $this->wpdb->update(
            $this->table,
            [
                'name'                => $data['name'],
                'starred'             => (int) $data['starred'],
                'published'           => (int) $data['published'],
                'description'         => $data['description'],
                'dateEdited'          => current_time('mysql'),
                'settings'            => json_encode([
                    'showTitle'            => (int)$data['showTitle'],
                    'showDescription'      => (int)$data['showDescription'],
                    'storeEntries'         => (int)$data['storeEntries'],
                    'formActionButtons'    => $data['formActionButtons'] ?? [
                        'submitButtonSettings' => [
                            'label'    => BackendStrings::getCommonStrings()['submit'],
                            'position' => 'default'
                        ]
                    ],
                ]),
                'integrationSettings' => json_encode($data['integrationSettings']),
            ],
            ['id' => $id],
            ['%s', '%d', '%d', '%s', '%s', '%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['unable_to_save_data'] . __CLASS__);
        }

        return $result;
    }

    /**
     * Update the form starred value by ID.
     *
     * @param int $id
     * @param string  $value
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function updateFormStarred(int $id, string $value): bool
    {
        // Update the starred column in the database
        $result = $this->wpdb->update(
            $this->table,
            ['starred' => $value],
            ['id' => $id],
            ['%d'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['unable_to_save_data'] . __CLASS__);
        }

        return $result;
    }

    /**
     * Update the published status of a form by ID.
     *
     * @param int $formId
     * @param string $value
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function updateFormStatus(int $formId, string $value): bool
    {
        // Update the published column in the database
        $result = $this->wpdb->update(
            $this->table,
            ['published' => $value],
            ['id' => $formId],
            ['%d'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['unable_to_save_data'] . __CLASS__);
        }

        return $result;
    }

    /**
     * Get the count of filters for the filter dropdown.
     *
     * @return array<string, int>
     * @throws QueryExecutionException
     */
    public function getFilterCount(): array
    {
        $result = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT 
            SUM(published = 1) as publishedTrueCount,
            SUM(published = 0) as publishedFalseCount,
            SUM(starred = 1) as starredTrueCount,
            SUM(starred = 0) as starredFalseCount
        FROM {$this->table}"
            ),
            ARRAY_A
        );
        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_search_entries'] . __CLASS__
            );
        }
        // Convert all values to integers and provide defaults if query returns null
        return array_map('intval', $result ?: [
            'publishedTrueCount' => 0,
            'publishedFalseCount' => 0,
            'starredTrueCount' => 0,
            'starredFalseCount' => 0,
        ]);
    }

    /**
     * Check if a form exists by its ID.
     *
     * @param int $formId
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function exists(int $formId): bool
    {
        $query = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM " . $this->table . " WHERE id = %d",
            $formId
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
     * Get searchable columns
     *
     * @return array<string>
     */
    protected function getSearchableColumns(): array
    {
        return ['name', 'author', 'description'];
    }

    /**
     * Get filterable columns
     *
     * @return array<string>
     */
    protected function getFilterableColumns(): array
    {
        return ['starred', 'published'];
    }

    /**
     * Get sortable columns
     *
     * @return array<string>
     */
    protected function getSortableColumns(): array
    {
        return ['id', 'name', 'dateCreated'];
    }

    /**
     * Get the date column
     *
     * @return string
     */
    protected function getDateColumn(): string
    {
        return 'dateCreated';
    }
}
