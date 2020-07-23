<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Lifecycle\Persister;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Persister\PermissionGateway62;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Persister\PermissionGateway63;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Persister\PermissionGatewayFactory;

class PermissionGatewayFactoryTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @dataProvider getVersionGateways
     */
    public function testCreateGateway(string $shopwareVersion, string $expectedGateway): void
    {
        $factory = new PermissionGatewayFactory(
            $shopwareVersion,
            $this->getContainer()
        );

        $gateway = $factory->createPermissionGateway();
        static::assertInstanceOf($expectedGateway, $gateway);
    }

    public function getVersionGateways(): array
    {
        return [
            ['6.1.3', PermissionGateway62::class],
            ['6.2.0', PermissionGateway62::class],
            ['6.2.3', PermissionGateway62::class],
            ['6.3.0.0', PermissionGateway63::class],
            ['6.3.0.1', PermissionGateway63::class],
            ['6.3.1.1', PermissionGateway63::class],
            ['6.4.0.0', PermissionGateway63::class],
        ];
    }
}
