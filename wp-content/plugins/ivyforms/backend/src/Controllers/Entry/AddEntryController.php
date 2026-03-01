<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Entry;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Factory\Entry\EntryFactory;
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\Field\FieldService;
use WP_REST_Request;
use WP_REST_Response;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class AddEntryController
 *
 * @package IvyForms\Controllers\Entry
 */
class AddEntryController extends Controller
{
    private EntryService $entryService;
    private FieldService $fieldService;

    public function __construct(EntryService $entryService, FieldService $fieldService)
    {
        $this->entryService = $entryService;
        $this->fieldService = $fieldService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Check if the entry data is provided
        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        // Sanitize the input data
        $params = Sanitizer::sanitizeEntryData($data->get_params());

        // Extract formId and submission data
        $formId = $params['formId'] ?? null;
        if (!$formId) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_form_id']
            );
        }
        $formFields = $this->fieldService->getAllFields($formId);
        $submissionData = $params['fields'] ?? [];

        // Create Entry entity
        $entry = EntryFactory::create($params);
        $entryId = $this->entryService->getEntryManager()->createEntry($entry);

        // Add EntryFields using the service method
        $this->entryService->getEntryFieldManager()->addEntryFields($formFields, $entryId, $submissionData);

        $entry->setId($entryId);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => $entry->toArray(),
        ], 200);
    }
}
