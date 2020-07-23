<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Webhook\EventWrapper;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\Event\BusinessEventInterface;
use Swag\SaasConnect\Core\Framework\Api\Acl\AclPrivilegeCollection;
use Swag\SaasConnect\Core\Framework\Webhook\BusinessEventEncoder;
use Swag\SaasConnect\Core\Framework\Webhook\Hookable;

class HookableBusinessEvent implements Hookable
{
    /**
     * @var BusinessEventInterface
     */
    private $businessEvent;

    /**
     * @var BusinessEventEncoder
     */
    private $businessEventEncoder;

    private function __construct(BusinessEventInterface $businessEvent, BusinessEventEncoder $businessEventEncoder)
    {
        $this->businessEvent = $businessEvent;
        $this->businessEventEncoder = $businessEventEncoder;
    }

    public static function fromBusinessEvent(
        BusinessEventInterface $businessEvent,
        BusinessEventEncoder $businessEventEncoder
    ): self {
        return new self($businessEvent, $businessEventEncoder);
    }

    public function getName(): string
    {
        return $this->businessEvent->getName();
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint
     */
    public function getWebhookPayload(): array
    {
        return $this->businessEventEncoder->encode($this->businessEvent);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function isAllowed(string $appId, AclPrivilegeCollection $permissions): bool
    {
        foreach ($this->businessEvent::getAvailableData()->toArray() as $dataType) {
            if (!$this->checkPermissionsForDataType($dataType, $permissions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    private function checkPermissionsForDataType(array $dataType, AclPrivilegeCollection $permissions): bool
    {
        if ($dataType['type'] === 'object' && is_array($dataType['data']) && !empty($dataType['data'])) {
            foreach ($dataType['data'] as $nested) {
                if (!$this->checkPermissionsForDataType($nested, $permissions)) {
                    return false;
                }
            }
        }

        if ($dataType['type'] === 'array' && $dataType['of']) {
            if (!$this->checkPermissionsForDataType($dataType['of'], $permissions)) {
                return false;
            }
        }

        if ($dataType['type'] === 'entity' || $dataType['type'] === 'collection') {
            /** @var EntityDefinition $definition */
            $definition = new $dataType['entityClass']();
            if (!$permissions->isAllowed($definition->getEntityName(), AclPrivilegeCollection::PRIVILEGE_LIST)
                && !$permissions->isAllowed($definition->getEntityName(), AclPrivilegeCollection::PRIVILEGE_READ)) {
                return false;
            }
        }

        return true;
    }
}
