<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\SaasConnect\Core\Content\App\AppCollection;
use Swag\SaasConnect\Core\Content\App\AppEntity;

class AppLifecycleIterator
{
    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var AppLoader
     */
    private $appLoader;

    public function __construct(
        EntityRepositoryInterface $appRepository,
        AppLoader $appLoader
    ) {
        $this->appRepository = $appRepository;
        $this->appLoader = $appLoader;
    }

    public function iterate(AppLifecycleInterface $appLifecycle, Context $context): void
    {
        $appsFromFileSystem = $this->appLoader->load();
        $appsFromDb = $this->getRegisteredApps($context);

        foreach ($appsFromFileSystem as $manifest) {
            if (!array_key_exists($manifest->getMetadata()->getName(), $appsFromDb)) {
                $appLifecycle->install($manifest, $context);

                continue;
            }

            $app = $appsFromDb[$manifest->getMetadata()->getName()];
            if (version_compare($manifest->getMetadata()->getVersion(), $app['version']) > 0) {
                $appLifecycle->update($manifest, $app, $context);
            }

            unset($appsFromDb[$manifest->getMetadata()->getName()]);
        }

        foreach ($appsFromDb as $appName => $app) {
            $appLifecycle->delete($appName, $app, $context);
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getRegisteredApps(Context $context): array
    {
        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $context)->getEntities();

        $appData = [];
        /** @var AppEntity $app */
        foreach ($apps as $app) {
            $appData[$app->getName()] = [
                'id' => $app->getId(),
                'version' => $app->getVersion(),
                'roleId' => $app->getAclRoleId(),
            ];
        }

        return $appData;
    }
}
