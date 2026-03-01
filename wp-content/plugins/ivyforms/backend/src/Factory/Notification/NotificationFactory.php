<?php

namespace IvyForms\Factory\Notification;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Entity\Notification\Notification as NotificationEntity;
use IvyForms\ValueObjects\Notification\Notification;

/**
 * Class NotificationFactory
 *
 * @package IvyForms\Factory\Notification
 */
class NotificationFactory
{
    /**
     * Create a NotificationEntity from an array of data.
     *
     * @param array<string, mixed> $data
     *
     * @return NotificationEntity
     * @throws InvalidArgumentException
     */
    public static function create(array $data): NotificationEntity
    {
        $notificationValueObject = new Notification(
            $data['id'] ?? 0,
            $data['name'] ?? '',
            $data['sender'] ?? '',
            $data['replyTo'] ?? '',
            $data['receiver'] ?? '',
            $data['enabled'] ?? true,
            $data['subject'] ?? '',
            $data['message'] ?? '',
            $data['smartLogic'] ?? false,
            $data['formId'] ?? 0
        );

        $notificationEntity = new NotificationEntity($notificationValueObject);

        if (isset($data['id'])) {
            $notificationEntity->setId($data['id']);
        }

        if (isset($data['name'])) {
            $notificationEntity->setName($data['name']);
        }

        if (isset($data['sender'])) {
            $notificationEntity->setSender($data['sender']);
        }

        if (isset($data['replyTo'])) {
            $notificationEntity->setReplyTo($data['replyTo']);
        }

        if (isset($data['receiver'])) {
            $notificationEntity->setReceiver($data['receiver']);
        }

        if (isset($data['enabled'])) {
            $notificationEntity->setEnabled($data['enabled']);
        }

        if (isset($data['subject'])) {
            $notificationEntity->setSubject($data['subject']);
        }

        if (isset($data['message'])) {
            $notificationEntity->setMessage(wp_unslash($data['message']));
        }

        if (isset($data['smartLogic'])) {
            $notificationEntity->setSmartLogic($data['smartLogic']);
        }

        if (isset($data['formId'])) {
            $notificationEntity->setFormId($data['formId']);
        }

        return $notificationEntity;
    }
}
