<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Symfony\Contracts\EventDispatcher\Event;

class AppInstalledEvent extends Event implements ShopwareEvent
{
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
}
