<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Registration;

use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

class HandshakeFactory
{
    /**
     * @var string
     */
    private $shopUrl;

    public function __construct(string $shopUrl)
    {
        $this->shopUrl = $shopUrl;
    }

    public function create(Manifest $manifest): AppHandshakeInterface
    {
        $metadata = $manifest->getMetadata();
        $privateSecret = $metadata->getSecret();
        if ($privateSecret) {
            return new PrivateHandshake(
                $this->shopUrl,
                $privateSecret,
                $metadata->getRegistrationUrl(),
                $metadata->getName());
        }

        return new StoreHandshake();
    }
}
