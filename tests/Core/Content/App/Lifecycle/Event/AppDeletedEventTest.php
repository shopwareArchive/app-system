<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Lifecycle\Event;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Api\Acl\Permission\AclPermissionCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Event\AppDeletedEvent;

class AppDeletedEventTest extends TestCase
{
    public function testGetter(): void
    {
        $appId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $event = new AppDeletedEvent(
            $appId,
            $context
        );

        static::assertEquals($appId, $event->getAppId());
        static::assertEquals($context, $event->getContext());
        static::assertEquals(AppDeletedEvent::NAME, $event->getName());
        static::assertEquals([], $event->getWebhookPayload());
    }

    public function testIsAllowed(): void
    {
        $appId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $event = new AppDeletedEvent(
            $appId,
            $context
        );

        static::assertTrue($event->isAllowed($appId, new AclPermissionCollection()));
        static::assertFalse($event->isAllowed(Uuid::randomHex(), new AclPermissionCollection()));
    }
}
