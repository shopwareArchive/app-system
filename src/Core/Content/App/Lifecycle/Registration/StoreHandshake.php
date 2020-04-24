<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Registration;

class StoreHandshake implements AppHandshakeInterface
{
    public function fetchUrl(): string
    {
        throw new \RuntimeException('Not implemented');
    }

    public function fetchAppProof(): string
    {
        throw new \RuntimeException('Not implemented');
    }
}
