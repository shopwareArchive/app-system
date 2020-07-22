<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Routing;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\RequestContextResolverInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Persister\PermissionGatewayStrategy;
use Swag\SaasConnect\Core\Framework\Api\Acl\AclPrivilegeCollection;
use Symfony\Component\HttpFoundation\Request;

class ApiRequestContextResolverDecorator implements RequestContextResolverInterface
{
    /**
     * @var RequestContextResolverInterface
     */
    private $inner;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PermissionGatewayStrategy
     */
    private $permissionGateway;

    /**
     * @var string
     */
    private $shopwareVersion;

    public function __construct(
        RequestContextResolverInterface $inner,
        Connection $connection,
        PermissionGatewayStrategy $permissionGateway,
        string $shopwareVersion
    ) {
        $this->inner = $inner;
        $this->connection = $connection;
        $this->permissionGateway = $permissionGateway;
        $this->shopwareVersion = $shopwareVersion;
    }

    public function resolve(Request $request): void
    {
        $this->inner->resolve($request);

        /** @var Context|null $context */
        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_CONTEXT_OBJECT);

        if (!$context || !$context->getSource() instanceof AdminApiSource) {
            return;
        }

        /** @var AdminApiSource $source */
        $source = $context->getSource();
        $integrationId = $source->getIntegrationId();
        if ($source->getUserId() || $integrationId === null) {
            return;
        }

        $roleId = $this->getRoleIdOfAppByIntegrationId($integrationId);
        if (!$roleId) {
            return;
        }

        $source->setIsAdmin(false);
        $permissions = $this->permissionGateway->fetchPrivileges($roleId);

        $this->addPermissionsToSource($source, $permissions);
    }

    private function getRoleIdOfAppByIntegrationId(string $integrationId): ?string
    {
        /** @var string|null $roleId */
        $roleId = $this->connection->fetchColumn(
            'SELECT acl_role_id FROM `saas_app` WHERE `integration_id` = :integrationId',
            ['integrationId' => Uuid::fromHexToBytes($integrationId)]
        );

        return $roleId ? $roleId : null;
    }

    /**
     * @psalm-suppress UndefinedMethod needed for compatibility issues
     */
    private function addPermissionsToSource(AdminApiSource $source, AclPrivilegeCollection $permissions): void
    {
        if (version_compare($this->shopwareVersion, '6.3.0.0', '<')) {
            /* @phpstan-ignore-next-line */
            $source->addPermissions($permissions->as62Compatible());
        } else {
            /* @phpstan-ignore-next-line */
            $source->setPermissions($permissions->as63Compatible());
        }
    }
}
