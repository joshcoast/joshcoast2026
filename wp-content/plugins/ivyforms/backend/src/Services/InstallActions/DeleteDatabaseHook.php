<?php

/**
 * Database hook for deletion
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

/**
 * Class DeleteDatabaseHook
 *
 * @package IvyForms\Services\InstallActions
 */
class DeleteDatabaseHook
{
    /**
     * Delete the plugin tables
     *
     * @throws InvalidArgumentException
     */
    public static function delete(): void
    {
        // Delete in reverse order of creation (respecting foreign keys)
        FieldOptionsTable::delete();
        EntryFieldsTable::delete();
        EntriesTable::delete();
        ConfirmationsTable::delete();
        NotificationsTable::delete();
        FieldsTable::delete();
        FormsTable::delete();

        // Also delete plugin options from wp_options
        delete_option('ivyforms_settings');
        delete_option('ivyforms_version');
    }
}
