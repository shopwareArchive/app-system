<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Event;

use Shopware\Core\Framework\Api\Acl\Permission\AclPermissionCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Swag\SaasConnect\Core\Framework\Webhook\Hookable;
use Symfony\Contracts\EventDispatcher\Event;

class AppDeletedEvent extends Event implements ShopwareEvent, Hookable
{
    public const NAME = 'app_deleted';

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

    public function getName(): string
    {
        return self::NAME;
    }

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
    public function isAllowed(string $appId, AclPermissionCollection $permissions): bool
    {
        return $appId === $this->getAppId();
    }
}
