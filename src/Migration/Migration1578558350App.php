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
        $connection->executeUpdate('
            CREATE TABLE `saas_app` (
                `id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `path` VARCHAR(255) NOT NULL,
                `author` VARCHAR(255) NULL,
                `copyright` VARCHAR(255) NULL,
                `license` VARCHAR(255) NULL,
                `version` VARCHAR(255) NOT NULL,
                `icon` MEDIUMBLOB NULL,
                `access_token` VARCHAR(255) NOT NULL,
                `app_secret` VARCHAR(255) NULL,
                `modules` JSON NULL,
                `integration_id` BINARY(16) NOT NULL,
                `acl_role_id` BINARY(16) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq.name` (`name`),
                CONSTRAINT `json.app.modules` CHECK (JSON_VALID(`modules`)),
                CONSTRAINT `fk.app.integration_id` FOREIGN KEY (`integration_id`) REFERENCES `integration` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.app.acl_role_id` FOREIGN KEY (`acl_role_id`) REFERENCES `acl_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeUpdate('
            CREATE TABLE `saas_app_translation` (
                `saas_app_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                `label` VARCHAR(255) NOT NULL,
                `description` LONGTEXT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`saas_app_id`,`language_id`),
                KEY `fk.app_translation.saas_app_id` (`saas_app_id`),
                KEY `fk.app_translation.language_id` (`language_id`),
                CONSTRAINT `fk.app_translation.saas_app_id` FOREIGN KEY (`saas_app_id`) REFERENCES `saas_app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.app_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
