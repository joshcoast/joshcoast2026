<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Menu\MenuManager;
use IvyForms\Services\Menu\MenuMapper;
use IvyForms\Services\Menu\MenuService;
use IvyForms\Services\Integrations\PluginInstaller;
use IvyForms\Services\Integrations\IntegrationRegistry;
use IvyForms\Services\Integrations\IntegrationService;
use IvyForms\Controllers\Integrations\InstallPluginController;
use IvyForms\Controllers\Integration\GetAllIntegrationsController;
use IvyForms\Controllers\Integration\GetIntegrationController;

return [

    MenuManager::class => function () {
        return new MenuManager(
            new MenuService(),
            new MenuMapper()
        );
    },

    PluginInstaller::class => function () {
        return new PluginInstaller();
    },

    InstallPluginController::class => function ($container) {
        return new InstallPluginController(
            $container->get(PluginInstaller::class)
        );
    },

    IntegrationRegistry::class => function () {
        return new IntegrationRegistry();
    },

    IntegrationService::class => function ($container) {
        return new IntegrationService(
            $container->get(IntegrationRegistry::class)
        );
    },

//    GetAllIntegrationsController::class => function ($container) {
//        return new GetAllIntegrationsController(
//            $container->get(IntegrationRegistry::class)
//        );
//    },
//
//    GetIntegrationController::class => function ($container) {
//        return new GetIntegrationController(
//            $container->get(IntegrationRegistry::class)
//        );
//    },
];
