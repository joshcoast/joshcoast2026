<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\ValueObjects\Field;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TimeFieldGeneralSettings
 *
 * Encapsulates time-specific settings extracted from FieldGeneralSettings.
 */
final class TimeFieldGeneralSettings
{
    /**
     * @var string
     */
    private string $timeFieldType;

    /**
     * @var string
     */
    private string $timeFormat;

    public function __construct(string $timeFieldType = '', string $timeFormat = '')
    {
        $this->timeFieldType = $timeFieldType;
        $this->timeFormat = $timeFormat;
    }

    /**
     * Get timeFieldType.
     */
    public function getTimeFieldType(): string
    {
        return $this->timeFieldType;
    }

    /**
     * Set timeFieldType.
     */
    public function setTimeFieldType(string $timeFieldType): void
    {
        $this->timeFieldType = $timeFieldType;
    }

    /**
     * Get timeFormat.
     */
    public function getTimeFormat(): string
    {
        return $this->timeFormat;
    }

    /**
     * Set timeFormat.
     */
    public function setTimeFormat(string $timeFormat): void
    {
        $this->timeFormat = $timeFormat;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'timeFieldType' => $this->getTimeFieldType(),
            'timeFormat'    => $this->getTimeFormat(),
        ];
    }
}
