<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Lifecycle\Registration;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\HandshakeFactory;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\PrivateHandshake;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\StoreHandshake;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

class HandshakeFactoryTest extends TestCase
{
    public function testManifestWithSecretProducesAPrivateHandshake(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/../../Manifest/_fixtures/private/manifest.xml');

        $shopUrl = 'test.shop.com';

        $factory = new HandshakeFactory($shopUrl);

        $handshake = $factory->create($manifest);

        static::assertInstanceOf(PrivateHandshake::class, $handshake);
    }

    public function testManifestWithoutSecretProducesAStoreHandshake(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/../../Manifest/_fixtures/minimal/manifest.xml');

        $shopUrl = 'test.shop.com';

        $factory = new HandshakeFactory($shopUrl);

        $handshake = $factory->create($manifest);

        static::assertInstanceOf(StoreHandshake::class, $handshake);
    }
}
