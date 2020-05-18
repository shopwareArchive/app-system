<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Registration;

use Swag\SaasConnect\Core\Content\App\Exception\AppRegistrationException;

class StoreHandshake implements AppHandshakeInterface
{
    public function fetchUrl(): string
    {
        throw new AppRegistrationException('Not implemented');
    }

    public function fetchAppProof(): string
    {
        throw new AppRegistrationException('Not implemented');
    }
}
