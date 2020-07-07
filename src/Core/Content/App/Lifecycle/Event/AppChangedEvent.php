<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Swag\SaasConnect\Core\Framework\Api\Acl\AclPrivilegeCollection;
use Swag\SaasConnect\Core\Framework\Webhook\Hookable;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AppChangedEvent extends Event implements ShopwareEvent, Hookable
{
    /**
     * @var string
     */
    private $appId;

    /**
     * @var Context
     */
    private $context;

    public function __construct(string $appId, Context $context)
    {
        $this->appId = $appId;
        $this->context = $context;
    }

    abstract public function getName(): string;

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint
     */
    public function getWebhookPayload(): array
    {
        return [];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function isAllowed(string $appId, AclPrivilegeCollection $permissions): bool
    {
        return $appId === $this->getAppId();
    }
}
