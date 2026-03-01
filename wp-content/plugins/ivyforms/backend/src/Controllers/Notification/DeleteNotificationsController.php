<?php

namespace IvyForms\Controllers\Notification;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Controllers\Controller;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class DeleteNotificationsController
 *
 * @package IvyForms\Controllers\Notification
 */
class DeleteNotificationsController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));
        $notificationIds =  Sanitizer::sanitizeIds($data->get_param('ids'));

        if (empty($notificationIds)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['notification_ids_required']
            );
        }

        $this->notificationService->deleteNotifications($notificationIds);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => []
        ], 200);
    }
}
