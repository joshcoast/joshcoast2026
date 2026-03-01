<?php

namespace IvyForms\Repository\Confirmation;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Repository\BaseRepositoryInterface;

interface ConfirmationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all confirmations by ID.
     *
     * @param int $id
     *
     * @return array<mixed>
     */
    public function getAllById(int $id): array;
}
