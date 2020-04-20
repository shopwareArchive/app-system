<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Symfony\Contracts\EventDispatcher\Event;

class AppDeletedEvent extends Event implements ShopwareEvent
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

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
