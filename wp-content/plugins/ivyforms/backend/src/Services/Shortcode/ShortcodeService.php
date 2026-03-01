<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Shortcode;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Factory\Field\FieldFactory;
use IvyForms\Routes\Routes;
use IvyForms\Services\API\IvyFormsAPI;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Translations\FrontendStrings;
use IvyForms\Factory\Security\SecurityServiceFactory;
use IvyForms\Services\Security\SecurityService;
use IvyForms\Repository\FieldOptions\FieldOptionsRepository;

/**
 * Class ShortcodeService
 *
 * @package IvyForms\Services\Shortcode
 */
class ShortcodeService
{
    public static int $counter = 0;
    /**
     * Array keyed by unique render counters (string) -> payload array
     *
     * @var array<string, array<string, mixed>>
     */
    public static array $formList = [];

    /**
     * Shortcode handler
     *
     * @param mixed[] $atts
     *
     * @return string
     * @throws ValidationException
     */
    public static function shortcodeHandler(array $atts): string
    {
        $atts = shortcode_atts(
            [
                'id'  => '0',
                'show_title' => null,
                'show_description' => null,
            ],
            $atts
        );

        if (empty($atts['id'])) {
            return '';
        }

        $formId = (int)$atts['id'];
        $form = IvyFormsAPI::getForm($formId);

        // Check if the form is published
        if (is_wp_error($form) || !($form->isPublished())) {
            return '';
        }

        // Generate a stable unique identifier for this render
        $counter = $formId . '_' . uniqid();

        // Build overrides array for show_title and show_description
        $overrides = [];
        if ($atts['show_title'] !== null) {
            // Convert string 'true'/'false' or '1'/'0' to boolean
            $overrides['showTitle'] = filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN);
        }
        if ($atts['show_description'] !== null) {
            $overrides['showDescription'] = filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN);
        }

        self::getFormList($formId, $counter, $overrides);

        self::enqueueScripts();

        ob_start();
        include IVYFORMS_PATH . '/view/frontend/view.php';
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * Get form list
     *
     * @param int $formId
     * @param string $counter Unique counter identifier for this render
     * @param array<string, mixed> $overrides Optional overrides for form settings (e.g., showTitle, showDescription)
     *
     * @return void
     */
    public static function getFormList(int $formId, string $counter, array $overrides = [])
    {
        $form = IvyFormsAPI::getForm($formId);

        $fields = IvyFormsAPI::getFields($form->getId());

        $confirmations = IvyFormsAPI::getConfirmations($form->getId());

        $fieldsMap = self::prepareFieldsMap($fields);

        $confirmationsMap = [];

        foreach ($confirmations as $confirmation) {
            $confirmationsMap[] = is_object($confirmation) && method_exists($confirmation, 'toArray')
                ? $confirmation->toArray()
                : $confirmation;
        }

        $form->setFields($fieldsMap);

        $formArr = $form->toArray();

        // Apply overrides for showTitle and showDescription (from Gutenberg block or shortcode)
        if (array_key_exists('showTitle', $overrides)) {
            $formArr['showTitle'] = $overrides['showTitle'];
        }
        if (array_key_exists('showDescription', $overrides)) {
            $formArr['showDescription'] = $overrides['showDescription'];
        }

        self::$formList[$counter] = [
            'form'    => $formArr,
            'fields'  => $fieldsMap,
            'id'      => $form->getId(),
            'counter' => $counter,
            'nonce'   =>  wp_create_nonce('ivyformsFrontSubmissionNonce_' .  $form->getId()),
            'confirmations' => $confirmationsMap,
        ];
    }

    /**
     * Prepare fields map for a form
     *
     * @param array<int, object> $fields
     * @return array<int, array<string, mixed>>
     */
    private static function prepareFieldsMap(array $fields): array
    {
        $fieldsMap = [];
        foreach ($fields as $field) {
            $fieldArr = $field->toArray();
            if (
                isset($fieldArr['type']) && in_array(
                    $fieldArr['type'],
                    ['radio', 'checkbox', 'select', 'multi-select', 'rating'],
                    true
                )
            ) {
                $fieldOptions = IvyFormsAPI::getFieldOptions($fieldArr['id']);
                $fieldArr['fieldOptions'] = [];
                foreach ($fieldOptions as $option) {
                    if (is_object($option) && method_exists($option, 'toArray')) {
                        $fieldArr['fieldOptions'][] = $option->toArray();
                    }
                }
            }
            $fieldsMap[] = $fieldArr;
        }
        return $fieldsMap;
    }

    /**
     * Enqueue handler
     * @throws ValidationException
     */
    public static function enqueueScripts(): void
    {
        $scriptId = IVYFORMS_DEV ? 'ivyforms_scripts_dev_vite' : 'ivyforms_script_index';   // @phpstan-ignore-line
        $scriptSrc = IVYFORMS_DEV ? // @phpstan-ignore-line
            'http://localhost:5173/src/assets/js/public/public.ts' :
            IVYFORMS_URL . 'frontend/dist/public.js';

        $styleSrc = IVYFORMS_DEV ? '' : IVYFORMS_URL . 'frontend/dist/index.css'; // @phpstan-ignore-line
        $stylePublicSrc = IVYFORMS_DEV ? '' : IVYFORMS_URL . 'frontend/dist/public.css'; // @phpstan-ignore-line
        $scriptVersion = IVYFORMS_DEV ? null : IVYFORMS_VERSION;// @phpstan-ignore-line

        wp_enqueue_script(
            $scriptId,
            $scriptSrc,
            [],
            $scriptVersion,
            true
        );

        // TODO: Improve this with merging styles
        if ($styleSrc) { // @phpstan-ignore-line
            wp_enqueue_style('ivyforms_style_index', $styleSrc, [], $scriptVersion);
            wp_enqueue_style('ivyforms_style_public', $stylePublicSrc, [], $scriptVersion);

            // Use helper for inline styles
            wp_add_inline_style(
                'ivyforms_style_index',
                InlineStyleHelper::getFrontendInlineCss()
            );
        }
        self::enqueueRecaptchaScript($scriptId);

        wp_localize_script(
            $scriptId,
            'wpIvyFormDataList',
            self::$formList
        );

        $frontendLabels = array_merge(
            FrontendStrings::getFormRenderStrings()
        );

        /**
         * Filter frontend labels before localizing
         * Allows Pro version to merge additional strings
         *
         * @since 0.1.0
         * @param array $frontendLabels Array of frontend label strings
         */
        $frontendLabels = apply_filters('ivyforms/shortcode/frontend_labels', $frontendLabels);

        wp_localize_script($scriptId, 'wpIvyLabels', $frontendLabels);

        wp_localize_script(
            $scriptId,
            'wpIvyUrls',
            [
                'pluginURL' => IVYFORMS_URL,
                'siteURL'   => esc_url_raw(site_url()),
            ]
        );

        wp_localize_script(
            $scriptId,
            'wpIvyApiSettings',
            [
                'root' => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
                'namespace' => Routes::$routeNamespace,
            ]
        );
        wp_localize_script(
            $scriptId,
            'wpIvyDateFormat',
            [
                'dateFormat'     => get_option('date_format'),
                'timeFormat'     => get_option('time_format'),
                'dateTimeFormat' => get_option('date_format') . ' ' . get_option('time_format'),
                'locale'         => get_locale(),
            ]
        );

        /**
         * Allow external plugins/components to enqueue additional scripts/styles.
         * @since 0.1.0
         *
         * Args:
         *  - string $scriptId The main script handle for the frontend (public) script
         */
        do_action('ivyforms/shortcode/enqueue_scripts', $scriptId);
    }

    /**
     * Add script attribute for module type
     *
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public static function addScriptAttribute(array $attributes): array
    {
        $scriptArray = [
            'ivyforms_scripts_dev_main-js',
            'ivyforms_scripts_dev_vite-js',
            'ivyforms_script_index-js',
            'ivyforms_scripts_public-js',
        ];

        if (isset($attributes['id']) && in_array($attributes['id'], $scriptArray, true)) {
            $attributes['type'] = 'module';
        }

        return $attributes;
    }

    /**
     * Enqueue CAPTCHA scripts (reCAPTCHA, Turnstile, etc.) if configured
     *
     * @param string $scriptId
     * @throws ValidationException
     */
    public static function enqueueRecaptchaScript(string $scriptId): void
    {
        $securityService = self::getSecurityService();
        $formFields = self::getAllFormFields();

        // Get security configuration using our enhanced service
        $securityConfig = $securityService->getFrontendSecurityConfig($formFields);

        // Enqueue scripts if CAPTCHA is configured and has fields
        if ($securityConfig['captcha']['enabled'] ?? false) {
            if (isset($securityConfig['captcha']['recaptcha'])) {
                self::enqueueGoogleRecaptchaScript($securityConfig['captcha']);
            }
            if (isset($securityConfig['captcha']['turnstile'])) {
                self::enqueueTurnstileScript($securityConfig['captcha']);
            }
            if (isset($securityConfig['captcha']['hcaptcha'])) {
                self::enqueueHCaptchaScript($securityConfig['captcha']);
            }
        }

        // Localize reCAPTCHA config
        wp_localize_script(
            $scriptId,
            'wpIvyRecaptchaConfig',
            $securityConfig['captcha'] ?? []
        );

        // Localize Turnstile config
        wp_localize_script(
            $scriptId,
            'wpIvyTurnstileConfig',
            $securityConfig['captcha'] ?? []
        );

        // Localize hCaptcha config
        wp_localize_script(
            $scriptId,
            'wpIvyHCaptchaConfig',
            $securityConfig['captcha'] ?? []
        );
    }

    /**
     * Get security service instance
     *
     * @return SecurityService
     */
    private static function getSecurityService(): SecurityService
    {
        $settingsService = new SettingsService();
        $securityFactory = new SecurityServiceFactory($settingsService);
        return new SecurityService($securityFactory);
    }

    /**
     * Get all form fields from currently loaded forms
     *
     * @return array<object> Array of form field objects
     * @throws ValidationException
     */
    private static function getAllFormFields(): array
    {
        $allFields = [];

        foreach (self::$formList as $formData) {
            foreach ($formData['fields'] as $fieldData) {
                // Create a simple object to match the expected interface
                $field = FieldFactory::create($fieldData);

                $allFields[] = $field;
            }
        }

        return $allFields;
    }

    /**
     * Enqueue Google reCAPTCHA script using security service
     *
     * @param array<string, mixed> $captchaConfig
     */
    private static function enqueueGoogleRecaptchaScript(array $captchaConfig): void
    {
        if (!isset($captchaConfig['recaptcha'])) {
            return;
        }

        $recaptchaConfig = $captchaConfig['recaptcha'];
        $scriptUrl = $recaptchaConfig['scriptUrl'] ?? '';

        if (empty($scriptUrl)) {
            return;
        }

        wp_enqueue_script(
            'google-recaptcha',
            $scriptUrl,
            [],
            null, // No version for external script
            true // Load in footer
        );
    }

    /**
     * Enqueue Cloudflare Turnstile script
     *
     * @param array<string, mixed> $captchaConfig
     */
    private static function enqueueTurnstileScript(array $captchaConfig): void
    {
        if (!isset($captchaConfig['turnstile'])) {
            return;
        }

        $turnstileConfig = $captchaConfig['turnstile'];
        $scriptUrl = $turnstileConfig['scriptUrl'] ?? '';

        if (empty($scriptUrl)) {
            return;
        }

        wp_enqueue_script(
            'cloudflare-turnstile',
            $scriptUrl,
            [],
            null, // No version for external script
            true // Load in footer
        );
    }

    /**
     * Enqueue hCaptcha script
     *
     * @param array<string, mixed> $captchaConfig
     */
    private static function enqueueHCaptchaScript(array $captchaConfig): void
    {
        if (!isset($captchaConfig['hcaptcha'])) {
            return;
        }

        $hcaptchaConfig = $captchaConfig['hcaptcha'];
        $scriptUrl = $hcaptchaConfig['scriptUrl'] ?? '';

        if (empty($scriptUrl)) {
            return;
        }

        wp_enqueue_script(
            'hcaptcha',
            $scriptUrl,
            [],
            null, // No version for external script
            true // Load in footer
        );
    }
}
