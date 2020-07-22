<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Api\Acl;

class AclPrivilegeCollection
{
    // Redeclare consts from AclResourceDefinition in <=6.2 and AclRoleDefinition in >=6.3
    // only available in <=6.2
    public const PRIVILEGE_LIST = 'list';
    // only available in <=6.2
    public const PRIVILEGE_DETAIL = 'detail';
    // only available in >=6.3
    public const PRIVILEGE_READ = 'read';
    public const PRIVILEGE_CREATE = 'create';
    public const PRIVILEGE_UPDATE = 'update';
    public const PRIVILEGE_DELETE = 'delete';

    /**
     * @var array<string>
     */
    private $privileges;

    /**
     * @param array<string> $privileges
     */
    public function __construct(array $privileges = [])
    {
        $this->privileges = $privileges;
    }

    public function isAllowed(string $resource, string $privilege): bool
    {
        return in_array($resource . ':' . $privilege, $this->privileges, true);
    }

    public function count(): int
    {
        return count($this->privileges);
    }

    /**
     * @return array<array<string, string>>
     */
    public function as62Compatible(): array
    {
        return array_map(static function (string $privilege): array {
            $parts = explode(':', $privilege);

            return [
                'resource' => $parts[0],
                'privilege' => $parts[1],
            ];
        }, $this->privileges);
    }

    /**
     * @return array<string>
     */
    public function as63Compatible(): array
    {
        return $this->privileges;
    }
}
