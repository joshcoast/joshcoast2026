<?php

/**
 * @copyright © Melograno Venture Studio. All rights.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\DB\Confirmation;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\InstallActions\DB\AbstractDatabaseTable;
use IvyForms\Common\Exceptions\InvalidArgumentException;

/**
 * Class ConfirmationsTable
 *
 * @package IvyForms\Services\InstallActions\DB\Confirmation
 */
class ConfirmationsTable extends AbstractDatabaseTable
{
    public const TABLE = 'confirmations';

    /**
     * @return string
     * @throws InvalidArgumentException
     *
     */
    public static function buildTable(): string
    {
        $table = self::getTableName();

        return "CREATE TABLE $table (
                   `id` INT(11) NOT NULL AUTO_INCREMENT,
                   `formId` INT(11) NULL,
                   `type` VARCHAR(255) NULL,
                   `enabled` INT(1) NULL,
                   `showForm` INT(1) NULL,
                   `message` TEXT NOT NULL,
                   `url` VARCHAR(255) NULL,
                   `page` VARCHAR(255) NULL,
                    PRIMARY KEY (`id`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
