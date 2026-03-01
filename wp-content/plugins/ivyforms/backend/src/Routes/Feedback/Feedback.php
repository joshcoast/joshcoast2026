<?php

namespace IvyForms\Routes\Feedback;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Deactivation\SubmitDeactivationFeedbackController;
use WP_REST_Server;

/**
 * Class Feedback
 *
 * @package IvyForms\Routes\Feedback
 */
class Feedback
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        // Register deactivation feedback endpoint
        register_rest_route(
            $routeNamespace,
            '/deactivation-feedback',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(SubmitDeactivationFeedbackController::class),
                'permission_callback' => function () {
                    // Require admin capabilities for deactivation feedback
                    return current_user_can('manage_options');
                },
                'args' => [
                    'api_version' => [
                        'required' => false,
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ],
                    'feedback_key' => [
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_string($param) && !empty($param);
                        }
                    ],
                    'feedback' => [
                        'required' => false,
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ]
                ]
            ]
        );
    }
}
