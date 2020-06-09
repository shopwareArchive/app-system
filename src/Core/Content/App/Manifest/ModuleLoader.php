<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Swag\SaasConnect\Core\Content\App\Aggregate\AppTranslation\AppTranslationEntity;
use Swag\SaasConnect\Core\Content\App\AppCollection;
use Swag\SaasConnect\Core\Content\App\AppEntity;

class ModuleLoader
{
    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var string
     */
    private $shopUrl;

    public function __construct(EntityRepositoryInterface $appRepository, string $shopUrl)
    {
        $this->appRepository = $appRepository;
        $this->shopUrl = $shopUrl;
    }

    /**
     * @return array<array<string, string|array<string, string>|array<array<string|array<string, string>>>>>
     */
    public function loadModules(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new NotFilter(
            MultiFilter::CONNECTION_AND,
            [new EqualsFilter('modules', '[]')]
        ))
            ->addAssociation('translations.language.locale');

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search($criteria, $context)->getEntities();

        return $this->formatPayload($apps);
    }

    /**
     * @return array<array<string, string|array<string, string>|array<array<string|array<string, string>>>>>
     */
    private function formatPayload(AppCollection $apps): array
    {
        $modules = [];

        /** @var AppEntity $app */
        foreach ($apps as $app) {
            $modules[] = [
                'name' => $app->getName(),
                'label' => $this->mapTranslatedLabels($app),
                'modules' => $this->formatModules($app),
            ];
        }

        return $modules;
    }

    /**
     * @return array<array<string|array<string, string>>>
     */
    private function formatModules(AppEntity $app): array
    {
        $modules = [];

        /** @var array<string|array<string, string>> $module */
        foreach ($app->getModules() as $module) {
            $date = new \DateTime();
            $queryString = 'shop-url=' . urlencode($this->shopUrl) . '&timestamp=' . $date->getTimestamp();
            /** @var string $secret */
            $secret = $app->getAppSecret();
            $signature = hash_hmac('sha256', $queryString, $secret);

            /** @var string $source */
            $source = $module['source'];

            $module['source'] = sprintf(
                '%s?%s&shopware-shop-signature=%s',
                $source,
                $queryString,
                $signature
            );

            $modules[] = $module;
        }

        return $modules;
    }

    /**
     * @return array<string, string>
     */
    private function mapTranslatedLabels(AppEntity $app): array
    {
        $labels = [];

        /** @var AppTranslationEntity $translation */
        foreach ($app->getTranslations() as $translation) {
            $labels[$translation->getLanguage()->getLocale()->getCode()] = $translation->getLabel();
        }

        return $labels;
    }
}
