<?php

namespace IvyForms\Routes\Confirmation;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Confirmation\AddConfirmationController;
use IvyForms\Controllers\Confirmation\GetConfirmationsController;
use IvyForms\Controllers\Confirmation\GetConformationController;
use IvyForms\Controllers\Confirmation\UpdateConfirmationController;
use WP_REST_Server;

class Confirmation
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        register_rest_route(
            $routeNamespace,
            '/confirmation/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetConformationController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/confirmations/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetConfirmationsController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/confirmation/add/',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(AddConfirmationController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/confirmation/update/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateConfirmationController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );
    }
}
