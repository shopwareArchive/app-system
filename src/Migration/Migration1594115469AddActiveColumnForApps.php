<?php declare(strict_types=1);

namespace Swag\SaasConnect\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1594115469AddActiveColumnForApps extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594115469;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            ALTER TABLE `saas_app`
            ADD COLUMN `active` tinyint(1) default 0 not null after `acl_role_id`
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
