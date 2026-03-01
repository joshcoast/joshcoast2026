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
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Field\FieldType;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class UpdateFormController
 */
class UpdateFormController extends Controller
{
    private FormService $formService;
    private FieldService $fieldService;

    public function __construct(
        FormService $formService,
        FieldService $fieldService
    ) {
        $this->formService = $formService;
        $this->fieldService = $fieldService;
    }

    /**
     * @param WP_REST_Request<array<string,mixed>> $data
     * @return WP_REST_Response
     * @throws QueryExecutionException
     * @throws InvalidArgumentException|ValidationException|ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }
        $params = Sanitizer::sanitizeFormData($data->get_params());

        $this->fieldService->validateFieldsType($params['fields']);

        // Update the form
        [$formId, $form] = $this->formService->updateFormOrFail($params);
        if (!$formId) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['failed_to_update_form'] . '.');
        }

        // Get all existing field IDs for the form
        $existingFieldIds = $this->fieldService->collectExistingFieldIds($formId);
        $submittedFieldIds = [];

        // Update/add all submitted fields (including options and parent/child logic)
        if (!empty($params['fields'])) {
            $submittedFieldIds = $this->fieldService->updateFieldsWithOptions(
                $params['fields'],
                $formId
            );
        }

        // Delete any fields (and their options) that are no longer present
        if (!empty($existingFieldIds)) {
            $idsToDelete = array_diff($existingFieldIds, $submittedFieldIds);
            if (!empty($idsToDelete)) {
                $this->fieldService->deleteFields($idsToDelete);
            }
        }

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data' => $form->toArray()
        ], 200);
    }
}
