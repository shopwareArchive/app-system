<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\Permissions;

class PermissionPersister
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array<string, array<string>>
     */
    private $privilegeDependence;

    /**
     * @param array<string, array<string>> $privilegeDependence
     */
    public function __construct(Connection $connection, array $privilegeDependence)
    {
        $this->connection = $connection;
        $this->privilegeDependence = $privilegeDependence;
    }

    public function updatePrivileges(?Permissions $permissions, string $roleId): void
    {
        $this->deleteExistingPrivileges($roleId);
        $this->addPrivileges($permissions, $roleId);
    }

    private function deleteExistingPrivileges(string $roleId): void
    {
        $this->connection->executeUpdate(
            'DELETE FROM `acl_resource` WHERE `acl_role_id` = :roleId',
            ['roleId' => Uuid::fromHexToBytes($roleId)]
        );
    }

    private function addPrivileges(?Permissions $permissions, string $roleId): void
    {
        if (!$permissions || empty($permissions->getPermissions())) {
            return;
        }

        $payload = $this->generatePrivileges($permissions->getPermissions(), $roleId);

        $this->connection->executeUpdate(
            sprintf(
                'INSERT INTO `acl_resource` (`resource`, `privilege`, `acl_role_id`, `created_at`) VALUES %s;',
                $payload
            )
        );
    }

    /**
     * @param array<string, array<string>> $permissions
     */
    private function generatePrivileges(array $permissions, string $roleId): string
    {
        $privilegeValues = [];

        foreach ($permissions as $resource => $privileges) {
            $grantedPrivileges = $privileges;

            foreach ($privileges as $privilege) {
                $grantedPrivileges = array_merge($grantedPrivileges, $this->privilegeDependence[$privilege]);
            }

            foreach (array_unique($grantedPrivileges) as $privilege) {
                $privilegeValues[] = sprintf(
                    '(%s, %s, UNHEX("%s"), NOW())',
                    $this->connection->quote($resource),
                    $this->connection->quote($privilege),
                    $roleId
                );
            }
        }

        return implode(', ', $privilegeValues);
    }
}
