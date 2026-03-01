<?php

namespace IvyForms\Routes\Entry;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Entry\AddEntryController;
use IvyForms\Controllers\Entry\DeleteEntriesController;
use IvyForms\Controllers\Entry\DeleteEntryController;
use IvyForms\Controllers\Entry\GetEntryController;
use IvyForms\Controllers\Entry\GetEntryCountController;
use IvyForms\Controllers\Entry\SearchEntriesController;
use IvyForms\Controllers\Entry\UpdateEntryStarredController;
use IvyForms\Controllers\Entry\UpdateEntryStatusController;
use WP_REST_Server;

class Entry
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        // TODO - Add IvyForms capability - Admin only for now
        register_rest_route(
            $routeNamespace,
            '/entry/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetEntryController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entry/add/',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(AddEntryController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
             ]
        );

        register_rest_route(
            $routeNamespace,
            '/entries/search',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(SearchEntriesController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entries/delete',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DeleteEntriesController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );
        register_rest_route(
            $routeNamespace,
            '/entry/delete/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DeleteEntryController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entry/update/starred/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateEntryStarredController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entry/update/status/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateEntryStatusController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        register_rest_route(
            $routeNamespace,
            '/entries/count',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetEntryCountController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );
    }
}
