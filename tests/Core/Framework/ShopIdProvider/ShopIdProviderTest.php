<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\ShopIdProvider;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\SaasConnect\Core\Framework\ShopId\NoSupportedShopIdProviderException;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProviderStrategy;

class ShopIdProviderTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var ShopIdProvider
     */
    private $shopIdProvider;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function setUp(): void
    {
        $this->shopIdProvider = $this->getContainer()->get(ShopIdProvider::class);
        $this->systemConfigService = $this->getContainer()->get(SystemConfigService::class);
    }

    public function tearDown(): void
    {
        // reset internal system config cache
        $reflection = new \ReflectionClass($this->systemConfigService);

        $property = $reflection->getProperty('configs');
        $property->setAccessible(true);
        $property->setValue($this->systemConfigService, []);
    }

    public function testGetShopIdWithoutStoredShopIds(): void
    {
        $appId = Uuid::randomHex();

        $shopId = $this->shopIdProvider->getShopId($appId);

        static::assertEquals([
            'fallback_shop_id_provider' => [
                'value' => $shopId,
                'apps' => [
                    $appId,
                ],
            ],
        ], $this->systemConfigService->get(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY));
    }

    public function testGetShopIdThrowsWithoutSupportedProvider(): void
    {
        $notSupportedProvider = $this->createMock(ShopIdProviderStrategy::class);
        $notSupportedProvider->expects(static::once())
            ->method('isSupported')
            ->willReturn(false);

        $shopIdProvider = new ShopIdProvider([$notSupportedProvider], $this->systemConfigService);

        static::expectException(NoSupportedShopIdProviderException::class);
        $shopIdProvider->getShopId(Uuid::randomHex());
    }

    public function testReturnsPreviouslyGeneratedIdForSameStrategy(): void
    {
        $firstAppId = Uuid::randomHex();
        $secondAppId = Uuid::randomHex();

        $shopId = $this->shopIdProvider->getShopId($firstAppId);

        static::assertEquals($shopId, $this->shopIdProvider->getShopId($secondAppId));

        static::assertEquals([
            'fallback_shop_id_provider' => [
                'value' => $shopId,
                'apps' => [
                    $firstAppId,
                    $secondAppId,
                ],
            ],
        ], $this->systemConfigService->get(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY));
    }

    public function testItReturnsSameIdForApp(): void
    {
        $appId = Uuid::randomHex();

        $this->systemConfigService->set(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY, [
            'test_provider' => [
                'value' => 'justATest',
                'apps' => [$appId],
            ],
        ]);

        static::assertEquals('justATest', $this->shopIdProvider->getShopId($appId));
    }
}
