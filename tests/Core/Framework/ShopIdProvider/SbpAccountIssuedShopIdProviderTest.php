<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\ShopIdProvider;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\SaasConnect\Core\Framework\ShopId\SbpAccountIssuedShopIdProvider;

class SbpAccountIssuedShopIdProviderTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var SbpAccountIssuedShopIdProvider
     */
    private $sbpAccountIssuedShopIdProvider;

    public function setUp(): void
    {
        $this->sbpAccountIssuedShopIdProvider = $this->getContainer()->get(SbpAccountIssuedShopIdProvider::class);
    }

    public function testGetName(): void
    {
        static::assertEquals('sbp_account_issued_shop_id_provider', $this->sbpAccountIssuedShopIdProvider->getName());
    }

    public function testIsSupported(): void
    {
        // Is not yet implemented and should always return false
        static::assertFalse($this->sbpAccountIssuedShopIdProvider->isSupported());
    }

    public function testGetShopId(): void
    {
        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Not implemented');
        // is not implemented and expected to throw
        $this->sbpAccountIssuedShopIdProvider->getShopId();
    }
}
