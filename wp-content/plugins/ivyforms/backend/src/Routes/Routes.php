<?php

/**
 * @copyright © Melograno Venture Studio. All rights.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Routes;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Routes\Confirmation\Confirmation;
use IvyForms\Routes\Entry\Entry;
use IvyForms\Routes\Feedback\Feedback;
use IvyForms\Routes\Form\Form;
use IvyForms\Routes\Notification\Notification;
use IvyForms\Routes\Template\Template;
use IvyForms\Routes\Settings\Settings;
use IvyForms\Routes\Changelog\Changelog;
use IvyForms\Routes\Integrations\Integrations;

/**
 * Class Routes
 *
 * @package IvyForms\Routes
 */
class Routes
{
    public static string $routeNamespace = 'ivyforms/v1';

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container): void
    {
        Form::registerRoutes($container, self::$routeNamespace);
        Notification::registerRoutes($container, self::$routeNamespace);
        Confirmation::registerRoutes($container, self::$routeNamespace);
        Entry::registerRoutes($container, self::$routeNamespace);
        Template::registerRoutes($container, self::$routeNamespace);
        Settings::registerRoutes($container, self::$routeNamespace);
        Changelog::registerRoutes($container, self::$routeNamespace);
        Integrations::registerRoutes($container, self::$routeNamespace);
        Feedback::registerRoutes($container, self::$routeNamespace);

        /**
         * Allow external plugins/components to register additional routes.
         * @since 0.1.0
         *
         * Args:
         *  - Container $container The IvyForms DI container (base)
         *  - string    $namespace Current REST namespace string (e.g. ivyforms/v1)
         */
        do_action('ivyforms/rest/register_additional_routes', $container, self::$routeNamespace);
    }
}
