<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test;

use Shopware\Core\Framework\Context;
use Swag\SaasConnect\Core\Content\App\AppService;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLifecycle;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLifecycleIterator;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait AppSystemTestBehaviour
{
    abstract protected function getContainer(): ContainerInterface;

    protected function loadAppsFromDir(string $appDir, bool $activateApps = true): void
    {
        $appService = new AppService(
            new AppLifecycleIterator(
                $this->getContainer()->get('saas_app.repository'),
                new AppLoader($appDir)
            ),
            $this->getContainer()->get(AppLifecycle::class)
        );

        $appService->refreshApps($activateApps, Context::createDefaultContext());
    }
}
