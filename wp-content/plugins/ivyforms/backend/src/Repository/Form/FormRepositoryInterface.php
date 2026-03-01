<?php

namespace IvyForms\Repository\Form;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Repository\BaseRepositoryInterface;

interface FormRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Check if a form exists in the database.
     *
     * @param int $formId
     * @return bool
     */
    public function exists(int $formId): bool;

    /**
     * @param int $formId
     * @param string $value
     *
     * @return bool
     */
    public function updateFormStarred(int $formId, string $value): bool;

    /**
     * @param int $formId
     * @param string $value
     *
     * @return bool
     */
    public function updateFormStatus(int $formId, string $value): bool;

    /**
     * Get the count of form filters.
     *
     * @return array<string, int>
     */
    public function getFilterCount(): array;
}
