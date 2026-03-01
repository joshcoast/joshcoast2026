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
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Factory\Field\FieldFactory;
use IvyForms\Factory\Form\FormFactory;
use IvyForms\Factory\Notification\NotificationFactory;
use IvyForms\Factory\Confirmation\ConfirmationFactory;
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Template\TemplateService;
use IvyForms\ValueObjects\Form\IntegrationSettings;
use WP_REST_Request;
use WP_REST_Response;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class AddFormController
 *
 * @package IvyForms\Controllers\Form
 */
class AddFormController extends Controller
{
    private FormService $formService;
    private FieldService $fieldService;
    private NotificationService $notificationService;
    private ConfirmationService $confirmationService;
    private TemplateService $templateService;

    public function __construct(
        FormService $formService,
        FieldService $fieldService,
        NotificationService $notificationService,
        ConfirmationService $confirmationService,
        TemplateService $templateService
    ) {
        $this->formService = $formService;
        $this->fieldService = $fieldService;
        $this->notificationService = $notificationService;
        $this->confirmationService = $confirmationService;
        $this->templateService = $templateService;
    }

    /**
     * @param WP_REST_Request<array<string, mixed>> $data
     *
     * @return WP_REST_Response
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Check if the form data is provided
        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        // TODO Add validation for template_id with Sanitizer
        //$templateId = Sanitizer::sanitizeText($data->get_param('id'));
        $templateId = sanitize_text_field($data->get_param('template_id'));

        if (empty($templateId)) {
            throw new InvalidArgumentException(
                BackendStrings::getTemplateStrings()['template_not_found']
            );
        }
        $formData = $this->templateService->getFormDataFromTemplate($templateId);
        $params = Sanitizer::sanitizeFormData($formData);

        $form = FormFactory::create($params);

        $formId = $this->formService->createForm($form);
        if (!$formId) {
            throw new QueryExecutionException(
                BackendStrings::getAllFormsStrings()['failed_to_create_form']
            );
        }

        $adminEmail = get_option('admin_email');

        // Create default notification
        $this->notificationService->createDefaultNotificationForForm(
            $formId,
            $params['name'],
            $adminEmail
        );

        // Create default confirmation using the service
        $confirmationId = $this->confirmationService->createDefaultConfirmationForForm($formId);

        // Save form fields and their options using the service
        if (!empty($params['fields'])) {
            $this->fieldService->saveFieldsWithOptions($params['fields'], $formId);
        }

        $form->setId($formId);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => array_merge($form->toArray(), ['confirmationId' => $confirmationId])
        ], 200);
    }
}
