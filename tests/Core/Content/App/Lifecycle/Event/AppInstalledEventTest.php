<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Lifecycle\Event;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Event\AppInstalledEvent;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Framework\Api\Acl\AclPrivilegeCollection;

class AppInstalledEventTest extends TestCase
{
    public function testGetter(): void
    {
        $appId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $event = new AppInstalledEvent(
            $appId,
            Manifest::createFromXmlFile(__DIR__ . '/../../Manifest/_fixtures/test/manifest.xml'),
            $context
        );

        static::assertEquals($appId, $event->getAppId());
        static::assertInstanceOf(Manifest::class, $event->getApp());
        static::assertEquals($context, $event->getContext());
        static::assertEquals(AppInstalledEvent::NAME, $event->getName());
        static::assertEquals([
            'appVersion' => '1.0.0',
        ], $event->getWebhookPayload());
    }

    public function testIsAllowed(): void
    {
        $appId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $event = new AppInstalledEvent(
            $appId,
            Manifest::createFromXmlFile(__DIR__ . '/../../Manifest/_fixtures/test/manifest.xml'),
            $context
        );

        static::assertTrue($event->isAllowed($appId, new AclPrivilegeCollection()));
        static::assertFalse($event->isAllowed(Uuid::randomHex(), new AclPrivilegeCollection()));
    }
}
