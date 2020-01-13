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
            $appData = $manifest->getMetadata();
            $appData['path'] = $manifest->getPath();

            /** @var AppEntity|null $app */
            $app = $appsFromDb->filterByProperty('name', $manifest->getMetadata()['name'])->first();

            if (!$app) {
                $this->updateApp($appData, $manifest, $context);
                continue;
            }

            if (version_compare($manifest->getMetadata()['version'], $app->getVersion()) > 0) {
                $appData['id'] = $app->getId();

                $this->updateApp($appData, $manifest, $context);
            }

            $appsFromDb = $appsFromDb->filter(function (AppEntity $x) use ($app) {
                return $x->getId() !== $app->getId();
            });
        }

        $this->deleteNotFoundApps($appsFromDb, $context);
    }

    private function getRegisteredApps(Context $context): AppCollection
    {
        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $context)->getEntities();

        return $apps;
    }

    private function updateApp(array $metadata, Manifest $manifest, Context $context): void
    {
        // ToDo handle import and saving of icons
        unset($metadata['icon']);

        $appWrittenEvent = $this->appRepository->upsert([$metadata], $context);
        /** @var EntityWrittenEvent $appEvents */
        $appEvents = $appWrittenEvent->getEventByEntityName(AppDefinition::ENTITY_NAME);
        $appId = $appEvents->getIds()[0];
        $this->updateActions($manifest->getAdmin()['actionButtons'], $appId, $context);
    }

    private function deleteNotFoundApps(AppCollection $toBeDeleted, Context $context): void
    {
        if ($toBeDeleted->count() === 0) {
            return;
        }

        $toBeDeletedIds = $toBeDeleted->map(function (AppEntity $app) {
            return ['id' => $app->getId()];
        });

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
