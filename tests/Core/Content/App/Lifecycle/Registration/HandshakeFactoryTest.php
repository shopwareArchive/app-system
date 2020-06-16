<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Lifecycle\Registration;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\HandshakeFactory;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\PrivateHandshake;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\StoreHandshake;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;

class HandshakeFactoryTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testManifestWithSecretProducesAPrivateHandshake(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/../../Manifest/_fixtures/minimal/manifest.xml');

        $shopUrl = 'test.shop.com';
        $appId = Uuid::randomHex();

        $factory = new HandshakeFactory($shopUrl, $this->getContainer()->get(ShopIdProvider::class));

        $handshake = $factory->create($manifest, $appId);

        static::assertInstanceOf(PrivateHandshake::class, $handshake);
    }

    public function testManifestWithoutSecretProducesAStoreHandshake(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/../../Manifest/_fixtures/public/manifest.xml');

        $shopUrl = 'test.shop.com';
        $appId = Uuid::randomHex();

        $factory = new HandshakeFactory($shopUrl, $this->getContainer()->get(ShopIdProvider::class));

        $handshake = $factory->create($manifest, $appId);

        static::assertInstanceOf(StoreHandshake::class, $handshake);
    }
}
