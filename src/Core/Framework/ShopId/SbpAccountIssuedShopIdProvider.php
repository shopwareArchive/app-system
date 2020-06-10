<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\ShopId;

/**
 * Currently not implemented
 */
class SbpAccountIssuedShopIdProvider implements ShopIdProviderStrategy
{
    public function getName(): string
    {
        return 'sbp_account_issued_shop_id_provider';
    }

    public function isSupported(): bool
    {
        return false;
    }

    public function getShopId(): string
    {
        throw new \RuntimeException('Not implemented');
    }
}
