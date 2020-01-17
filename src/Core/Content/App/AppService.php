<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

class AppService
{
    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var AppLoader
     */
    private $appLoader;

    /**
     * @var AppLifecycle
     */
    private $appLifecycle;

    public function __construct(
        EntityRepositoryInterface $appRepository,
        AppLifecycle $appLifecycle,
        AppLoader $appLoader
    ) {
        $this->appRepository = $appRepository;
        $this->appLoader = $appLoader;
        $this->appLifecycle = $appLifecycle;
    }

    public function refreshApps(Context $context): void
    {
        $appsFromFileSystem = $this->appLoader->load();

        $appsFromDb = $this->getRegisteredApps($context);

        foreach ($appsFromFileSystem as $manifest) {
            /** @var string $appName */
            $appName = $manifest->getMetadata()['name'];
            // install
            if (!array_key_exists($appName, $appsFromDb)) {
                $this->appLifecycle->install($manifest, $context);

                continue;
            }

            $app = $appsFromDb[$appName];
            // update
            /** @var string $currentVersion */
            $currentVersion = $manifest->getMetadata()['version'];
            if (version_compare($currentVersion, $app['version']) > 0) {
                $this->appLifecycle->update($manifest, $app['id'], $app['roleId'], $context);
            }

            unset($appsFromDb[$manifest->getMetadata()['name']]);
        }

        $this->deleteNotFoundApps($appsFromDb, $context);
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

    private function updateApp(Manifest $manifest, Context $context, ?string $id = null): void
    {
        $metadata = $manifest->getMetadata();
        $metadata['path'] = $manifest->getPath();

        if ($id) {
            $metadata['id'] = $id;
        }

        // ToDo handle import and saving of icons
        unset($metadata['icon']);

        $appWrittenEvent = $this->appRepository->upsert([$metadata], $context);
        /** @var EntityWrittenEvent $appEvents */
        $appEvents = $appWrittenEvent->getEventByEntityName(AppDefinition::ENTITY_NAME);
        $appId = $appEvents->getIds()[0];
        $this->updateActions($manifest->getAdmin()['actionButtons'], $appId, $context);
    }

    /**
     * @param array<string, array<string, string>> $toBeDeleted
     */
    private function deleteNotFoundApps(array $toBeDeleted, Context $context): void
    {
        foreach ($toBeDeleted as $app) {
            $this->appLifecycle->delete($app['id'], $context);
        }
    }
}
