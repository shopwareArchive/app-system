<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\AppUrlChangeResolver;

use Shopware\Core\Framework\Context;

interface AppUrlChangeResolverInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function resolve(Context $context): void;
}
