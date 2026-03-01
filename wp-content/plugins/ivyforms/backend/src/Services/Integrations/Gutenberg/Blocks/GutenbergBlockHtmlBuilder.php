<?php

/**
 * GutenbergBlockHtmlBuilder class for building block HTML output
 *
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Integrations\Gutenberg\Blocks;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Shortcode\ShortcodeService;

/**
 * Class GutenbergBlockHtmlBuilder
 *
 * Handles HTML and style building for the IvyForms Gutenberg block
 *
 * @package IvyForms\Services\Blocks
 */
class GutenbergBlockHtmlBuilder
{
    /**
     * Build wrapper attributes array
     *
     * @param array<string, mixed> $attributes
     * @param string $blockInstanceId
     * @return array<string, string>
     */
    public static function buildWrapperAttributes(array $attributes, string $blockInstanceId): array
    {
        $cssClasses = self::buildCssClasses($attributes);

        return [
            'id' => $blockInstanceId,
            'class' => implode(' ', $cssClasses),
            'data-show-title' => !empty($attributes['showTitle']) ? 'true' : 'false',
            'data-show-description' => !empty($attributes['showDescription']) ? 'true' : 'false',
        ];
    }

    /**
     * Build CSS classes array from attributes
     *
     * @param array<string, mixed> $attributes
     * @return array<int, string>
     */
    private static function buildCssClasses(array $attributes): array
    {
        $cssClasses = ['ivyforms-gutenberg-block'];

        if (!empty($attributes['className'])) {
            $cssClasses[] = sanitize_text_field($attributes['className']);
        }

        if (!empty($attributes['customCssClass'])) {
            $cssClasses[] = sanitize_text_field($attributes['customCssClass']);
        }

        return $cssClasses;
    }

    /**
     * Convert wrapper attributes array to HTML attribute string
     *
     * @param array<string, string> $wrapperAttributes
     * @return string
     */
    public static function attributesToString(array $wrapperAttributes): string
    {
        $parts = [];
        foreach ($wrapperAttributes as $key => $value) {
            $parts[] = sprintf('%s="%s"', esc_attr($key), esc_attr($value));
        }
        return ' ' . implode(' ', $parts);
    }

    /**
     * Build custom styles for hiding title/description
     *
     * @param array<string, mixed> $attributes
     * @param string $blockInstanceId
     * @return string
     */
    public static function buildCustomStyles(array $attributes, string $blockInstanceId): string
    {
        $rules = self::buildHideRules($attributes, $blockInstanceId);
        return $rules !== '' ? '<style>' . $rules . '</style>' : '';
    }

    /**
     * Build CSS rules for hiding elements
     *
     * @param array<string, mixed> $attributes
     * @param string $blockInstanceId
     * @return string
     */
    private static function buildHideRules(array $attributes, string $blockInstanceId): string
    {
        $selector = '#' . esc_attr($blockInstanceId);
        $rules = [];

        if (isset($attributes['showTitle']) && $attributes['showTitle'] === false) {
            $rules[] = $selector . ' .ivyforms-form-title { display: none !important; }';
        }

        if (isset($attributes['showDescription']) && $attributes['showDescription'] === false) {
            $rules[] = $selector . ' .ivyforms-form-description { display: none !important; }';
        }

        return implode('', $rules);
    }

    /**
     * Generate embedded form data JSON for data attribute
     *
     * @param int $formId
     * @return string
     */
    public static function generateFormDataJson(int $formId): string
    {
        if (empty(ShortcodeService::$formList)) {
            return '';
        }

        $formDataMap = self::filterFormListById($formId);
        if (empty($formDataMap)) {
            return '';
        }

        $json = wp_json_encode($formDataMap);
        return $json !== false ? $json : '';
    }

    /**
     * Generate embedded JSON script blocks
     *
     * @param int $formId
     * @return string
     */
    public static function generateJsonScripts(int $formId): string
    {
        if (empty(ShortcodeService::$formList)) {
            return '';
        }

        $scripts = '';
        foreach (ShortcodeService::$formList as $counterKey => $payload) {
            if (isset($payload['id']) && (int)$payload['id'] === (int)$formId) {
                $scripts .= self::buildScriptTag($counterKey, $payload);
            }
        }

        return $scripts;
    }

    /**
     * Build a single JSON script tag
     *
     * @param string $counterKey
     * @param array<string, mixed> $payload
     * @return string
     */
    private static function buildScriptTag(string $counterKey, array $payload): string
    {
        $json = wp_json_encode($payload);
        $json = str_replace('</script>', '<\/script>', $json);
        return '<script type="application/json" class="ivyforms-ssr-data"'
            . ' data-ivyforms-counter="' . esc_attr($counterKey) . '">'
            . $json . '</script>';
    }

    /**
     * Filter form list by form ID
     *
     * @param int $formId
     * @return array<string, mixed>
     */
    private static function filterFormListById(int $formId): array
    {
        $formDataMap = [];
        foreach (ShortcodeService::$formList as $counterKey => $payload) {
            if (isset($payload['id']) && (int)$payload['id'] === (int)$formId) {
                $formDataMap[$counterKey] = $payload;
            }
        }
        return $formDataMap;
    }

    /**
     * Build the final block HTML
     *
     * @param string $wrapperAttrsString
     * @param string $formOutput
     * @param string $customStyles
     * @param string $embeddedScripts
     * @return string
     */
    public static function buildBlockHtml(
        string $wrapperAttrsString,
        string $formOutput,
        string $customStyles,
        string $embeddedScripts
    ): string {
        return $customStyles . '<div' . $wrapperAttrsString . '>' . $formOutput . '</div>' . $embeddedScripts;
    }
}
