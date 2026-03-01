<?php

namespace IvyForms\ValueObjects\Form;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\ValueObjects\Form\IntegrationSettings;

/**
 * Class Form
 *
 * @package IvyForms\ValueObjects\Form
 */
final class Form
{
    /**
     * @var int
     */
    public int $id;
    /**
     * @var string
     */
    public string $name;
    /**
     * @var string
     */
    public string $author;
    /**
     * @var bool
     */
    public bool $published;
    /**
     * @var bool
     */
    public bool $starred;
    /**
     * @var string
     */
    public string $dateCreated;
    /**
     * @var string
     */
    public string $dateEdited;
    /**
     * @var array<int, array<string, mixed>>|null
     */
    public ?array $fields;
    /**
     * @var string
     */
    public string $description;
    /**
     * @var bool
     */
    public bool $showTitle;
    /**
     * @var bool
     */
    public bool $showDescription;
    /**
     * @var bool
     */
    public bool $storeEntries;
    /**
     * @var IntegrationSettings|null
     */
    public ?IntegrationSettings $integrationSettings;

    /**
     * @var array<string, mixed>
     */
    public array $formActionButtons;

    /**
     * Form constructor.
     *
     * @param int $id
     * @param string $name
     * @param string $author
     * @param bool $starred
     * @param bool $published
     * @param string $description
     * @param bool $showTitle
     * @param bool $showDescription
     * @param bool $storeEntries
     * @param array<int, array<string, mixed>> $fields
     * @param IntegrationSettings|null $integrationSettings
     * @param array<string, mixed>|null $formActionButtons
     * @throws ValidationException
     * @SuppressWarnings("ExcessiveParameterList")
     */
    public function __construct(
        int $id,
        string $name,
        string $author,
        bool $starred,
        bool $published,
        string $description,
        bool $showTitle,
        bool $showDescription,
        bool $storeEntries,
        array $fields = [],
        IntegrationSettings $integrationSettings = null,
        ?array $formActionButtons = null
    ) {
        $this->id                   = $this->validateId($id);
        $this->name                 = $this->validateString($name, 255, 'name');
        $this->author               = $this->validateString($author, 1000, 'author');
        $this->starred              = $this->validateBool($starred);
        $this->published            = $this->validateBool($published);
        $this->dateCreated          = gmdate('Y-m-d H:i:s');
        $this->dateEdited           = gmdate('Y-m-d H:i:s');
        $this->fields               = $fields;
        $this->description          = $this->validateString($description, 1000, 'description');
        $this->showTitle            = $this->validateBool($showTitle);
        $this->showDescription      = $this->validateBool($showDescription);
        $this->storeEntries         = $this->validateBool($storeEntries);
        $this->integrationSettings  = $integrationSettings;
        $this->formActionButtons = $formActionButtons ?? [
            'submitButtonSettings' => [
                'label'    => BackendStrings::getCommonStrings()['submit'],
                'position' => 'default'
            ]
        ];
    }

    /**
     * Validates the ID.
     *
     * @param int $id
     *
     * @return int
     * @throws ValidationException
     */
    private function validateId(int $id): int
    {
        if ($id < 0) {
            throw new ValidationException(BackendStrings::getExceptionStrings()['id_positive_integer']);
        }
        return $id;
    }

    /**
     * Validates a string value.
     *
     * @param string $value
     * @param int    $maxLength
     * @param string $fieldName
     *
     * @return string
     *
     * @throws ValidationException
     */
    private function validateString(string $value, int $maxLength, string $fieldName): string
    {
        if (strlen($value) > $maxLength) {
            throw new ValidationException(
                sprintf(
                /* translators: 1: String value, 2: String max length. */
                    esc_html__('%1$s must be at most %2$d characters.', 'ivyforms'),
                    esc_html($fieldName),
                    $maxLength
                )
            );
        }
        return $value;
    }

    /**
     * Validates a boolean field.
     *
     * @param bool $value
     *
     * @return bool
     */
    private function validateBool(bool $value): bool
    {
        return $value;
    }

    /**
     * Get the form ID.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the form name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the form author.
     *
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Get the form starred status.
     *
     * @return bool
     */
    public function isStarred(): bool
    {
        return $this->starred;
    }
    /**
     * Get the form published status.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published;
    }
    /**
     * Get the date of form creation.
     *
     * @return string
     */
    public function getDateCreated(): string
    {
        return $this->dateCreated;
    }
    /**
     * Get the form date edited.
     *
     * @return string
     */
    public function getDateEdited(): string
    {
        return $this->dateEdited;
    }
    /**
     * Get the form fields.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }
    /**
     * Get the form description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
    /**
     * Get the show description of the form.
     *
     * @return bool The form show description status.
     */
    public function isDescriptionVisible(): bool
    {
        return $this->showDescription;
    }
    /**
     * Get the form store entries' status.
     * @return bool
     */
    public function isStoreEntries(): bool
    {
        return $this->storeEntries;
    }

    /**
     * Get integration settings.
     * @return IntegrationSettings|null
     */
    public function getIntegrationSettings(): ?IntegrationSettings
    {
        return $this->integrationSettings;
    }

    /**
     * Set integration settings.
     * @param IntegrationSettings $settings
     */
    public function setIntegrationSettings(IntegrationSettings $settings): void
    {
        $this->integrationSettings = $settings;
    }

    /**
     * Get the show title of the form.
     *
     * @return bool The form show title status.
     */
    public function isTitleVisible(): bool
    {
        return $this->showTitle;
    }

    /**
     * Convert the form to an array.
     *
     * @return array<string, mixed>
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
            ],
            $this->getIntegrationSettings()->toArray()
        );
        return $data;
    }
}
