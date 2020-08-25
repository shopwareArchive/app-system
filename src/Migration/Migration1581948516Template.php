<?php declare(strict_types=1);

namespace Swag\SaasConnect\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1581948516Template extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1581948516;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            CREATE TABLE IF NOT EXISTS `saas_template` (
              `id` BINARY(16) NOT NULL,
              `template` LONGTEXT NOT NULL,
              `path` VARCHAR(1024) NOT NULL,
              `active` TINYINT(1) NOT NULL,
              `app_id` BINARY(16) NOT NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              INDEX `idx.saas_template.path` (`path`(256)),
              CONSTRAINT `fk.saas_template.app_id` FOREIGN KEY (`app_id`) REFERENCES `saas_app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
