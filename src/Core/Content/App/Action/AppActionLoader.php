<?php


namespace Swag\SaasConnect\Core\Content\App\Action;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton\ActionButtonEntity;
use Swag\SaasConnect\Core\Content\App\Exception\ActionNotFoundException;

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

    public function __construct(string $url, EntityRepository $actionButtonRepo)
    {
        $this->actionButtonRepo = $actionButtonRepo;
        $this->url = $url;
    }

    /**
     * @param array<string> $ids
     */
    public function loadAppAction(string $actionId, array $ids, Context $context): AppAction
    {
        $criteria = new Criteria([$actionId]);
        $criteria->addAssociation('app');

        $actionButton = $this->actionButtonRepo->search($criteria, $context);

        if ($actionButton->count() === 0) {
            throw new ActionNotFoundException();
        }
        /** @var ActionButtonEntity $action */
        $action = $actionButton->first();

        return new AppAction(
            $action->getUrl(),
            $this->url,
            $action->getApp()->getVersion(),
            $action->getEntity(),
            $action->getAction(),
            $ids
        );
    }
}
