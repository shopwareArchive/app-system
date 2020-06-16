<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\ShopId;

use Swag\SaasConnect\Core\Content\App\Exception\SaasConnectException;

class NoSupportedShopIdProviderException extends \RuntimeException implements SaasConnectException
{
    public function __construct()
    {
        parent::__construct('No supported ShopIdProvider found.');
    }
}
