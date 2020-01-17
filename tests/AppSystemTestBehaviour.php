<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test;

use Shopware\Core\Framework\Context;
use Swag\SaasConnect\Core\Content\App\AppLifecycle;
use Swag\SaasConnect\Core\Content\App\AppLoader;
use Swag\SaasConnect\Core\Content\App\AppService;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait AppSystemTestBehaviour
{
    abstract protected function getContainer(): ContainerInterface;

    protected function loadAppsFromDir(string $appDir): void
    {
        $appService = new AppService(
            $this->getContainer()->get('app.repository'),
            $this->getContainer()->get(AppLifecycle::class),
            new AppLoader($appDir)
        );

        $appService->refreshApps(Context::createDefaultContext());
    }
}
