<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Action;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Action\AppAction;
use Swag\SaasConnect\Core\Content\App\Exception\InvalidArgumentException;

class AppActionTest extends TestCase
{
    public function testAsPayload(): void
    {
        $targetUrl = 'https://my-server.com/action';
        $shopUrl = 'https://my-shop.com';
        $appVersion = '1.0.0';
        $entity = 'product';
        $action = 'detail';
        $shopId = Random::getAlphanumericString(12);
        $ids = [Uuid::randomHex()];
        $result = new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids,
            'i am not secret',
            'i am secret',
            's3cr3t',
            $shopId
        );

        $expected = [
            'source' => [
                'url' => $shopUrl,
                'appVersion' => $appVersion,
                'apiKey' => 'i am not secret',
                'secretKey' => 'i am secret',
                'shopId' => $shopId,
            ],
            'data' => [
                'ids' => $ids,
                'entity' => $entity,
                'action' => $action,
            ],
        ];

        static::assertEquals($expected, $result->asPayload());
    }

    public function testInvalidTargetUrl(): void
    {
        static::expectException(InvalidArgumentException::class);
        $targetUrl = 'https://my-server:.com/action';
        $shopUrl = 'https://my-shop.com';
        $appVersion = '1.0.0';
        $entity = 'product';
        $action = 'detail';
        $shopId = Random::getAlphanumericString(12);
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids,
            'i am not secret',
            'i am secret',
            's3cr3t',
            $shopId
        );
    }

    public function testInvalidShopUrl(): void
    {
        static::expectException(InvalidArgumentException::class);
        $targetUrl = 'https://my-server.com/action';
        $shopUrl = 'https://my:shop.com';
        $appVersion = '1.0.0';
        $entity = 'product';
        $action = 'detail';
        $shopId = Random::getAlphanumericString(12);
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids,
            'i am not secret',
            'i am secret',
            's3cr3t',
            $shopId
        );
    }

    public function testInvalidAppVersion(): void
    {
        static::expectException(InvalidArgumentException::class);
        $targetUrl = 'https://my-server.com/action';
        $shopUrl = 'https://my-shop.com';
        $appVersion = '1.0';
        $entity = 'product';
        $action = 'detail';
        $shopId = Random::getAlphanumericString(12);
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids,
            'i am not secret',
            'i am secret',
            's3cr3t',
            $shopId
        );
    }

    public function testEmptyEntity(): void
    {
        static::expectException(InvalidArgumentException::class);
        $targetUrl = 'https://my-server.com/action';
        $shopUrl = 'https://my-shop.com';
        $appVersion = '1.0.0';
        $entity = '';
        $action = 'detail';
        $shopId = Random::getAlphanumericString(12);
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids,
            'i am not secret',
            'i am secret',
            's3cr3t',
            $shopId
        );
    }

    public function testEmptyAction(): void
    {
        static::expectException(InvalidArgumentException::class);
        $targetUrl = 'https://my-server.com/action';
        $shopUrl = 'https://my-shop.com';
        $appVersion = '1.0.0';
        $entity = 'product';
        $action = '';
        $shopId = Random::getAlphanumericString(12);
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids,
            'i am not secret',
            'i am secret',
            's3cr3t',
            $shopId
        );
    }

    public function testInvalidId(): void
    {
        static::expectException(InvalidArgumentException::class);
        $targetUrl = 'https://my-server.com/action';
        $shopUrl = 'https://my-shop.com';
        $appVersion = '1.0.0';
        $entity = 'product';
        $action = 'detail';
        $shopId = Random::getAlphanumericString(12);
        $ids = [Uuid::randomHex(), 'test'];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids,
            'i am not secret',
            'i am secret',
            's3cr3t',
            $shopId
        );
    }

    public function testInvalidAccessKey(): void
    {
        static::expectException(InvalidArgumentException::class);
        $targetUrl = 'https://my-server.com/action';
        $shopUrl = 'https://my-shop.com';
        $appVersion = '1.0.0';
        $entity = 'product';
        $action = 'detail';
        $shopId = Random::getAlphanumericString(12);
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids,
            '',
            'i am secret',
            's3cr3t',
            $shopId
        );
    }

    public function testInvalidSecretKey(): void
    {
        static::expectException(InvalidArgumentException::class);
        $targetUrl = 'https://my-server.com/action';
        $shopUrl = 'https://my-shop.com';
        $appVersion = '1.0.0';
        $entity = 'product';
        $action = 'detail';
        $shopId = Random::getAlphanumericString(12);
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids,
            'i am not secret',
            '',
            's3cr3t',
            $shopId
        );
    }

    public function testInvalidAppSecret(): void
    {
        static::expectException(InvalidArgumentException::class);
        $targetUrl = 'https://my-server.com/action';
        $shopUrl = 'https://my-shop.com';
        $appVersion = '1.0.0';
        $entity = 'product';
        $action = 'detail';
        $shopId = Random::getAlphanumericString(12);
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids,
            'i am not secret',
            'i am secret',
            '',
            $shopId
        );
    }

    public function testInvalidShopId(): void
    {
        static::expectException(InvalidArgumentException::class);
        $targetUrl = 'https://my-server.com/action';
        $shopUrl = 'https://my-shop.com';
        $appVersion = '1.0.0';
        $entity = 'product';
        $action = 'detail';
        $shopId = '';
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids,
            'i am not secret',
            'i am secret',
            's3cr3t',
            $shopId
        );
    }
}
