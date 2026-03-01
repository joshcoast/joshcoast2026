<?php

/**
 * Database hook for activation
 */

namespace IvyForms\Services\InstallActions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Confirmation\ConfirmationsTable;
use IvyForms\Services\InstallActions\DB\Entry\EntriesTable;
use IvyForms\Services\InstallActions\DB\EntryField\EntryFieldsTable;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;
use IvyForms\Services\InstallActions\DB\Form\FormsTable;
use IvyForms\Services\InstallActions\DB\Notification\NotificationsTable;
use IvyForms\Services\InstallActions\DB\FieldOptions\FieldOptionsTable;
use IvyForms\Services\InstallActions\Migrations\AddRowColumnLayoutToFields;
use IvyForms\Services\InstallActions\Migrations\SeedRowIndexForFields;
use IvyForms\Services\InstallActions\Migrations\AddReplyToToNotifications;

/**
 * Class ActivationHook
 *
 * @package IvyForms\Services\InstallActions
 */
class ActivationDatabaseHook
{
    /**
     * Initialize the plugin
     * @throws InvalidArgumentException
     */
    public static function init(): void
    {
        FormsTable::init();
        FieldsTable::init();
        NotificationsTable::init();
        ConfirmationsTable::init();
        EntriesTable::init();
        EntryFieldsTable::init();
        FieldOptionsTable::init();

        // Run migrations
        AddRowColumnLayoutToFields::run();
        SeedRowIndexForFields::run();
        AddReplyToToNotifications::run();
    }
}
