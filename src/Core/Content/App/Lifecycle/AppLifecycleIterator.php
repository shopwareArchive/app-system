<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\SaasConnect\Core\Content\App\AppCollection;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Swag\SaasConnect\Core\Content\App\Exception\SaasConnectException;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

class AppLifecycleIterator
{
    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var AppLoaderInterface
     */
    private $appLoader;

    public function __construct(
        EntityRepositoryInterface $appRepository,
        AppLoaderInterface $appLoader
    ) {
        $this->appRepository = $appRepository;
        $this->appLoader = $appLoader;
    }

    /**
     * @return array<Manifest>
     */
    public function iterate(AppLifecycleInterface $appLifecycle, Context $context): array
    {
        $appsFromFileSystem = $this->appLoader->load();
        $installedApps = $this->getRegisteredApps($context);

        $successfulUpdates = [];
        $fails = [];
        foreach ($appsFromFileSystem as $manifest) {
            try {
                if (!array_key_exists($manifest->getMetadata()->getName(), $installedApps)) {
                    $appLifecycle->install($manifest, $context);
                    $successfulUpdates[] = $manifest->getMetadata()->getName();

                    continue;
                }

                $app = $installedApps[$manifest->getMetadata()->getName()];
                if (version_compare($manifest->getMetadata()->getVersion(), $app['version']) > 0) {
                    $appLifecycle->update($manifest, $app, $context);
                }
                $successfulUpdates[] = $manifest->getMetadata()->getName();
            } catch (SaasConnectException $exception) {
                $fails[] = $manifest;

                continue;
            }
        }

        $this->deleteNotFoundAndFailedInstallApps($successfulUpdates, $appLifecycle, $context);

        return $fails;
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

    /**
     * @param array<string> $successfulUpdates
     */
    private function deleteNotFoundAndFailedInstallApps(
        array $successfulUpdates,
        AppLifecycleInterface $appLifecycle,
        Context $context
    ): void {
        // refetch registered apps so we can remove apps where the installation failed
        $appsFromDb = $this->getRegisteredApps($context);
        foreach ($successfulUpdates as $app) {
            unset($appsFromDb[$app]);
        }
        foreach ($appsFromDb as $appName => $app) {
            $appLifecycle->delete($appName, $app, $context);
        }
    }
}
