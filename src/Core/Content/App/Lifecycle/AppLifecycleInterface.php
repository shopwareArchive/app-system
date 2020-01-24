<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle;

use Shopware\Core\Framework\Context;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

interface AppLifecycleInterface
{
    public function install(Manifest $manifest, Context $context): void;

    /**
     * @param array<string, string> $app
     */
    public function update(Manifest $manifest, array $app, Context $context): void;

    /**
     * @param array<string, string> $app
     */
    public function delete(string $appName, array $app, Context $context): void;
}
