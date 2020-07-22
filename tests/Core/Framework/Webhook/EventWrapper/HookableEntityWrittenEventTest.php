<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\Webhook\EventWrapper;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Framework\Api\Acl\AclPrivilegeCollection;
use Swag\SaasConnect\Core\Framework\Webhook\EventWrapper\HookableEntityWrittenEvent;

class HookableEntityWrittenEventTest extends TestCase
{
    public function testGetter(): void
    {
        $entityId = Uuid::randomHex();
        $event = new HookableEntityWrittenEvent($this->getEntityWrittenEvent($entityId));

        static::assertEquals('product.written', $event->getName());
        static::assertEquals([
            [
                'entity' => 'product',
                'operation' => 'delete',
                'primaryKey' => $entityId,
                'updatedFields' => [],
            ],
        ], $event->getWebhookPayload());
    }

    public function testIsAllowed(): void
    {
        $entityId = Uuid::randomHex();
        $event = new HookableEntityWrittenEvent($this->getEntityWrittenEvent($entityId));

        $allowedPermissions = new AclPrivilegeCollection([
            ProductDefinition::ENTITY_NAME . ':' . AclPrivilegeCollection::PRIVILEGE_LIST,
            ProductDefinition::ENTITY_NAME . ':' . AclPrivilegeCollection::PRIVILEGE_READ,
        ]);
        static::assertTrue($event->isAllowed(
            Uuid::randomHex(),
            $allowedPermissions
        ));

        $notAllowedPermissions = new AclPrivilegeCollection([
            CustomerDefinition::ENTITY_NAME . ':' . AclPrivilegeCollection::PRIVILEGE_LIST,
            CustomerDefinition::ENTITY_NAME . ':' . AclPrivilegeCollection::PRIVILEGE_READ,
        ]);
        static::assertFalse($event->isAllowed(
            Uuid::randomHex(),
            $notAllowedPermissions
        ));
    }

    private function getEntityWrittenEvent(string $entityId): EntityWrittenEvent
    {
        $context = Context::createDefaultContext();

        return new EntityWrittenEvent(
            ProductDefinition::ENTITY_NAME,
            [
                new EntityWriteResult(
                    $entityId,
                    [],
                    ProductDefinition::ENTITY_NAME,
                    EntityWriteResult::OPERATION_DELETE,
                    null,
                    null
                ),
            ],
            $context
        );
    }
}
