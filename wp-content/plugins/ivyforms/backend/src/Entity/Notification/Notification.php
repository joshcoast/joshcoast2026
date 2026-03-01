<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Entity\Notification;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\ValueObjects\Notification\Notification as NotificationValueObject;

/**
 * Class Notification
 *
 * @package IvyForms\Entity\Notification
 */
class Notification
{
    /**
     * @var NotificationValueObject
     */
    private NotificationValueObject $notification;

    /**
     * Notification constructor.
     *
     * @param NotificationValueObject $notification The notification object.
     */
    public function __construct(NotificationValueObject $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the notification ID.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->notification->getId();
    }

    /**
     * Set the notification ID.
     *
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->notification->id = $id;
    }

    /**
     * Get the notification name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->notification->getName();
    }

    /**
     * Set the notification name.
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->notification->name = $name;
    }

    /**
     * Get the notification sender.
     *
     * @return string
     */
    public function getSender(): string
    {
        return $this->notification->getSender();
    }

    /**
     * Set the notification sender.
     *
     * @param string $sender
     */
    public function setSender(string $sender): void
    {
        $this->notification->sender = $sender;
    }

    /**
     * Get the notification reply-to address.
     *
     * @return string
     */
    public function getReplyTo(): string
    {
        return $this->notification->getReplyTo();
    }

    /**
     * Set the notification reply-to address.
     *
     * @param string $replyTo
     */
    public function setReplyTo(string $replyTo): void
    {
        $this->notification->replyTo = $replyTo;
    }

    /**
     * Get the notification receiver.
     *
     * @return string
     */
    public function getReceiver(): string
    {
        return $this->notification->getReceiver();
    }

    /**
     * Set the notification receiver.
     *
     * @param string $receiver
     */
    public function setReceiver(string $receiver): void
    {
        $this->notification->receiver = $receiver;
    }

    /**
     * Get the notification status.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->notification->isEnabled();
    }

    /**
     * Set the notification status.
     *
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->notification->enabled = $enabled;
    }

    /**
     * Get the notification subject.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->notification->getSubject();
    }

    /**
     * Set the notification subject.
     *
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->notification->subject = $subject;
    }

    /**
     * Get the notification message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->notification->getMessage();
    }

    /**
     * Set the notification message.
     *
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->notification->message = $message;
    }

    /**
     * Get the smart logic status.
     *
     * @return bool
     */
    public function isSmartLogic(): bool
    {
        return $this->notification->isSmartLogic();
    }

    /**
     * Set the smart logic status.
     *
     * @param bool $smartLogic
     */
    public function setSmartLogic(bool $smartLogic): void
    {
        $this->notification->smartLogic = $smartLogic;
    }

    /**
     * Get the form ID.
     *
     * @return int
     */
    public function getFormId(): int
    {
        return $this->notification->getFormId();
    }

    /**
     * Set the form ID.
     *
     * @param int $formId
     */
    public function setFormId(int $formId): void
    {
        $this->notification->formId = $formId;
    }

    /**
     * Convert the notification entity to an array.
     *
     * @return array<string, mixed> The notification entity as an array.
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->getId(),
            'name'       => $this->getName(),
            'sender'     => $this->getSender(),
            'replyTo'    => $this->getReplyTo(),
            'receiver'   => $this->getReceiver(),
            'enabled'    => $this->isEnabled(),
            'subject'    => $this->getSubject(),
            'message'    => $this->getMessage(),
            'smartLogic' => $this->isSmartLogic(),
            'formId'     => $this->getFormId(),
        ];
    }
}
