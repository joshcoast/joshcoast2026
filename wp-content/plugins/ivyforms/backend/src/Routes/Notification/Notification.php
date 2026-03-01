<?php

namespace IvyForms\Routes\Notification;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Notification\AddNotificationController;
use IvyForms\Controllers\Notification\DeleteNotificationController;
use IvyForms\Controllers\Notification\DuplicateNotificationController;
use IvyForms\Controllers\Notification\GetNotificationController;
use IvyForms\Controllers\Notification\GetNotificationsController;
use IvyForms\Controllers\Notification\SearchNotificationsController;
use IvyForms\Controllers\Notification\UpdateNotificationController;
use WP_REST_Server;

class Notification
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        register_rest_route(
            $routeNamespace,
            '/notification/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetNotificationController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/notifications/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetNotificationsController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/notification/add/',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(AddNotificationController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/notification/update/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateNotificationController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/notification/delete/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DeleteNotificationController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );
        register_rest_route(
            $routeNamespace,
            '/notification/duplicate/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DuplicateNotificationController::class),
                // TODO - Add IvyForms capability - Admin only for now
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/notifications/search',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(SearchNotificationsController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );
    }
}
