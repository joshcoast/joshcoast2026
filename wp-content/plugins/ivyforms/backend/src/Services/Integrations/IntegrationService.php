<?php

namespace IvyForms\Services\Integrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;

/**
 * Integration Service
 *
 * Initializes the integration registry and registers default Lite integrations
 *
 * @since 1.0.0
 */
class IntegrationService
{
    private IntegrationRegistry $registry;

    public function __construct(IntegrationRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Initialize integrations
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function init(): void
    {
        // Register Lite integrations
        $this->registerLiteIntegrations();

        /**
         * Allow Pro/addons to register their integrations
         *
         * @since 1.0.0
         *
         * @param IntegrationRegistry $registry The integration registry instance
         */
        do_action('ivyforms/integrations/register', $this->registry);
    }

    /**
     * Register default Lite integrations
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function registerLiteIntegrations(): void
    {
        // wpDataTables Integration (Lite)
        $this->registry->register('wpdatatables', [
            'label' => 'wpdatatables_label',
            'component' => 'WpDataTablesIntegrationSettings',
            'icon' => 'wpdatatables',
            'description' => 'wpdatatables_description',
            'category' => 'Data Management',
            'requiresAuth' => false,
            'plan' => 'lite',
            'settingsSchema' => [
                'enabled' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ]);

        // Register Pro integrations as placeholders
        // These will be shown with "Upgrade" button when Pro is not installed
        // When Pro is installed, these will be overwritten by Pro's registrar

        // MailChimp Integration (Pro - Essentials)
        $this->registry->register('mailchimp', [
            'label' => 'mailchimp_label',
            'component' => 'MailChimpIntegrationSettings',
            'icon' => 'mailchimp',
            'description' => 'mailchimp_description',
            'category' => 'Email Marketing',
            'requiresAuth' => true,
            'plan' => 'essentials',
        ]);

        // Zapier Integration (Pro - Growth)
//        $this->registry->register('zapier', [
//            'label' => 'zapier_label',
//            'component' => 'ZapierIntegrationSettings',
//            'icon' => 'zapier',
//            'description' => 'zapier_description',
//            'category' => 'Automation',
//            'requiresAuth' => false,
//            'plan' => 'growth',
//        ]);

        // Salesforce Integration (Pro - Agency)
//        $this->registry->register('salesforce', [
//            'label' => 'salesforce_label',
//            'component' => 'SalesforceIntegrationSettings',
//            'icon' => 'salesforce',
//            'description' => 'salesforce_description',
//            'category' => 'CRM',
//            'requiresAuth' => true,
//            'plan' => 'agency',
//        ]);
    }
}
