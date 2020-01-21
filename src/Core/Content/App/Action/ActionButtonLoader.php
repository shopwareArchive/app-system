<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Action;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton\ActionButtonCollection;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton\ActionButtonEntity;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButtonTranslation\ActionButtonTranslationCollection;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButtonTranslation\ActionButtonTranslationEntity;
use Swag\SaasConnect\Core\Content\App\AppEntity;

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

    public function loadActionButtonsForView(string $entity, string $view, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('entity', $entity),
            new EqualsFilter('view', $view)
        )->addAssociation('app')
        ->addAssociation('translations.language.locale');

        /** @var ActionButtonCollection $actionButtons */
        $actionButtons = $this->actionButtonRepository->search($criteria, $context)->getEntities();

        return $this->formatCollection($actionButtons);
    }

    private function formatCollection(ActionButtonCollection $actionButtons): array
    {
        return array_values(array_map(function (ActionButtonEntity $button): array {
            return [
                'app' => $button->getApp()->getName(),
                'id' => $button->getId(),
                'label' => $this->mapTranslatedLabels($button),
                'action' => $button->getAction(),
                'url' => $button->getUrl(),
                'openNewTab' => $button->isOpenNewTab()
            ];
        }, $actionButtons->getElements()));
    }

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
