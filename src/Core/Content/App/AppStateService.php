<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Event\AppActivatedEvent;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Event\AppDeactivatedEvent;
use Swag\SaasConnect\Storefront\Theme\Lifecycle\ThemeLifecycleHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AppStateService
{
    /**
     * @var EntityRepositoryInterface
     */
    private $appRepo;

    /**
     * @var ThemeLifecycleHandler
     */
    private $themeLifecycleHandler;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        EntityRepositoryInterface $appRepo,
        ThemeLifecycleHandler $themeLifecycleHandler,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->appRepo = $appRepo;
        $this->themeLifecycleHandler = $themeLifecycleHandler;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function activateApp(string $appId, Context $context): void
    {
        $criteria = new Criteria([$appId]);
        $criteria->addFilter(new EqualsFilter('active', false));
        /** @var AppEntity | null $app */
        $app = $this->appRepo->search($criteria, $context)->first();

        if (!$app) {
            return;
        }

        $this->appRepo->update([['id' => $appId, 'active' => true]], $context);
        $this->themeLifecycleHandler->handleAppActivation($app, $context);

        $this->eventDispatcher->dispatch(new AppActivatedEvent($appId, $context));
    }

    public function deactivateApp(string $appId, Context $context): void
    {
        $criteria = new Criteria([$appId]);
        $criteria->addFilter(new EqualsFilter('active', true));
        /** @var AppEntity | null $app */
        $app = $this->appRepo->search($criteria, $context)->first();

        if (!$app) {
            return;
        }

        $this->themeLifecycleHandler->handleUninstall($app->getName(), $context);
        $this->appRepo->update([['id' => $appId, 'active' => false]], $context);

        $this->eventDispatcher->dispatch(new AppDeactivatedEvent($appId, $context));
    }
}
