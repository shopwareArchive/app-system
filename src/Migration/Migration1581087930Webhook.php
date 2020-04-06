<?php declare(strict_types=1);

namespace Swag\SaasConnect\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1581087930Webhook extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1581087930;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            CREATE TABLE `saas_webhook` (
                `id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `event_name` VARCHAR(500) NOT NULL,
                `url` VARCHAR(500) NOT NULL,
                `app_id` BINARY(16) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.webhook.app_id` FOREIGN KEY (`app_id`) REFERENCES `saas_app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `uniq.webhook.name` UNIQUE (`name`, `app_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
