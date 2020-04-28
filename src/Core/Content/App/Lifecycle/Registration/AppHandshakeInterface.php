<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Registration;

interface AppHandshakeInterface
{
    public function fetchUrl(): string;

    public function fetchAppProof(): string;
}
