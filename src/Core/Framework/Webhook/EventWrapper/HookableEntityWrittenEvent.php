<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Webhook\EventWrapper;

use Shopware\Core\Framework\Api\Acl\Permission\AclPermissionCollection;
use Shopware\Core\Framework\Api\Acl\Resource\AclResourceDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Swag\SaasConnect\Core\Framework\Webhook\Hookable;

class HookableEntityWrittenEvent implements Hookable
{
    /**
     * @var EntityWrittenEvent
     */
    private $event;

    public function __construct(EntityWrittenEvent $event)
    {
        $this->event = $event;
    }

    public function getName(): string
    {
        return $this->event->getName();
    }

    /**
     * @return array<array<string, string|array<string, string>|array<string>>>
     */
    public function getWebhookPayload(): array
    {
        return $this->getPayloadFromEvent($this->event);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function isAllowed(string $appId, AclPermissionCollection $permissions): bool
    {
        return $permissions->isAllowed($this->event->getEntityName(), AclResourceDefinition::PRIVILEGE_LIST);
    }

    /**
     * @return array<array<string, string|array<string, string>|array<string>>>
     */
    private function getPayloadFromEvent(EntityWrittenEvent $event): array
    {
        $payload = [];

        foreach ($event->getWriteResults() as $writeResult) {
            $result = [
                'entity' => $writeResult->getEntityName(),
                'operation' => $writeResult->getOperation(),
                'primaryKey' => $writeResult->getPrimaryKey(),
            ];

            if (!$event instanceof EntityDeletedEvent) {
                $result['updatedFields'] = array_keys($writeResult->getPayload());
            }

            $payload[] = $result;
        }

        return $payload;
    }
}
