<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Lifecycle\Registration;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\PrivateHandshake;

class PrivateHandshakeTest extends TestCase
{
    public function testUrlContainsAllNecessaryElements(): void
    {
        $shopUrl = 'test.shop.com';
        $secret = 's3cr3t';
        $appEndpoint = 'https://test.com/install';

        $handshake = new PrivateHandshake($shopUrl, $secret, $appEndpoint, '');

        $request = $handshake->assembleRequest();
        static::assertStringStartsWith($appEndpoint, (string) $request->getUri());

        $queryParams = [];
        \parse_str($request->getUri()->getQuery(), $queryParams);

        static::assertArrayHasKey('shop-url', $queryParams);
        static::assertEquals(urlencode($shopUrl), $queryParams['shop-url']);

        static::assertArrayHasKey('timestamp', $queryParams);
        static::assertNotEmpty((string) $queryParams['timestamp']);

        static::assertTrue($request->hasHeader('shopware-app-signature'));
        static::assertEquals(
            hash_hmac('sha256', $request->getUri()->getQuery(), $secret),
            $request->getHeaderLine('shopware-app-signature')
        );
    }

    public function testAppProof(): void
    {
        $shopUrl = 'test.shop.com';
        $secret = 'stuff';
        $appEndpoint = 'https://test.com/install';
        $appName = 'testapp';

        $handshake = new PrivateHandshake($shopUrl, $secret, $appEndpoint, $appName);

        $appProof = $handshake->fetchAppProof();

        static::assertEquals(hash_hmac('sha256', $shopUrl . $appName, $secret), $appProof);
    }
}
