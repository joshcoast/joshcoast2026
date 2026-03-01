<?php

namespace IvyForms\Services\Confirmation;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Entity\Confirmation\Confirmation;
use IvyForms\Factory\Confirmation\ConfirmationFactory;
use IvyForms\Repository\Confirmation\ConfirmationRepositoryInterface;
use IvyForms\Services\Placeholder\PlaceholderService;
use IvyForms\Services\Translations\BackendStrings;

class ConfirmationService
{
    private ConfirmationRepositoryInterface $confirmationRepository;

    // Constructor injection for the ConfirmationRepository via PHP-DI
    public function __construct(ConfirmationRepositoryInterface $confirmationRepository)
    {
        $this->confirmationRepository = $confirmationRepository;
    }

    /**
     * Create a new confirmation.
     *
     * @param Confirmation $confirmationData
     *
     * @return int Confirmation ID
     */
    public function createConfirmation(Confirmation $confirmationData): int
    {
        // Create confirmation via repository and return new confirmation ID
        return $this->confirmationRepository->add($confirmationData);
    }

    /**
     * Update an existing confirmation.
     *
     * @param int $confirmationId
     * @param Confirmation $confirmationData
     *
     * @return bool
     */
    public function updateConfirmation(int $confirmationId, Confirmation $confirmationData): bool
    {
        return $this->confirmationRepository->update($confirmationId, $confirmationData);
    }

    /**
     * Get confirmations by form ID.
     *
     * @param int $id
     *
     * @return mixed[]|null
     *
     * @throws NotFoundException If no confirmations are found
     */
    public function getConfirmationsById(int $id): ?array
    {
        $confirmation = $this->confirmationRepository->getAllById($id);

        if (!$confirmation) {
            throw new NotFoundException(
                BackendStrings::getExceptionStrings()['confirmation_not_found']
            );
        }

        return $confirmation;
    }
    /**
     * Get a specific form by its ID.
     *
     * @param int $id
     *
     * @return object ConfirmationEntity
     *
     * @throws NotFoundException If the confirmation is not found
     */
    public function getConfirmationById(int $id): object
    {
        $confirmation = $this->confirmationRepository->getById($id);

        if (!$confirmation) {
            throw new NotFoundException(
                BackendStrings::getExceptionStrings()['confirmation_not_found']
            );
        }

        return $confirmation;
    }

    /**
     * Delete confirmations by multiple form IDs.
     *
     * @param array<int> $formIds
     * @return int Number of deleted confirmations
     * @throws InvalidArgumentException If no form IDs are provided
     */
    public function deleteConfirmationsByFormIds(array $formIds): int
    {
        if (empty($formIds)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['no_form_ids_provided']
            );
        }

        return $this->confirmationRepository->deleteManyByForeignKeyValues($formIds);
    }

    /**
     * Duplicate confirmations from one form to another.
     *
     * @param int $originalFormId
     * @param int $newFormId
     *
     * @return void
     * @throws NotFoundException
     */
    public function duplicateConfirmations(int $originalFormId, int $newFormId): void
    {
        $confirmations = $this->getConfirmationsById($originalFormId);
        foreach ($confirmations as $confirmation) {
            $confirmationData = $confirmation->toArray();
            unset($confirmationData['id']);
            $confirmationData['formId'] = $newFormId;

            $this->createConfirmation(ConfirmationFactory::create($confirmationData));
        }
    }

    /**
     * Create a default confirmation for a form.
     *
     * @param int $formId
     * @return int Confirmation ID
     */
    public function createDefaultConfirmationForForm(int $formId): int
    {
        $defaultConfirmation = [
            'formId'   => $formId,
            'type'     => 'successMessage',
            'enabled'  => 1,
            'showForm' => 0,
            'message'  => BackendStrings::getAllFormsStrings()['thanks_reaching'],
            'url'      => '',
            'page'     => '',
        ];
        $confirmation = ConfirmationFactory::create($defaultConfirmation);
        $confirmationId = $this->createConfirmation($confirmation);
        if (!$confirmationId) {
            throw new QueryExecutionException(
                BackendStrings::getSettingsFormBuilderStrings()['failed_to_create_confirmation']
            );
        }
        $confirmation->setId($confirmationId);
        return $confirmationId;
    }

    /**
     * Process confirmations for the form.
     *
     * @param int $formId
     * @param array<string, mixed> $fieldData
     * @param array<string, mixed> $generalData
     * @param array<string, string> $fieldLabels
     * @return string
     * @throws NotFoundException
     */
    public function processConfirmations(
        int $formId,
        array $fieldData,
        array $generalData,
        array $fieldLabels = []
    ): string {
        $confirmations = $this->getConfirmationsById($formId);
        foreach ($confirmations as $confirmation) {
            if (
                !empty($confirmation) &&
                $confirmation->isEnabled() &&
                $confirmation->getType() === 'successMessage'
            ) {
                $confirmationMessage = $confirmation->getMessage();
                return PlaceholderService::replacePlaceholders(
                    $confirmationMessage,
                    $fieldData,
                    $generalData,
                    $fieldLabels
                );
            }
        }
        return '';
    }
}
