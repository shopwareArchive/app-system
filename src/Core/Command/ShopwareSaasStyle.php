<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;

class ShopwareSaasStyle extends ShopwareStyle
{
    public function confirmOrThrow(string $question, \Throwable $exception, bool $default = true): void
    {
        if (!$this->confirm($question, $default)) {
            throw $exception;
        }
    }
}
