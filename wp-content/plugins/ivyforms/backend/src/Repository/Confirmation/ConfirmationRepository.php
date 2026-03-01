<?php

namespace IvyForms\Repository\Confirmation;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Entity\Confirmation\Confirmation;
use IvyForms\Factory\Confirmation\ConfirmationFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Repository\Confirmation\ConfirmationRepositoryInterface;
use IvyForms\Services\Translations\BackendStrings;

class ConfirmationRepository extends AbstractRepository implements ConfirmationRepositoryInterface
{
    public const FACTORY = ConfirmationFactory::class;

    /**
     * Add confirmation to the database
     *
     * @param Confirmation $entity
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
                'formId'    => (int) $data['formId'],
                'type'      => $data['type'],
                'enabled'   => (int) $data['enabled'],
                'showForm'  => (int) $data['showForm'],
                'message'   => $data['message'],
                'url'       => $data['url'],
                'page'      => $data['page'],
            ],
            ['%d', '%s', '%d', '%d', '%s', '%s', '%s']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Update confirmation in the database
     *
     * @param int $id
     * @param Confirmation $entity
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
                'formId'    => (int) $data['formId'],
                'type'      => $data['type'],
                'enabled'   => (int) $data['enabled'],
                'showForm'  => (int) $data['showForm'],
                'message'   => $data['message'],
                'url'       => $data['url'],
                'page'      => $data['page'],
            ],
            ['id' => $id],
            ['%d', '%s', '%d', '%d', '%s', '%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['unable_to_save_data'] . __CLASS__);
        }

        return $result;
    }

    /**
     * Get all confirmations by form ID.
     *
     * @param int $id
     *
     * @return array<Confirmation>
     * @throws QueryExecutionException
     */
    public function getAllById(int $id): array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                $this->selectQuery() . " WHERE $this->table.formId = %d",
                $id
            ),
            ARRAY_A
        );
        if ($rows === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }
        if (empty($rows)) {
            return [];
        }
        return array_map(function ($row) {
            return call_user_func([static::FACTORY, 'create'], $row);
        }, $rows);
    }
}
