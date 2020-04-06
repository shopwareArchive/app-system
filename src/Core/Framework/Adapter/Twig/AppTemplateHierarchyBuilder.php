<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Adapter\Twig;

use Shopware\Core\Framework\Adapter\Twig\NamespaceHierarchy\TemplateNamespaceHierarchyBuilderInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\TermsAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\TermsResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;

class AppTemplateHierarchyBuilder implements TemplateNamespaceHierarchyBuilderInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    public function __construct(
        EntityRepositoryInterface $appRepository
    ) {
        $this->appRepository = $appRepository;
    }

    /**
     * @param array<string> $namespaceHierarchy
     * @return array<string>
     */
    public function buildNamespaceHierarchy(array $namespaceHierarchy): array
    {
        return array_unique(array_merge($this->getAppTemplateNamespaces(), $namespaceHierarchy));
    }

    /**
     * @return array<string>
     */
    private function getAppTemplateNamespaces(): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new NotFilter(
            MultiFilter::CONNECTION_AND,
            [new EqualsFilter('saas_app.templates.id', null)]
        ));
        $criteria->addAggregation(new TermsAggregation('appNames', 'saas_app.name'));

        /** @var TermsResult $appNames */
        $appNames = $this->appRepository->aggregate($criteria, Context::createDefaultContext())->get('appNames');

        return $appNames->getKeys();
    }
}
