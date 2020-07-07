<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Action;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton\ActionButtonCollection;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton\ActionButtonEntity;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButtonTranslation\ActionButtonTranslationEntity;

class ActionButtonLoader
{
    /**
     * @var EntityRepositoryInterface
     */
    private $actionButtonRepository;

    public function __construct(EntityRepositoryInterface $actionButtonRepository)
    {
        $this->actionButtonRepository = $actionButtonRepository;
    }

    /**
     * @return array<array<string|array|bool|null>>
     */
    public function loadActionButtonsForView(string $entity, string $view, Context $context): array
    {
        $criteria = new Criteria();
        $criteria
            ->addAssociation('app')
            ->addAssociation('translations.language.locale')
            ->addFilter(
                new EqualsFilter('entity', $entity),
                new EqualsFilter('view', $view),
                new EqualsFilter('app.active', true));

        /** @var ActionButtonCollection $actionButtons */
        $actionButtons = $this->actionButtonRepository->search($criteria, $context)->getEntities();

        return $this->formatCollection($actionButtons);
    }

    /**
     * @return array<array<string|array|bool|null>>
     */
    private function formatCollection(ActionButtonCollection $actionButtons): array
    {
        return array_values(array_map(function (ActionButtonEntity $button): array {
            return [
                'app' => $button->getApp()->getName(),
                'id' => $button->getId(),
                'label' => $this->mapTranslatedLabels($button),
                'action' => $button->getAction(),
                'url' => $button->getUrl(),
                'openNewTab' => $button->isOpenNewTab(),
                'icon' => $button->getApp()->getIcon(),
            ];
        }, $actionButtons->getElements()));
    }

    /**
     * @return array<string, string>
     */
    private function mapTranslatedLabels(ActionButtonEntity $button): array
    {
        $labels = [];

        /** @var ActionButtonTranslationEntity $translation */
        foreach ($button->getTranslations() as $translation) {
            $labels[$translation->getLanguage()->getLocale()->getCode()] = $translation->getLabel();
        }

        return $labels;
    }
}
