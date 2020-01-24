<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
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
        $this->deleteExistingActions($appId, $context);

        $actionButtons = $manifest->getAdmin() ? $manifest->getAdmin()->getActionButtons() : [];
        $this->addActionButtons($actionButtons, $appId, $context);
    }

    private function deleteExistingActions(string $appId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('appId', $appId));

        /** @var array<string> $ids */
        $ids = $this->actionButtonRepository->searchIds($criteria, $context)->getIds();

        if (!empty($ids)) {
            $ids = array_map(static function (string $id): array {
                return ['id' => $id];
            }, $ids);

            $this->actionButtonRepository->delete($ids, $context);
        }
    }

    /**
     * @param array<ActionButton> $actionButtons
     */
    private function addActionButtons(array $actionButtons, string $appId, Context $context): void
    {
        if (empty($actionButtons)) {
            return;
        }

        $actionButtons = array_map(static function (ActionButton $actionButton) use ($appId): array {
            $actionButton = $actionButton->toArray();
            $actionButton['appId'] = $appId;

            return $actionButton;
        }, $actionButtons);

        $this->actionButtonRepository->create($actionButtons, $context);
    }
}
