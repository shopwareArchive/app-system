<?php declare(strict_types=1);

namespace Swag\SaasConnect\Storefront\Subscriber;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Swag\SaasConnect\Core\Framework\ShopId\AppUrlChangeDetectedException;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StorefrontRenderSubscriber implements EventSubscriberInterface
{
    /**
     * @var ShopIdProvider
     */
    private $shopIdProvider;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    public function __construct(ShopIdProvider $shopIdProvider, EntityRepositoryInterface $appRepository)
    {
        $this->shopIdProvider = $shopIdProvider;
        $this->appRepository = $appRepository;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorefrontRenderEvent::class => 'addParameters',
        ];
    }

    public function addParameters(StorefrontRenderEvent $event): void
    {
        if (!$this->hasActiveApps($event->getContext())) {
            return;
        }

        try {
            $shopId = $this->shopIdProvider->getShopId();
        } catch (AppUrlChangeDetectedException $e) {
            return;
        }

        $event->setParameter('swagShopId', $shopId);
    }

    private function hasActiveApps(Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));

        $result = $this->appRepository->searchIds($criteria, $context);

        return $result->getTotal() > 0;
    }
}
