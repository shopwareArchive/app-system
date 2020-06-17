<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Event;

use Shopware\Core\Framework\Api\Acl\Permission\AclPermissionCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Framework\Webhook\Hookable;
use Symfony\Contracts\EventDispatcher\Event;

class AppInstalledEvent extends Event implements ShopwareEvent, Hookable
{
    public const NAME = 'app_installed';

    /**
     * @var string
     */
    private $appId;

    /**
     * @var Manifest
     */
    private $app;

    /**
     * @var Context
     */
    private $context;

    public function __construct(string $appId, Manifest $app, Context $context)
    {
        $this->appId = $appId;
        $this->app = $app;
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

    public function getApp(): Manifest
    {
        return $this->app;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return array<string, string>
     */
    public function getWebhookPayload(): array
    {
        return [
            'appVersion' => $this->getApp()->getMetadata()->getVersion(),
        ];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function isAllowed(string $appId, AclPermissionCollection $permissions): bool
    {
        return $appId === $this->getAppId();
    }
}
