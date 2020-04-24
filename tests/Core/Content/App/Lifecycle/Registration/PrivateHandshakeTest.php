<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Lifecycle\Registration;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\PrivateHandshake;

class PrivateHandshakeTest extends TestCase
{
    public function testUrlContainsAllNecessaryElements(): void
    {
        $appEndpoint = 'https://test.com/install';

        $handshake = new PrivateHandshake('', '', $appEndpoint, '');

        $registrationUrl = $handshake->fetchUrl();
        static::assertStringStartsWith($appEndpoint, $registrationUrl);

        $queryParams = [];
        \parse_str(\parse_url($registrationUrl, PHP_URL_QUERY), $queryParams);

        static::assertArrayHasKey('shop', $queryParams);
        static::assertArrayHasKey('timestamp', $queryParams);
        static::assertArrayHasKey('hmac', $queryParams);
    }

    public function testUrlHasCorrectShopUrl(): void
    {
        $shopUrl = 'test.shop.com';
        $appEndpoint = 'https://test.com/install';

        $handshake = new PrivateHandshake($shopUrl, '', $appEndpoint, '');

        $registrationUrl = $handshake->fetchUrl();

        $queryParams = [];
        \parse_str(\parse_url($registrationUrl, PHP_URL_QUERY), $queryParams);

        static::assertEquals(urlencode($shopUrl), $queryParams['shop']);
    }

    public function testTimestampIsSet(): void
    {
        $appEndpoint = 'https://test.com/install';

        $handshake = new PrivateHandshake('', '', $appEndpoint, '');

        $registrationUrl = $handshake->fetchUrl();

        $queryParams = [];
        \parse_str(\parse_url($registrationUrl, PHP_URL_QUERY), $queryParams);

        $timestamp = (string) $queryParams['timestamp'];

        static::assertNotEmpty($timestamp);
    }

    public function testSignatureIsCorrect(): void
    {
        $shopUrl = 'test.shop.com';
        $secret = 's3cr3t';
        $appEndpoint = 'https://test.com/install';

        $handshake = new PrivateHandshake($shopUrl, $secret, $appEndpoint, '');

        $registrationUrl = $handshake->fetchUrl();

        $queryParams = [];
        \parse_str(\parse_url($registrationUrl, PHP_URL_QUERY), $queryParams);

        $signature = (string) $queryParams['hmac'];

        $content = \parse_url($registrationUrl, PHP_URL_QUERY);
        $content = \str_replace('&hmac=' . $signature, '', $content);

        static::assertEquals(hash_hmac('sha256', $content, $secret), $signature);
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
