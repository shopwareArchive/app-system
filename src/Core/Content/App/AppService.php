<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

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

    public function __construct(EntityRepositoryInterface $appRepository, AppLoader $appLoader)
    {
        $this->appRepository = $appRepository;
        $this->appLoader = $appLoader;
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
                $this->updateApp($appData, $context);
                continue;
            }

            if (version_compare($manifest->getMetadata()['version'], $app->getVersion()) > 0) {
                $appData['id'] = $app->getId();

                $this->updateApp($appData, $context);
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

    private function updateApp(array $metadata, Context $context): void
    {
        // ToDo handle import and saving of icons
        unset($metadata['icon']);

        $this->appRepository->upsert([$metadata], $context);
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
}
