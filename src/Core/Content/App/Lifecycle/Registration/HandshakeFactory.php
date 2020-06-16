<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Registration;

use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;

class HandshakeFactory
{
    /**
     * @var string
     */
    private $shopUrl;

    /**
     * @var ShopIdProvider
     */
    private $shopIdProvider;

    public function __construct(string $shopUrl, ShopIdProvider $shopIdProvider)
    {
        $this->shopUrl = $shopUrl;
        $this->shopIdProvider = $shopIdProvider;
    }

    public function create(Manifest $manifest, string $appId): AppHandshakeInterface
    {
        $setup = $manifest->getSetup();
        $privateSecret = $setup->getSecret();
        if ($privateSecret) {
            $metadata = $manifest->getMetadata();

            return new PrivateHandshake(
                $this->shopUrl,
                $privateSecret,
                $setup->getRegistrationUrl(),
                $metadata->getName(),
                $this->shopIdProvider->getShopId($appId)
            );
        }

        return new StoreHandshake();
    }
}
