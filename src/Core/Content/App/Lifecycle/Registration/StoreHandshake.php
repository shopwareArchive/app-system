<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Registration;

use Psr\Http\Message\RequestInterface;
use Swag\SaasConnect\Core\Content\App\Exception\AppRegistrationException;

class StoreHandshake implements AppHandshakeInterface
{
    public function assembleRequest(): RequestInterface
    {
        throw new AppRegistrationException('Not implemented');
    }

    public function fetchAppProof(): string
    {
        throw new AppRegistrationException('Not implemented');
    }
}
