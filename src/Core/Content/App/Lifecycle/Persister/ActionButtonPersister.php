<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Persister;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton\ActionButtonCollection;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton\ActionButtonEntity;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\ActionButton;

class ActionButtonPersister
{
    /**
     * @var EntityRepositoryInterface
     */
    private $actionButtonRepository;

    public function __construct(EntityRepositoryInterface $actionButtonRepository)
    {
        $this->actionButtonRepository = $actionButtonRepository;
    }

    public function updateActions(Manifest $manifest, string $appId, Context $context): void
    {
        $existingActionButtons = $this->getExistingActionButtons($appId, $context);

        $actionButtons = $manifest->getAdmin() ? $manifest->getAdmin()->getActionButtons() : [];
        $upserts = [];
        /** @var ActionButton $actionButton */
        foreach ($actionButtons as $actionButton) {
            $payload = $actionButton->toArray();
            $payload['appId'] = $appId;

            /** @var ActionButtonEntity|null $existing */
            $existing = $existingActionButtons->filterByProperty('action', $actionButton->getAction())->first();
            if ($existing) {
                $payload['id'] = $existing->getId();
                $existingActionButtons->remove($existing->getId());
            }

            $upserts[] = $payload;
        }

        if (!empty($upserts)) {
            $this->actionButtonRepository->upsert($upserts, $context);
        }

        $this->deleteOldActions($existingActionButtons, $context);
    }

    private function deleteOldActions(ActionButtonCollection $toBeRemoved, Context $context): void
    {
        /** @var array<string> $ids */
        $ids = $toBeRemoved->getIds();

        if (!empty($ids)) {
            $ids = array_map(static function (string $id): array {
                return ['id' => $id];
            }, array_values($ids));

            $this->actionButtonRepository->delete($ids, $context);
        }
    }

    private function getExistingActionButtons(string $appId, Context $context): ActionButtonCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('appId', $appId));

        /** @var ActionButtonCollection $actionButtons */
        $actionButtons = $this->actionButtonRepository->search($criteria, $context)->getEntities();

        return $actionButtons;
    }
}
