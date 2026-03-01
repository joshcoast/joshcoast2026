<?php

namespace IvyForms\Factory\Confirmation;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Entity\Confirmation\Confirmation as ConfirmationEntity;
use IvyForms\ValueObjects\Confirmation\Confirmation;

/**
 * Class ConfirmationFactory
 *
 * @package IvyForms\Factory\Confirmation
 */
class ConfirmationFactory
{
    /**
     * Create a ConfirmationEntity from an array of data.
     *
     * @param array<string, mixed> $data
     *
     * @return ConfirmationEntity
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public static function create(array $data): ConfirmationEntity
    {
        $confirmationValueObject = new Confirmation(
            $data['id'] ?? 0,
            $data['formId'] ?? 0,
            $data['type'] ?? '',
            $data['enabled'] ?? true,
            $data['showForm'] ?? false,
            $data['message'] ?? '',
            $data['url'] ?? '',
            $data['page'] ?? ''
        );

        $confirmationEntity = new ConfirmationEntity($confirmationValueObject);

        if (isset($data['id'])) {
            $confirmationEntity->setId($data['id']);
        }

        if (isset($data['formId'])) {
            $confirmationEntity->setFormId($data['formId']);
        }

        if (isset($data['type'])) {
            $confirmationEntity->setType($data['type']);
        }

        if (isset($data['enabled'])) {
            $confirmationEntity->setEnabled($data['enabled']);
        }

        if (isset($data['showForm'])) {
            $confirmationEntity->setShowForm($data['showForm']);
        }

        if (isset($data['message'])) {
            $confirmationEntity->setMessage(wp_unslash($data['message']));
        }

        if (isset($data['url'])) {
            $confirmationEntity->setUrl($data['url']);
        }

        if (isset($data['page'])) {
            $confirmationEntity->setPage($data['page']);
        }

        return $confirmationEntity;
    }
}
