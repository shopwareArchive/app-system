<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\ShopIdProvider;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\SaasConnect\Core\Framework\ShopId\FallbackShopIdProvider;

class FallbackShopIdProviderTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var FallbackShopIdProvider
     */
    private $fallbackShopIdProvider;

    public function setUp(): void
    {
        $this->fallbackShopIdProvider = $this->getContainer()->get(FallbackShopIdProvider::class);
    }

    public function testGetName(): void
    {
        static::assertEquals('fallback_shop_id_provider', $this->fallbackShopIdProvider->getName());
    }

    public function testIsSupported(): void
    {
        static::assertTrue($this->fallbackShopIdProvider->isSupported());
    }

    public function testGetShopId(): void
    {
        static::assertIsString($this->fallbackShopIdProvider->getShopId());
    }
}
