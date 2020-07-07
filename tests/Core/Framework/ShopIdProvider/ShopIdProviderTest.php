<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\ShopIdProvider;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\SaasConnect\Core\Framework\ShopId\AppUrlChangeDetectedException;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;
use Swag\SaasConnect\Test\EnvTestBehaviour;
use Swag\SaasConnect\Test\SystemConfigTestBehaviour;

class ShopIdProviderTest extends TestCase
{
    use IntegrationTestBehaviour;
    use EnvTestBehaviour;
    use SystemConfigTestBehaviour;

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

    public function testGetShopIdWithoutStoredShopId(): void
    {
        $shopId = $this->shopIdProvider->getShopId();

        static::assertEquals([
            'app_url' => getenv('APP_URL'),
            'value' => $shopId,
        ], $this->systemConfigService->get(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY));

        static::assertNull(
            $this->systemConfigService->get(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY)
        );
    }

    public function testGetShopIdReturnsSameIdOnMultipleCalls(): void
    {
        $firstShopId = $this->shopIdProvider->getShopId();
        $secondShopId = $this->shopIdProvider->getShopId();

        static::assertEquals($firstShopId, $secondShopId);

        static::assertEquals([
            'app_url' => getenv('APP_URL'),
            'value' => $firstShopId,
        ], $this->systemConfigService->get(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY));

        static::assertNull(
            $this->systemConfigService->get(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY)
        );
    }

    public function testGetShopIdThrowsIfAppUrlIsChanged(): void
    {
        $this->shopIdProvider->getShopId();

        $this->setEnvVars([
            'APP_URL' => 'http://test.com',
        ]);

        $wasThrown = false;

        try {
            $this->shopIdProvider->getShopId();
        } catch (AppUrlChangeDetectedException $e) {
            $wasThrown = true;
        }

        static::assertTrue($wasThrown);
        static::assertTrue(
            $this->systemConfigService->get(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY)
        );
    }
}
