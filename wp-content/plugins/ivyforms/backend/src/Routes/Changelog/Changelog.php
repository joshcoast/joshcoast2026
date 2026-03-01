<?php

namespace IvyForms\Routes\Changelog;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Controllers\Changelog\GetChangelogController;
use WP_REST_Server;

class Changelog
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container, string $routeNamespace): void
    {
        register_rest_route(
            $routeNamespace,
            '/changelog',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => $container->get(GetChangelogController::class),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ]
        );
    }
}
