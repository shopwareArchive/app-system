<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Persister;

use Swag\SaasConnect\Core\Content\App\Manifest\Xml\Permissions;
use Swag\SaasConnect\Core\Framework\Api\Acl\AclPrivilegeCollection;

interface PermissionGatewayStrategy
{
    public function updatePrivileges(?Permissions $permissions, string $roleId): void;

    /**
     * @param string $roleId the role id encoded as binary
     */
    public function fetchPrivileges(string $roleId): AclPrivilegeCollection;
}
