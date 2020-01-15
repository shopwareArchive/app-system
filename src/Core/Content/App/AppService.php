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
     * @var EntityRepositoryInterface
     */
    private $actionButtonRepository;

    public function __construct(
        EntityRepositoryInterface $appRepository,
        EntityRepositoryInterface $actionButtonRepository,
        AppLoader $appLoader
    ) {
        $this->appRepository = $appRepository;
        $this->appLoader = $appLoader;
        $this->actionButtonRepository = $actionButtonRepository;
    }

    public function refreshApps(Context $context): void
    {
        $appsFromFileSystem = $this->appLoader->load();

        $appsFromDb = $this->getRegisteredApps($context);

        foreach ($appsFromFileSystem as $manifest) {
            // install
            if (!array_key_exists($manifest->getMetadata()['name'], $appsFromDb)) {
                $this->updateApp($manifest, $context);
                continue;
            }

            $app = $appsFromDb[$manifest->getMetadata()['name']];
            // update
            if (version_compare($manifest->getMetadata()['version'], $app['version']) > 0) {
                $this->updateApp($manifest, $context, $app['id']);
            }

            unset($appsFromDb[$manifest->getMetadata()['name']]);
        }

        $this->deleteNotFoundApps($appsFromDb, $context);
    }

    private function getRegisteredApps(Context $context): array
    {
        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $context)->getEntities();

        $appData = [];
        /** @var AppEntity $app */
        foreach ($apps as $app) {
            $appData[$app->getName()] = [
                'id' => $app->getId(),
                'version' => $app->getVersion()
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

    private function deleteNotFoundApps(array $toBeDeleted, Context $context): void
    {
        if (empty($toBeDeleted)) {
            return;
        }

        $toBeDeletedIds = array_map(function (array $app) {
            return ['id' => $app['id']];
        }, $toBeDeleted);

        $this->appRepository->delete(array_values($toBeDeletedIds), $context);
    }

    private function updateActions(array $actionButtons, string $appId, Context $context): void
    {
        $this->deleteExistingActions($appId, $context);

        if (!empty($actionButtons)) {
            $this->addActionButtons($actionButtons, $appId, $context);
        }
    }

    private function deleteExistingActions(string $appId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('appId', $appId));

        /** @var string[] $ids */
        $ids = $this->actionButtonRepository->searchIds($criteria, $context)->getIds();

        if (!empty($ids)) {
            $ids = array_map(function (string $id): array {
                return ['id' => $id];
            }, $ids);

            $this->actionButtonRepository->delete($ids, $context);
        }
    }

    private function addActionButtons(array $actionButtons, string $appId, Context $context): void
    {
        $actionButtons = array_map(function ($actionButton) use ($appId): array {
            $actionButton['appId'] = $appId;

            return $actionButton;
        }, $actionButtons);

        $this->actionButtonRepository->create($actionButtons, $context);
    }
}
