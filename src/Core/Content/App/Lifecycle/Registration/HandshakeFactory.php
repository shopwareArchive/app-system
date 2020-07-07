<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Registration;

use Swag\SaasConnect\Core\Content\App\Exception\AppRegistrationException;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Framework\ShopId\AppUrlChangeDetectedException;
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

    public function create(Manifest $manifest): AppHandshakeInterface
    {
        $setup = $manifest->getSetup();
        $privateSecret = $setup->getSecret();
        if ($privateSecret) {
            $metadata = $manifest->getMetadata();

            try {
                $shopId = $this->shopIdProvider->getShopId();
            } catch (AppUrlChangeDetectedException $e) {
                throw new AppRegistrationException(
                    'The app url changed. Please resolve how the apps should handle this change.'
                );
            }

            return new PrivateHandshake(
                $this->shopUrl,
                $privateSecret,
                $setup->getRegistrationUrl(),
                $metadata->getName(),
                $shopId
            );
        }

        return new StoreHandshake();
    }
}
