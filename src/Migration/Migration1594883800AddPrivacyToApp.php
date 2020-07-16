<?php declare(strict_types=1);

namespace Swag\SaasConnect\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1594883800AddPrivacyToApp extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594883800;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            ALTER TABLE `saas_app`
            ADD COLUMN `privacy` VARCHAR(255) NULL AFTER `license`;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
