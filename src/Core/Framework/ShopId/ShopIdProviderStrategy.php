<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\ShopId;

interface ShopIdProviderStrategy
{
    /**
     * @return string the name of the ShopIdProvider, under which the shopId will be stored
     */
    public function getName(): string;

    /**
     * @return bool if the provider is currently supported or not
     */
    public function isSupported(): bool;

    /**
     * @return string the shopId for this shop, will be called only once for each provider
     */
    public function getShopId(): string;
}
