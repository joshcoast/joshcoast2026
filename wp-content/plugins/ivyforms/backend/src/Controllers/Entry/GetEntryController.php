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
 * Class GetEntryController
 *
 * @package IvyForms\Controllers\Entry
 */
class GetEntryController extends Controller
{
    private EntryService $entryService;

    public function __construct(EntryService $entryService)
    {
        $this->entryService = $entryService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws NotFoundException
     * @throws InvalidArgumentException|ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Check if the entry ID is provided
        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['entry_id_required']
            );
        }

        $entryId = Sanitizer::sanitizeId($data->get_param('id'));

        $entry = $this->entryService->getEntryManager()->getEntry($entryId);
        $entryArray = $entry->toArray();

        $entryFields = $this->entryService->getEntryFieldManager()->getEntryFields([$entryArray]);

        return new WP_REST_Response([
            'entry' => $entryArray,
            'fields' => $entryFields,
        ], 200);
    }
}
