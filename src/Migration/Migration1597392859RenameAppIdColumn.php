<?php declare(strict_types=1);

namespace Swag\SaasConnect\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1597392859RenameAppIdColumn extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1597392859;
    }

    /**
     * We needed to rename the column in the original migration to prevent compatibility issues with platform
     * For updates where the old column name is in use we migrate it to the new name
     */
    public function update(Connection $connection): void
    {
        $columnExists = $connection->executeQuery('SHOW COLUMNS FROM `custom_field_set` LIKE "saas_app_id";')->fetchAll();

        if (count($columnExists) > 0) {
            // if column with the new name already exists, we don't need to rename anything
            return;
        }

        $connection->executeUpdate('
            ALTER TABLE `custom_field_set`
            DROP FOREIGN KEY `fk.custom_field_set.app_id`
            CHANGE COLUMN `app_id` `saas_app_id` BINARY(16) NULL,
            ADD CONSTRAINT `fk.custom_field_set.saas_app_id` FOREIGN KEY (`saas_app_id`) REFERENCES `saas_app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
