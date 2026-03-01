<?php

/**
 * Plugin Loader.
 *
 * @package IvyForms
 * @since 0.1.0
 */

namespace IvyForms\Plugin;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Exception;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Routes\Routes;
use IvyForms\Services\InstallActions\ActivationDatabaseHook;
use IvyForms\Services\InstallActions\ActivationMultisite;
use IvyForms\Services\InstallActions\ActivationNewSiteMultisite;
use IvyForms\Services\InstallActions\ActivationSettingsHook;
use IvyForms\Services\InstallActions\DeleteDatabaseHook;
use IvyForms\Services\InstallActions\DeletionMultisite;
use IvyForms\Services\Integrations\Gutenberg\Blocks\GutenbergBlock;
use IvyForms\Services\Integrations\IntegrationService;
use IvyForms\Services\Menu\MenuManager;
use IvyForms\Services\Preview\PreviewService;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Shortcode\ShortcodeService;
use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\ContainerBuilder;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;

/**
 * Plugin
 */
class Plugin
{
    /**
     * Instance
     */
    private static ?object $instance = null;

    private ContainerBuilder $builder;
    /**
     * Container
     */
    public ?Container $container = null;

    /**
     * Settings Service
     */
    private ?SettingsService $settingsService = null;

    /**
     * Load plugin
     *
     * @return object initialized object of class.
     * @throws Exception
     */
    public static function getInstance(): ?object
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @throws Exception
     * @since 0.1.0
     */
    public function __construct()
    {
        $this->builder = new ContainerBuilder();
        $this->builder->addDefinitions(IVYFORMS_PATH . '/backend/src/Config/repository.php');
        $this->builder->addDefinitions(IVYFORMS_PATH . '/backend/src/Config/services.php');

        /**
         * Allow to hook into container before build
         * @since 0.1.0
         *
         * Arguments:
         * - ContainerBuilder $builder The IvyForms DI container builder (base)
         */
        do_action('ivyforms/boot/extend_container_builder', $this->builder);

        $this->container = $this->builder->build();

        $this->settingsService = $this->container->get(SettingsService::class);

        /**
         * The code that runs during plugin activation
         */
        register_activation_hook(
            IVYFORMS_FILE,
            [ self::class, 'activation' ]
        );
        register_activation_hook(
            IVYFORMS_FILE,
            [ self::class, 'activationSettings' ]
        );

        /**
         * The code that runs during plugin deactivation
         */
        register_deactivation_hook(
            IVYFORMS_FILE,
            [ self::class, 'deactivation' ]
        );

        /**
         * The code that runs when plugin is deleted
         */
        register_uninstall_hook(
            IVYFORMS_FILE,
            [ self::class, 'deletion']
        );

        /** Init load */
        add_action('plugins_loaded', [ $this, 'init' ]);

        /** Load translations at init action (WordPress 6.7+ requirement) */
        add_action('init', [ $this, 'loadTranslations' ]);

        /** Register API calls */
        add_action('rest_api_init', [$this, 'registerRoutes']);

        /** Isolate API calls */
        add_action('wp_ajax_ivyforms_api', [ $this, 'registerRoutes' ]);
        add_action('wp_ajax_nopriv_ivyforms_api', [ $this, 'registerRoutes' ]);

        /** Activation hook for new site on multisite setup */
        add_action('wpmu_new_blog', [ $this, 'initNewSite' ]);

        add_filter('wp_script_attributes', ['IvyForms\Services\Shortcode\ShortcodeService', 'addScriptAttribute']);

        // Register admin hooks via AdminHooks class
        $adminHooks = new AdminHooks($this->settingsService);
        $adminHooks->register();
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function registerRoutes(): void
    {
        // Register REST API routes after the container is built
        Routes::registerRoutes($this->container);
    }


    /**
     * @param bool $networkWide
     * @throws InvalidArgumentException
     */
    public static function activation(bool $networkWide): void
    {
        //Network activation
        if ($networkWide && function_exists('is_multisite') && is_multisite()) {
            ActivationMultisite::init();
        }

        ActivationDatabaseHook::init();
    }

    /**
     * @param int $siteId
     * @throws InvalidArgumentException
     */
    public static function initNewSite(int $siteId): void
    {
        ActivationNewSiteMultisite::init($siteId);
    }

    /**
     * @throws Exception
     */
    public static function activationSettings(): void
    {
        ActivationSettingsHook::init();
    }

    /**
     * Deactivation function when plugin is deactivated
     * Deletes all IvyForms data if delete_on_uninstall is enabled
     * @throws InvalidArgumentException
     */
    public static function deactivation(): void
    {
        $settingsService = new SettingsService();

        if ($settingsService->getSetting('general', 'delete_on_uninstall')) {
            //Network deletion
            if (
                function_exists('is_multisite') &&
                is_multisite()
            ) {
                DeletionMultisite::delete();
            }

            DeleteDatabaseHook::delete();
        }
    }

    /**
     * Uninstall function when plugin is deleted
     * @throws InvalidArgumentException
     */
    public static function deletion(): void
    {
        $settingsService = new SettingsService();

        if ($settingsService->getSetting('general', 'delete_on_uninstall')) {
            //Network deletion
            if (
                function_exists('is_multisite') &&
                is_multisite()
            ) {
                DeletionMultisite::delete();
            }

            DeleteDatabaseHook::delete();
        }
    }

    /**
     * Init
     * @return void
     * @throws ValidationException|InvalidArgumentException
     */
    public function init(): void
    {

        new PreviewService();

        // Initialize integration registry system
        try {
            /** @var IntegrationService $integrationService */
            $integrationService = $this->container->get(IntegrationService::class);
            $integrationService->init();
        } catch (DependencyException | NotFoundException $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Integration service error: ' . $e->getMessage());
            }
        }

        try {
            /** @var MenuManager $menuManager*/
            $menuManager = $this->container->get(MenuManager::class);
            // Register the admin menus and submenus using the 'admin_menu' hook
            $menuManager->registerMenus();
        } catch (DependencyException | NotFoundException $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Menu error: ' . $e->getMessage());
            }
        }

        if (!is_admin()) {
            add_shortcode('ivyforms', function ($atts = []) {
                return ShortcodeService::shortcodeHandler($atts);
            });
        }

        // Register Gutenberg block
        if (function_exists('register_block_type')) {
            add_action('init', [GutenbergBlock::class, 'register']);
        }
    }

    /**
     * Load plugin translations
     * Called on 'init' action to comply with WordPress 6.7+ requirements
     *
     * @return void
     */
    public function loadTranslations(): void
    {
        load_plugin_textdomain(
            'ivyforms',
            false,
            dirname(plugin_basename(IVYFORMS_FILE)) . '/languages/' . get_locale() . '/'
        );
    }
}
