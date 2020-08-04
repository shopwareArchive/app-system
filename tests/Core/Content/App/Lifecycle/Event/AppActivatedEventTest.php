<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Lifecycle\Event;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Event\AppActivatedEvent;
use Swag\SaasConnect\Core\Framework\Api\Acl\AclPrivilegeCollection;

class AppActivatedEventTest extends TestCase
{
    public function testGetter(): void
    {
        $appId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $event = new AppActivatedEvent(
            $appId,
            $context
        );

        static::assertEquals($appId, $event->getAppId());
        static::assertEquals($context, $event->getContext());
        static::assertEquals(AppActivatedEvent::NAME, $event->getName());
        static::assertEquals([], $event->getWebhookPayload());
    }

    public function testIsAllowed(): void
    {
        $appId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $event = new AppActivatedEvent(
            $appId,
            $context
        );

        static::assertTrue($event->isAllowed($appId, new AclPrivilegeCollection()));
        static::assertFalse($event->isAllowed(Uuid::randomHex(), new AclPrivilegeCollection()));
    }
}
