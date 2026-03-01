<?php

namespace IvyForms\Controllers\Confirmation;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Factory\Confirmation\ConfirmationFactory;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class AddConfirmationController
 *
 * @package IvyForms\Controllers\Confirmation
 */
class AddConfirmationController extends Controller
{
    private ConfirmationService $confirmationService;

    public function __construct(ConfirmationService $confirmationService)
    {
        $this->confirmationService = $confirmationService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Validate request data
        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        // Sanitize the input data
        $params = Sanitizer::sanitizeConfirmationData($data->get_params());

        $confirmation = ConfirmationFactory::create($params);

        $confirmationId = $this->confirmationService->createConfirmation($confirmation);

        if (!$confirmationId) {
            throw new QueryExecutionException(
                BackendStrings::getSettingsFormBuilderStrings()['failed_to_create_confirmation']
            );
        }

        $confirmation->setId($confirmationId);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => $confirmation->toArray()
        ], 200);
    }
}
