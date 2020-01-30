<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Routing;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\RequestContextResolverInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
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

    public function __construct(RequestContextResolverInterface $inner, Connection $connection)
    {
        $this->inner = $inner;
        $this->connection = $connection;
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
        $source->addPermissions($this->fetchPermissions($roleId));
    }

    private function getRoleIdOfAppByIntegrationId(string $integrationId): ?string
    {
        /** @var string|null $roleId */
        $roleId = $this->connection->fetchColumn(
            'SELECT acl_role_id FROM `app` WHERE `integration_id` = :integrationId',
            ['integrationId' => Uuid::fromHexToBytes($integrationId)]
        );

        return $roleId ? Uuid::fromBytesToHex($roleId) : null;
    }

    /**
     * @return array<string, string>
     */
    private function fetchPermissions(string $roleId): array
    {
        return $this->connection->executeQuery(
            'SELECT `resource`, `privilege`
            FROM `acl_resource`
            WHERE `acl_role_id` = :roleId',
            ['roleId' => Uuid::fromHexToBytes($roleId)]
        )->fetchAll(FetchMode::ASSOCIATIVE);
    }
}
