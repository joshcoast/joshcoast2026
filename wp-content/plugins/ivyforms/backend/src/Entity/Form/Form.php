<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Entity\Form;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\ValueObjects\Form\Form as FormValueObject;
use IvyForms\ValueObjects\Form\IntegrationSettings;

/**
 * Class Form
 *
 * @package IvyForms\Entity\Form
 */
class Form
{
    /**
     * @var FormValueObject
     */
    private FormValueObject $form;

    /**
     * Form constructor.
     *
     * @param FormValueObject $form The form object.
     */
    public function __construct(FormValueObject $form)
    {
        $this->form = $form;
    }

    /**
     * Get the ID of the form.
     *
     * @return int The form ID.
     */
    public function getId(): int
    {
        return $this->form->getId();
    }

    /**
     * Set the ID of the form.
     *
     * @param int $formId The form ID to set.
     */
    public function setId(int $formId): void
    {
        $this->form->id = $formId;
    }

    /**
     * Get the name of the form.
     *
     * @return string The form name.
     */
    public function getName(): string
    {
        return $this->form->getName();
    }

    /**
     * Set the name of the form.
     *
     * @param string $name The form name to set.
     */
    public function setName(string $name): void
    {
        $this->form->name = $name;
    }

    /**
     * Get the author of the form.
     *
     * @return string The form author.
     */
    public function getAuthor(): string
    {
        return $this->form->getAuthor();
    }

    /**
     * Set the author of the form.
     *
     * @param string $author The form author to set.
     */
    public function setAuthor(string $author): void
    {
        $this->form->author = $author;
    }

    /**
     * Get the starred status of the form.
     *
     * @return bool The form starred status.
     */
    public function isStarred(): bool
    {
        return $this->form->isStarred();
    }

    /**
     * Set the starred status of the form.
     *
     * @param bool $starred The form starred status to set.
     */
    public function setStarred(bool $starred): void
    {
        $this->form->starred = $starred;
    }

    /**
     * Get the published status of the form.
     *
     * @return bool The form published status.
     */
    public function isPublished(): bool
    {
        return $this->form->isPublished();
    }

    /**
     * Set the published status of the form.
     *
     * @param bool $published The form published status to set.
     */
    public function setPublished(bool $published): void
    {
        $this->form->published = $published;
    }

    /**
     * Get the creation date of the form.
     *
     * @return string The form of date creation.
     */
    public function getDateCreated(): string
    {
        return $this->form->getDateCreated();
    }
    /**
     * Get the date edited of the form.
     *
     * @return string The form date edited.
     */
    public function getDateEdited(): string
    {
        return $this->form->getDateEdited();
    }

    /**
     * Get the form fields.
     *
     * @return mixed[] The form fields.
     */
    public function getFields(): array
    {
        return $this->form->getFields();
    }

    /**
     * Set the form fields.
     *
     * @param mixed[] $fields The form fields to set.
     */
    public function setFields(array $fields): void
    {
        $this->form->fields = $fields;
    }

    /**
     * Get the form description.
     *
     * @return string The form description.
     */
    public function getDescription(): string
    {
        return $this->form->getDescription();
    }

    /**
     * Set the form description.
     *
     * @param string $description The form description to set.
     */
    public function setDescription(string $description): void
    {
        $this->form->description = $description;
    }

    /**
     * Get the show title of the form.
     *
     * @return bool The form show title status.
     */
    public function isTitleVisible(): bool
    {
        return $this->form->isTitleVisible();
    }

    /**
     * Set the show title of the form.
     *
     * @param bool $showTitle The form show title to set.
     */
    public function setTitleVisible(bool $showTitle): void
    {
        $this->form->showTitle = $showTitle;
    }
    /**
     * Get the show description of the form.
     *
     * @return bool The form show description status.
     */
    public function isDescriptionVisible(): bool
    {
        return $this->form->isDescriptionVisible();
    }


    /**
     * Set the show description of the form.
     *
     * @param bool $description The form show title to set.
     */
    public function setDescriptionVisible(bool $description): void
    {
        $this->form->showDescription = $description;
    }

    /**
     *
     * Set whether to store entries or not.
     *
     * @param bool $storeEntries
     * @return void
     */
    public function setStoreEntries(bool $storeEntries): void
    {
        $this->form->storeEntries = $storeEntries;
    }
    /**
     *
     * Get whether to store entries or not.
     *
     * @return bool
     */
    public function isStoreEntries(): bool
    {
        return $this->form->storeEntries;
    }

    /**
     * Get integration settings.
     * @return IntegrationSettings|null The integration settings.
     */
    public function getIntegrationSettings(): ?IntegrationSettings
    {
        return $this->form->integrationSettings;
    }
    /**
     * Set integration settings.
     * @param IntegrationSettings|null $settings The integration settings to set.
     */
    public function setIntegrationSettings(?IntegrationSettings $settings): void
    {
        $this->form->integrationSettings = $settings;
    }

    /**
     * Get form action buttons settings.
     *
     * @return array<string, mixed> The form action buttons settings.
     */
    public function getFormActionButtons(): array
    {
        return $this->form->formActionButtons;
    }

    /**
     * Set form action buttons settings.
     *
     * @param array<string, mixed> $formActionButtons The form action buttons settings to set.
     * @return void
     */
    public function setFormActionButtons(array $formActionButtons): void
    {
        $this->form->formActionButtons = $formActionButtons;
    }

    /**
     * Convert the form entity to an array.
     *
     * @return array<string, mixed> The form entity as an array.
     */
    public function toArray(): array
    {
        $data = array_merge(
            [
                'id'                    => $this->getId(),
                'name'                  => $this->getName(),
                'author'                => $this->getAuthor(),
                'starred'               => $this->isStarred(),
                'published'             => $this->isPublished(),
                'dateCreated'           => $this->getDateCreated(),
                'dateEdited'            => $this->getDateEdited(),
                'fields'                => $this->getFields(),
                'description'           => $this->getDescription(),
                'showTitle'             => $this->isTitleVisible(),
                'showDescription'       => $this->isDescriptionVisible(),
                'storeEntries'          => $this->isStoreEntries(),
                'formActionButtons'     => $this->getFormActionButtons(),
            ],
            $this->getIntegrationSettings()->toArray()
        );
        return $data;
    }
}
