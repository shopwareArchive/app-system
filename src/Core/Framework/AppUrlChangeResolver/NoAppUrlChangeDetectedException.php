<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\AppUrlChangeResolver;

class NoAppUrlChangeDetectedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('No APP_URL change was detected, cannot run AppUrlChange strategies.');
    }
}
