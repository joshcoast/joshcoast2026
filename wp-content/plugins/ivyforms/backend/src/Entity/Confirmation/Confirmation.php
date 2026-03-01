<?php

namespace IvyForms\Entity\Confirmation;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\ValueObjects\Confirmation\Confirmation as ConfirmationValueObject;
use IvyForms\Common\Helpers\Helper;

/**
 * Class Confirmation
 *
 * @package IvyForms\Entity\Confirmation
 */
class Confirmation
{
    /**
     * @var ConfirmationValueObject
     */
    private ConfirmationValueObject $confirmation;

    /**
     * Confirmation constructor.
     *
     * @param ConfirmationValueObject $confirmation The confirmation object.
     */
    public function __construct(ConfirmationValueObject $confirmation)
    {
        $this->confirmation = $confirmation;
    }

    /**
     * Get the confirmation ID.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->confirmation->getId();
    }

    /**
     * Set the confirmation ID.
     *
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->confirmation->id = $id;
    }

    /**
     * Get the form ID.
     *
     * @return int
     */
    public function getFormId(): int
    {
        return $this->confirmation->getFormId();
    }

    /**
     * Set the form ID.
     *
     * @param int $formId
     */
    public function setFormId(int $formId): void
    {
        $this->confirmation->formId = $formId;
    }

    /**
     * Get the confirmation type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->confirmation->getType();
    }

    /**
     * Set the confirmation type.
     *
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->confirmation->type = $type;
    }

    /**
     * Get the confirmation status.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->confirmation->isEnabled();
    }

    /**
     * Set the confirmation status.
     *
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->confirmation->enabled = $enabled;
    }

    /**
     * Get the show form flag.
     *
     * @return bool
     */
    public function isShowForm(): bool
    {
        return $this->confirmation->isShowForm();
    }

    /**
     * Set the show form flag.
     *
     * @param bool $showForm
     */
    public function setShowForm(bool $showForm): void
    {
        $this->confirmation->showForm = $showForm;
    }

    /**
     * Get the confirmation message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->confirmation->getMessage();
    }

    /**
     * Set the confirmation message.
     *
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->confirmation->message = $message;
    }

    /**
     * Get the confirmation URL.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->confirmation->getUrl();
    }

    /**
     * Set the confirmation URL.
     *
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->confirmation->url = $url;
    }

    /**
     * Get the confirmation page.
     *
     * @return string
     */
    public function getPage(): string
    {
        return $this->confirmation->getPage();
    }

    /**
     * Set the confirmation page.
     *
     * @param string $page
     */
    public function setPage(string $page): void
    {
        $this->confirmation->page = $page;
    }

    /**
     * Get the page URL converted from page ID.
     *
     * @return string The page URL, empty string if page is not set or not found.
     */
    public function getPageUrl(): string
    {
        return Helper::getPageUrlFromId($this->getPage());
    }

    /**
     * Convert the confirmation entity to an array.
     *
     * @return array<string, mixed> The confirmation entity as an array.
     */
    public function toArray(): array
    {
        return [
            'id'        => $this->getId(),
            'formId'    => $this->getFormId(),
            'type'      => $this->getType(),
            'enabled'   => $this->isEnabled(),
            'showForm'  => $this->isShowForm(),
            'message'   => $this->getMessage(),
            'url'       => $this->getUrl(),
            //TODO : Check if page is needed as we have pageUrl
            'page'      => $this->getPage(),
            'pageUrl'   => $this->getPageUrl(),
        ];
    }
}
