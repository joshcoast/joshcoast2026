<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Form;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Helpers\EntryHelper;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Factory\Entry\EntryFactory;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Field\FieldType;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Mailer\MailerService;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Placeholder\PlaceholderService;
use IvyForms\Services\Security\SecurityService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class FormSubmissionController
 *
 * @package IvyForms\Controllers\Form
 */
class FormSubmissionController extends Controller
{
    private FormService $formService;
    private FieldService $fieldService;
    private NotificationService $notificationService;
    private MailerService $mailerService;
    private EntryService $entryService;
    private SecurityService $securityService;
    private ConfirmationService $confirmationService;

    public function __construct(
        FormService $formService,
        FieldService $fieldService,
        NotificationService $notificationService,
        MailerService $mailerService,
        EntryService $entryService,
        SecurityService $securityService,
        ConfirmationService $confirmationService
    ) {
        $this->formService = $formService;
        $this->fieldService = $fieldService;
        $this->notificationService = $notificationService;
        $this->mailerService = $mailerService;
        $this->entryService = $entryService;
        $this->securityService = $securityService;
        $this->confirmationService = $confirmationService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException|NotFoundException|ForbiddenException
     * @throws ValidationException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $formId = Sanitizer::sanitizeId($data->get_param('formId'));
        Sanitizer::verifySubmissionNonce($data->get_param('nonce'), $formId);

        if ($formId <= 0) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_form_id']
            );
        }

        $form = $this->formService->getFormById($formId);
        $formFields = $this->fieldService->getAllFields($form->getId());

        $this->fieldService->validateFieldsType($formFields);

        // Build fieldLabels mapping for placeholders
        $fieldLabels = PlaceholderService::buildFieldLabels($formFields);

        // Validate CAPTCHA if present using SecurityService (automatically determines provider and validates)
        $this->securityService->validateFormSubmission($data->get_params(), $formFields);

        // Sanitize form submission data based on field types
        $submissionData = Sanitizer::sanitizeFormSubmissionData($data->get_params(), $formFields);

        [$isDuplicate, $duplicateErrors] = $this->fieldService->checkDuplicateFieldValues(
            $formFields,
            $submissionData,
            $formId
        );
        if ($isDuplicate) {
            return new WP_REST_Response([
                'data'    => [
                    'success' => null,
                    'entry' => ['stored' => false],
                    'confirmation' => null,
                    'is_duplicate' => true,
                    'duplicate_errors' => $duplicateErrors,
                ],
            ], 200);
        }

        $entryId = null;
        $entry = [
            'stored' => false
        ];
        /**
         * Allow to hook before form submission for integrations
         * @since 0.1.0
         *
         * Arguments:
         * - int $formId The ID of the form being submitted
         * - array $submissionData The form submission data (field values)
         * - array $formFields The form field definitions
         */
        do_action('ivyforms/form/before_submission', $formId, $submissionData, $formFields);

        if ($form->isStoreEntries()) {
            $entryData = EntryHelper::buildEntryData($formId);
            $entryObj = EntryFactory::create($entryData);
            $entryId = $this->entryService->getEntryManager()->createEntry($entryObj);
            $this->entryService->getEntryFieldManager()->addEntryFields($formFields, $entryId, $submissionData);
            $entry = [
                'stored' => true,
                'id' => $entryId,
            ];
        }

        // Build field and general entry data for placeholders
        $fieldData = PlaceholderService::buildFieldData($formFields, $submissionData);
        $generalData = PlaceholderService::buildGeneralData($entryId ?? 0, $submissionData);

        // Use NotificationService for notifications
        // Note: Notification messages are sanitized inside processNotifications() method
        // to allow signature images with data: URIs (using Sanitizer::sanitizeHtmlContent)
        $notificationsResult = $this->notificationService->processNotifications(
            $formId,
            $submissionData,
            $fieldData,
            $generalData,
            $this->mailerService,
            $fieldLabels
        );
        // Use ConfirmationService for confirmations
        $confirmationMessage = $this->confirmationService->processConfirmations(
            $formId,
            $fieldData,
            $generalData,
            $fieldLabels
        );

        // Sanitize confirmation message HTML to preserve colors and formatting
        $safeConfirmationMessage = Sanitizer::sanitizeEditorContent($confirmationMessage);

        /**
         * Allow to hook after form submission for integrations
         * @since 0.1.0
         *
         * Arguments:
         * - int $formId The ID of the form being submitted
         * - array $submissionData The form submission data (field values)
         * - array $formFields The form field definitions
         * - int|null $entryId The entry ID if entry storage is enabled
         */
        do_action('ivyforms/form/after_submission', $formId, $submissionData, $formFields, $entryId);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => [
                'success' => $notificationsResult,
                'entry' => $entry,
                'confirmation' => $safeConfirmationMessage,
            ],
        ], 200);
    }
}
