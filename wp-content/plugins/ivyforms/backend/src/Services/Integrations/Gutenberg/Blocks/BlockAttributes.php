<?php

/**
 * BlockAttributes class for defining Gutenberg block attributes
 *
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Integrations\Gutenberg\Blocks;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class BlockAttributes
 *
 * @package IvyForms\Services\Blocks
 */
class BlockAttributes
{
    /**
     * Get all attributes for the IvyForms Gutenberg block
     *
     * @return array<string, array<string, mixed>> Block attributes configuration
     */
    public static function getAttributes(): array
    {
        return [
            'formId' => [
                'type' => 'string',
                'default' => '',
            ],
            'showTitle' => [
                'type' => 'boolean',
                'default' => true,
            ],
            'showDescription' => [
                'type' => 'boolean',
                'default' => true,
            ],
            'customCssClass' => [
                'type' => 'string',
                'default' => '',
            ],
            'className' => [
                'type' => 'string',
                'default' => '',
            ],
        ];
    }
}
