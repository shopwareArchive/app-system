<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Persister;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PermissionGatewayFactory
{
    /**
     * @var string
     */
    private $shopwareVersion;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(string $shopwareVersion, ContainerInterface $container)
    {
        $this->shopwareVersion = $shopwareVersion;
        $this->container = $container;
    }

    public function createPermissionGateway(): PermissionGatewayStrategy
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        if (version_compare($this->shopwareVersion, '6.3.0.0', '<')) {
            $privileges = [];

            if ($this->container->hasParameter('acl_resource_privileges')) {
                // should be always the case except in tests running on 6.3
                $privileges = $this->container->getParameter('acl_resource_privileges');
            }

            return new PermissionGateway62(
                $connection,
                $privileges
            );
        }

        return new PermissionGateway63($connection);
    }
}
