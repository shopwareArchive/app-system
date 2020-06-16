<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Persister;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

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

    public function updatePrivileges(Manifest $manifest, string $roleId): void
    {
        $permissions = $manifest->getPermissions();
        $toBeCreated = $this->generatePrivileges($permissions ? $permissions->getPermissions() : []);

        $toBeDeleted = $this->getExistingPrivileges($roleId);

        foreach ($toBeCreated as $index => $privilege) {
            /** @var int|false $exists */
            $exists = array_search($privilege, $toBeDeleted, true);

            if ($exists === false) {
                continue;
            }

            unset($toBeDeleted[$exists], $toBeCreated[$index]);
        }

        $this->addPrivileges($toBeCreated, $roleId);
        $this->deleteExistingPrivileges($toBeDeleted);
    }

    /**
     * @param array<array<string, string>> $privileges
     */
    private function deleteExistingPrivileges(array $privileges): void
    {
        if (empty($privileges)) {
            return;
        }

        foreach ($privileges as $privilege) {
            $this->connection->executeUpdate(
                'DELETE FROM `acl_resource` WHERE `resource` = :resource AND `privilege` = :privilege',
                ['resource' => $privilege['resource'], 'privilege' => $privilege['privilege']]
            );
        }
    }

    /**
     * @param array<array<string, string>> $privileges
     */
    private function addPrivileges(array $privileges, string $roleId): void
    {
        if (empty($privileges)) {
            return;
        }

        $this->connection->executeUpdate(
            sprintf(
                'INSERT INTO `acl_resource` (`resource`, `privilege`, `acl_role_id`, `created_at`) VALUES %s;',
                $this->getSqlValues($privileges, $roleId)
            )
        );
    }

    /**
     * @param array<string, array<string>> $permissions
     * @return array<array<string, string>>
     */
    private function generatePrivileges(array $permissions): array
    {
        $grantedPrivileges = array_map(function (array $privileges): array {
            $grantedPrivileges = [];

            foreach ($privileges as $privilege) {
                if ($privilege === 'read') {
                    $grantedPrivileges = array_merge($grantedPrivileges, ['list', 'detail']);

                    continue;
                }

                $grantedPrivileges[] = $privilege;
                $grantedPrivileges = array_merge($grantedPrivileges, $this->privilegeDependence[$privilege]);
            }

            return array_unique($grantedPrivileges);
        }, $permissions);

        $privilegeValues = [];
        foreach ($grantedPrivileges as $resource => $privileges) {
            $newPrivileges = array_map(static function (string $privilege) use ($resource): array {
                return ['resource' => $resource, 'privilege' => $privilege];
            }, $privileges);

            $privilegeValues = array_merge($privilegeValues, $newPrivileges);
        }

        return $privilegeValues;
    }

    /**
     * @param array<array<string, string>> $privileges
     */
    private function getSqlValues(array $privileges, string $roleId): string
    {
        $privilegeValues = array_map(function (array $privilege) use ($roleId): string {
            return sprintf(
                '(%s, %s, UNHEX("%s"), NOW())',
                $this->connection->quote($privilege['resource']),
                $this->connection->quote($privilege['privilege']),
                $roleId
            );
        }, $privileges);

        return implode(', ', $privilegeValues);
    }

    /**
     * @return array<array<string, string>>
     */
    private function getExistingPrivileges(string $roleId): array
    {
        $result = $this->connection->executeQuery(
            'SELECT `resource`, `privilege` FROM `acl_resource` WHERE `acl_role_id` = :roleId',
            ['roleId' => Uuid::fromHexToBytes($roleId)]
        )->fetchAll(FetchMode::ASSOCIATIVE);

        if (!$result) {
            return [];
        }

        return $result;
    }
}
