<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\AppUrlChangeResolver;

class AppUrlChangeResolverNotFoundException extends \RuntimeException
{
    public function __construct(string $strategyName)
    {
        parent::__construct('Unable to find AppUrlChangeResolver with name: "' . $strategyName . '".');
    }
}
