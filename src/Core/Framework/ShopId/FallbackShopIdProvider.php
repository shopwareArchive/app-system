<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\ShopId;

use Shopware\Core\Framework\Util\Random;

class FallbackShopIdProvider implements ShopIdProviderStrategy
{
    public function getName(): string
    {
        return 'fallback_shop_id_provider';
    }

    public function isSupported(): bool
    {
        return true;
    }

    public function getShopId(): string
    {
        return Random::getAlphanumericString(12);
    }
}
