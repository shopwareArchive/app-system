<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Action;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton\ActionButtonEntity;
use Swag\SaasConnect\Core\Content\App\Exception\ActionNotFoundException;
use Swag\SaasConnect\Core\Framework\ShopId\AppUrlChangeDetectedException;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;

class AppActionLoader
{
    /**
     * @var EntityRepository
     */
    private $actionButtonRepo;

    /**
     * @var string
     */
    private $url;

    /**
     * @var ShopIdProvider
     */
    private $shopIdProvider;

    public function __construct(string $url, EntityRepository $actionButtonRepo, ShopIdProvider $shopIdProvider)
    {
        $this->actionButtonRepo = $actionButtonRepo;
        $this->url = $url;
        $this->shopIdProvider = $shopIdProvider;
    }

    /**
     * @param array<string> $ids
     */
    public function loadAppAction(string $actionId, array $ids, Context $context): AppAction
    {
        $criteria = new Criteria([$actionId]);
        $criteria->addAssociation('app.integration');

        /** @var ActionButtonEntity | null $actionButton */
        $actionButton = $this->actionButtonRepo->search($criteria, $context)->first();

        if ($actionButton === null) {
            throw new ActionNotFoundException();
        }

        try {
            $shopId = $this->shopIdProvider->getShopId();
        } catch (AppUrlChangeDetectedException $e) {
            throw new ActionNotFoundException();
        }

        /** @var string $secret */
        $secret = $actionButton->getApp()->getAppSecret();

        return new AppAction(
            $actionButton->getUrl(),
            $this->url,
            $actionButton->getApp()->getVersion(),
            $actionButton->getEntity(),
            $actionButton->getAction(),
            $ids,
            $secret,
            $shopId
        );
    }
}
