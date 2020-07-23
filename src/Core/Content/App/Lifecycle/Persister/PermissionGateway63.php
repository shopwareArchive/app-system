<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Persister;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\Permissions;
use Swag\SaasConnect\Core\Framework\Api\Acl\AclPrivilegeCollection;

class PermissionGateway63 implements PermissionGatewayStrategy
{
    private const PRIVILEGE_DEPENDENCE = [
        AclPrivilegeCollection::PRIVILEGE_READ => [],
        AclPrivilegeCollection::PRIVILEGE_CREATE => [AclPrivilegeCollection::PRIVILEGE_READ],
        AclPrivilegeCollection::PRIVILEGE_UPDATE => [AclPrivilegeCollection::PRIVILEGE_READ],
        AclPrivilegeCollection::PRIVILEGE_DELETE => [AclPrivilegeCollection::PRIVILEGE_READ],
    ];

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function updatePrivileges(?Permissions $permissions, string $roleId): void
    {
        $privileges = $this->generatePrivileges($permissions ? $permissions->getPermissions() : []);

        $this->addPrivileges($privileges, $roleId);
    }

    public function fetchPrivileges(string $roleId): AclPrivilegeCollection
    {
        /** @var string $privileges */
        $privileges = $this->connection->fetchColumn('
            SELECT `privileges`
            FROM `acl_role`
            WHERE `id` = :aclRoleId
        ', ['aclRoleId' => $roleId]);

        return new AclPrivilegeCollection(json_decode($privileges, true));
    }

    /**
     * @param array<string> $privileges
     */
    private function addPrivileges(array $privileges, string $roleId): void
    {
        $this->connection->executeUpdate(
            'UPDATE `acl_role` SET `privileges` = :privileges WHERE id = :id',
            [
                'privileges' => json_encode($privileges),
                'id' => Uuid::fromHexToBytes($roleId),
            ]
        );
    }

    /**
     * @param array<string, array<string>> $permissions
     * @return array<string>
     */
    private function generatePrivileges(array $permissions): array
    {
        $grantedPrivileges = array_map(static function (array $privileges): array {
            $grantedPrivileges = [];

            foreach ($privileges as $privilege) {
                $grantedPrivileges[] = $privilege;
                $grantedPrivileges = array_merge($grantedPrivileges, self::PRIVILEGE_DEPENDENCE[$privilege]);
            }

            return array_unique($grantedPrivileges);
        }, $permissions);

        $privilegeValues = [];
        foreach ($grantedPrivileges as $resource => $privileges) {
            $newPrivileges = array_map(static function (string $privilege) use ($resource): string {
                return $resource . ':' . $privilege;
            }, $privileges);

            $privilegeValues = array_merge($privilegeValues, $newPrivileges);
        }

        return $privilegeValues;
    }
}
