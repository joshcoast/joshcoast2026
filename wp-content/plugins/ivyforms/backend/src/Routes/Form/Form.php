<?php

namespace IvyForms\Routes\Form;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Form\AddFormController;
use IvyForms\Controllers\Form\DeleteFormController;
use IvyForms\Controllers\Form\FormSubmissionController;
use IvyForms\Controllers\Form\DuplicateFormController;
use IvyForms\Controllers\Form\GetFormController;
use IvyForms\Controllers\Form\GetFormsController;
use IvyForms\Controllers\Form\SearchFormsController;
use IvyForms\Controllers\Form\UpdateFormController;
use IvyForms\Controllers\Form\DeleteFormsController;
use IvyForms\Controllers\Form\UpdateFormStarredController;
use IvyForms\Controllers\Form\UpdateFormStatusController;
use IvyForms\Controllers\Form\CheckDuplicateController;
use WP_REST_Server;

class Form
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        self::registerFormCrudRoutes($container, $routeNamespace);
        self::registerFormManagementRoutes($container, $routeNamespace);
        self::registerFormSubmissionRoutes($container, $routeNamespace);
    }

    /**
     * Register CRUD routes for forms (Create, Read, Update, Delete)
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function registerFormCrudRoutes(Container $container, string $routeNamespace): void
    {
        // Get single form
        register_rest_route(
            $routeNamespace,
            '/form/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetFormController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        // Get all forms
        register_rest_route(
            $routeNamespace,
            '/form',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetFormsController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
             ]
        );

        // Add new form
        register_rest_route(
            $routeNamespace,
            '/form/add/',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(AddFormController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
             ]
        );

        // Update form
        register_rest_route(
            $routeNamespace,
            '/form/update/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateFormController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
             ]
        );

        // Delete single form
        register_rest_route(
            $routeNamespace,
            '/form/delete/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DeleteFormController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
             ]
        );

        // Delete multiple forms
        register_rest_route(
            $routeNamespace,
            '/forms/delete',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DeleteFormsController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );
    }

    /**
     * Register form management routes (search, status updates, etc.)
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function registerFormManagementRoutes(Container $container, string $routeNamespace): void
    {
        // Search forms
        register_rest_route(
            $routeNamespace,
            '/forms/search',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(SearchFormsController::class),
                'args' => [
                    'page' => ['type' => 'integer'],
                    'perPage' => ['type' => 'integer'],
                    'search' => ['type' => 'string'],
                    'orderBy' => ['type' => 'string'],
                    'order' => ['type' => 'string'],
                    'shouldGetCount' => ['type' => 'boolean'],
                ],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );

        // Update form starred status
        register_rest_route(
            $routeNamespace,
            '/form/update/starred/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateFormStarredController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
             ]
        );

        // Update form status
        register_rest_route(
            $routeNamespace,
            '/form/update/status/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(UpdateFormStatusController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
             ]
        );

        // Duplicate form
        register_rest_route(
            $routeNamespace,
            '/form/duplicate/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(DuplicateFormController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );
    }

    /**
     * Register form submission routes (public-facing)
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function registerFormSubmissionRoutes(Container $container, string $routeNamespace): void
    {
        // Form submission (public route)
        register_rest_route(
            $routeNamespace,
            '/form/submission/',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $container->get(FormSubmissionController::class),
                // TODO - Add IvyForms capability - Public use
                'permission_callback' => '__return_true'
            ]
        );
    }
}
