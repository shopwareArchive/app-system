<?php declare(strict_types=1);

namespace Swag\SaasConnect\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1592313163DropAppAccessToken extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1592313163;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            ALTER TABLE `saas_app`
            DROP COLUMN `access_token`
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
