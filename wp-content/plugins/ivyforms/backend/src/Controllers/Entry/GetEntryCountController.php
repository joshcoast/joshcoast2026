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
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class GetEntryCountController
 *
 * @package IvyForms\Controllers\Entry
 */
class GetEntryCountController extends Controller
{
    private EntryService $entryService;

    public function __construct(EntryService $entryService)
    {
        $this->entryService = $entryService;
    }

    /**
     * Fetches the count of entries for a given form ID or multiple form IDs.
     *
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response The response containing the count(s) of entries.
     *
     * @throws InvalidArgumentException|ForbiddenException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $params = $data->get_params();
        if (empty($params)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['form_id_required']
            );
        }

        $formIds = Sanitizer::sanitizeIds($data->get_param('id')) ;
        $counts = $this->entryService->getEntryManager()->getEntryCountByFormIds($formIds);
        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => ['counts' => $counts],
        ], 200);
    }
}
