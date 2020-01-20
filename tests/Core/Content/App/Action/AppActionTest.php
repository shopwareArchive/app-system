<?php


namespace Swag\SaasConnect\Test\Core\Content\App\Action;

use PHPUnit\Framework\TestCase;
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
        $ids = [Uuid::randomHex()];
        $result = new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids
        );

        $expected = [
            'source' => [
                'url' => $shopUrl,
                'appVersion' => $appVersion,
                'apiKey' => '',
            ],
            'data' => [
                'ids' => $ids,
                'entity' => $entity,
                'action' => $action,
            ]
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
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids
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
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids
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
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids
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
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids
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
        $ids = [Uuid::randomHex()];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids
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
        $ids = [Uuid::randomHex(), 'test'];
        new AppAction(
            $targetUrl,
            $shopUrl,
            $appVersion,
            $entity,
            $action,
            $ids
        );
    }
}
