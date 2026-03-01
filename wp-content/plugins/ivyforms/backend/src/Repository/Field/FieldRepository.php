<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository\Field;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Entity\Field\Field;
use IvyForms\Factory\Field\FieldFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class FieldRepository
 *
 * @package IvyForms\Repository\Field
 */
class FieldRepository extends AbstractRepository implements FieldRepositoryInterface
{
    public const FACTORY = FieldFactory::class;

    /**
     * Add field to the database
     *
     * @param Field $entity
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
                'fieldIndex'   => $data['fieldIndex'],
                'formId'       => $data['formId'],
                'type'         => $data['type'],
                'label'        => $data['label'],
                'required'     => (int)$data['required'],
                'defaultValue' => $data['defaultValue'],
                'placeholder'  => $data['placeholder'],
                'position'     => $data['position'],
                'rowIndex'     => $data['rowIndex'] ?? 0,
                'columnIndex'  => $data['columnIndex'] ?? 0,
                'width'        => $data['width'] ?? 100,
                'parentId'     => $data['parentId'] ?? null,
                'settings'     => json_encode(
                    /**
                     * Allows modification of the settings data before saving to database.
                     * @since 0.1.0
                     *
                     * @param mixed[] $settings The current settings array.
                     * @param mixed[] $data The full field data array.
                     * @param Field $entity The Field entity instance.
                     * @return mixed[] The modified settings array.
                     */
                    apply_filters(
                        'ivyforms/repository/field/settings',
                        [
                        'hideLabel'               => (int)$data['hideLabel'],
                        'readOnly'                => (int)$data['readOnly'],
                        'description'             => $data['description'],
                        'requiredMessage'         => $data['requiredMessage'],
                        'cssClasses'              => $data['cssClasses'],
                        'limitMaxLength'          => (int)$data['limitMaxLength'],
                        'maxLength'               => $data['maxLength'],
                        'labelPosition'           => $data['labelPosition'],
                        'noDuplicates'            => (int)$data['noDuplicates'],
                        'shuffleOptions'          => (int)$data['shuffleOptions'],
                        'showValues'              => (int)$data['showValues'],
                        'enableSearch'            => (int)$data['enableSearch'],
                        'rows'                    => (int)$data['rows'],
                        'confirmFieldEnabled'     => (int)($data['confirmFieldEnabled'] ?? 0),
                        'confirmFieldLabel'       => $data['confirmFieldLabel'] ?? '',
                        'confirmFieldPlaceholder' => $data['confirmFieldPlaceholder'] ?? '',
                        'confirmFieldHideLabel'   => (int)($data['confirmFieldHideLabel'] ?? 0),
                        'phoneFormat'             => $data['phoneFormat'],
                        'phoneAutoDetect'         => $data['phoneAutoDetect'],
                            'minValue'                => isset($data['minValue'])
                                ? (float)$data['minValue'] : null,
                            'maxValue'                => isset($data['maxValue'])
                                ? (float)$data['maxValue'] : null,
                        'step'                    => (float)$data['step'],
                        'numberFormat'            => $data['numberFormat'] ?? '',
                        'visible'                 => (int)$data['visible'],
                        'inputPrefix'             => $data['inputPrefix'] ?? '',
                        'inputSuffix'             => $data['inputSuffix'] ?? '',
                        'timeFieldType'           => $data['timeFieldType'] ?? '',
                        'timeFormat'              => $data['timeFormat'] ?? '',
                        'dateFieldType'           => $data['dateFieldType'] ?? '',
                        'dateFormat'              => $data['dateFormat'] ?? '',
                        'minDateValue'            => $data['minDateValue'] ?? '',
                        'maxDateValue'            => $data['maxDateValue'] ?? '',
                        'showRatingText'          => (int)($data['showRatingText'] ?? 0),
                        'ratingIcon'              => $data['ratingIcon'] ?? 'star',
                        ],
                        $data,
                        $entity
                    )
                ),
                'dateCreated'  => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s', '%d', '%s', '%s',  '%d', '%d', '%d', '%d', '%d', '%s', '%s']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Update field in the database
     *
     * @param int      $id
     * @param Field $entity
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
                'fieldIndex'   => $data['fieldIndex'],
                'formId'       => $data['formId'],
                'type'         => $data['type'],
                'label'        => $data['label'],
                'required'     => (int)$data['required'],
                'defaultValue' => $data['defaultValue'],
                'placeholder'  => $data['placeholder'],
                'position'     => $data['position'],
                'rowIndex'     => $data['rowIndex'] ?? 0,
                'columnIndex'  => $data['columnIndex'] ?? 0,
                'width'        => $data['width'] ?? 100,
                'parentId'     => $data['parentId'] ?? null,
                'settings'     => json_encode(
                    /**
                     * Allows modification of the settings data before updating database.
                     * @since 0.1.0
                     *
                     * @param mixed[] $settings The current settings array.
                     * @param mixed[] $data The full field data array.
                     * @param Field $entity The Field entity instance.
                     * @return mixed[] The modified settings array.
                     */
                    apply_filters(
                        'ivyforms/repository/field/settings',
                        [
                        'hideLabel'               => (int)$data['hideLabel'],
                        'readOnly'                => (int)$data['readOnly'],
                        'description'             => $data['description'],
                        'requiredMessage'         => $data['requiredMessage'],
                        'cssClasses'              => $data['cssClasses'],
                        'limitMaxLength'          => (int)$data['limitMaxLength'],
                        'maxLength'               => $data['maxLength'],
                        'labelPosition'           => $data['labelPosition'],
                        'noDuplicates'            => (int)$data['noDuplicates'],
                        'shuffleOptions'          => (int)$data['shuffleOptions'],
                        'showValues'              => (int)$data['showValues'],
                        'enableSearch'            => (int)$data['enableSearch'],
                        'rows'                    => (int)$data['rows'],
                        'confirmFieldEnabled'     => (int)($data['confirmFieldEnabled'] ?? 0),
                        'confirmFieldLabel'       => $data['confirmFieldLabel'] ?? '',
                        'confirmFieldPlaceholder' => $data['confirmFieldPlaceholder'] ?? '',
                        'confirmFieldHideLabel'   => (int)($data['confirmFieldHideLabel'] ?? 0),
                        'phoneFormat'             => $data['phoneFormat'],
                        'phoneAutoDetect'         => $data['phoneAutoDetect'],
                            'minValue'                => isset($data['minValue'])
                                ? (float)$data['minValue'] : null,
                            'maxValue'                => isset($data['maxValue'])
                                ? (float)$data['maxValue'] : null,
                        'step'                    => (float)$data['step'],
                        'numberFormat'            => $data['numberFormat'] ?? '',
                        'visible'                 => array_key_exists('visible', $data) ? (int)$data['visible'] : 1,
                        'inputPrefix'             => $data['inputPrefix'] ?? '',
                        'inputSuffix'             => $data['inputSuffix'] ?? '',
                        'timeFieldType'           => $data['timeFieldType'] ?? '',
                        'timeFormat'              => $data['timeFormat'] ?? '',
                        'dateFieldType'           => $data['dateFieldType'] ?? '',
                        'dateFormat'              => $data['dateFormat'] ?? '',
                        'minDateValue'            => $data['minDateValue'] ?? '',
                        'maxDateValue'            => $data['maxDateValue'] ?? '',
                        'showRatingText'          => (int)($data['showRatingText'] ?? 0),
                        'ratingIcon'              => $data['ratingIcon'] ?? 'star',
                        ],
                        $data,
                        $entity
                    )
                ),
            ],
            ['id' => $data['id']],
            ['%d', '%d', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['unable_to_save_data'] . __CLASS__);
        }

        return $result;
    }

    /**
     * Get fields by form ID
     *
     * @param int $id
     *
     * @return mixed[]
     * @throws QueryExecutionException
     */
    public function getFieldsById(int $id): array
    {
        $rows =  $this->wpdb->get_results(
            $this->wpdb->prepare(
                $this->selectQuery() . " WHERE $this->table.formId = %d ORDER BY $this->table.position ASC",
                $id
            ),
            ARRAY_A
        );
        if ($rows === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }
        $result = [];
        foreach ($rows as $row) {
            if (array_key_exists('parentId', $row)) {
                $row['parentId'] = $row['parentId'] !== null ? (int)$row['parentId'] : null;
            }
            $result[] = call_user_func([static::FACTORY, 'create'], $row);
        }
        return $result;
    }

    /**
     * Get multiple fields by their IDs in a single query.
     *
     * @param int[] $fieldIds
     * @return array<int, object> Indexed by field ID
     * @throws QueryExecutionException
     */
    public function getFieldsByIds(array $fieldIds): array
    {
        if (empty($fieldIds)) {
            return [];
        }

        $fieldIds = array_map('intval', $fieldIds);
        $placeholders = implode(',', array_fill(0, count($fieldIds), '%d'));
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                $this->selectQuery() . " WHERE $this->table.id IN ($placeholders) ORDER BY $this->table.position ASC",
                ...$fieldIds
            ),
            ARRAY_A
        );

        if ($rows === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }

        $result = [];
        foreach ($rows as $row) {
            if (array_key_exists('parentId', $row)) {
                $row['parentId'] = $row['parentId'] !== null ? (int)$row['parentId'] : null;
            }
            $field = call_user_func([static::FACTORY, 'create'], $row);
            $result[$field->getId()] = $field;
        }

        return $result;
    }

    /**
     * Delete multiple fields by their IDs in bulk, and their field options.
     *
     * @param array<int> $ids
     * @return int Number of deleted fields
     * @throws QueryExecutionException
     */
    public function deleteMany(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $deleteFieldsQuery = "DELETE FROM {$this->table} WHERE id IN ($placeholders)";
        $result = $this->wpdb->query($this->wpdb->prepare($deleteFieldsQuery, ...$ids));
        if ($result === false) {
            throw new QueryExecutionException(
                sprintf(
                    BackendStrings::getExceptionStrings()['failed_delete_by_ids'],
                    implode(', ', $ids),
                    $this->table
                )
            );
        }
        return $result;
    }

    /**
     * Delete multiple fields by foreign key values (formId), and their field options.
     *
     * @param array<int> $formIds
     * @param string $foreignColumn
     * @return int Number of deleted fields
     * @throws QueryExecutionException
     */
    public function deleteManyByForeignKeyValues(array $formIds, string $foreignColumn = 'formId'): int
    {
        if (empty($formIds)) {
            return 0;
        }
        $formIds = array_map('intval', $formIds);
        $placeholders = implode(',', array_fill(0, count($formIds), '%d'));
        $deleteFieldsQuery = "DELETE FROM {$this->table} WHERE {$foreignColumn} IN ($placeholders)";
        $result = $this->wpdb->query($this->wpdb->prepare($deleteFieldsQuery, ...$formIds));
        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['failed_delete_by_foreign_keys']
            );
        }
        return $result;
    }

    /**
     * Delete fields by a single foreign key value (formId), and their field options.
     *
     * @param int $formId
     * @param string $foreignColumn
     * @return int Number of deleted fields
     * @throws QueryExecutionException
     */
    public function deleteOneByForeignKeyValue(int $formId, string $foreignColumn = 'formId'): int
    {
        $deleteFieldsQuery = "DELETE FROM {$this->table} WHERE {$foreignColumn} = %d";
        $result = $this->wpdb->query($this->wpdb->prepare($deleteFieldsQuery, $formId));
        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['failed_delete_by_foreign_key']
            );
        }
        return $result;
    }
}
