<?php declare(strict_types=1);

namespace Swag\SaasConnect\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1578558350App extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1578558350;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `app` (
                `id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `path` VARCHAR(255) NOT NULL,
                `author` VARCHAR(255) NULL,
                `copyright` VARCHAR(255) NULL,
                `license` VARCHAR(255) NULL,
                `version` VARCHAR(255) NOT NULL,
                `icon` MEDIUMBLOB NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq.name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeQuery('
            CREATE TABLE `app_translation` (
                `app_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                `label` VARCHAR(255) NOT NULL,
                `description` LONGTEXT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`app_id`,`language_id`),
                KEY `fk.app_translation.app_id` (`app_id`),
                KEY `fk.app_translation.language_id` (`language_id`),
                CONSTRAINT `fk.app_translation.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.app_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
