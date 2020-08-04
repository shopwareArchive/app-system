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
use Swag\SaasConnect\Core\Framework\ShopId\AppUrlChangeDetectedException;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;

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

    /**
     * @var ShopIdProvider
     */
    private $shopIdProvider;

    public function __construct(
        EntityRepositoryInterface $appRepository,
        string $shopUrl,
        ShopIdProvider $shopIdProvider
    ) {
        $this->appRepository = $appRepository;
        $this->shopUrl = $shopUrl;
        $this->shopIdProvider = $shopIdProvider;
    }

    /**
     * @return array<array<string, string|array<string, string>|array<array<string|array<string, string>>>>>
     */
    public function loadModules(Context $context): array
    {
        $criteria = new Criteria();
        $containsModulesFilter = new NotFilter(
            MultiFilter::CONNECTION_AND,
            [new EqualsFilter('modules', '[]')]
        );
        $appActiveFilter = new EqualsFilter('active', true);
        $criteria->addFilter($containsModulesFilter, $appActiveFilter)
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
        $appModules = [];

        /** @var AppEntity $app */
        foreach ($apps as $app) {
            $modules = $this->formatModules($app);

            if (empty($modules)) {
                continue;
            }

            $appModules[] = [
                'name' => $app->getName(),
                'label' => $this->mapTranslatedLabels($app),
                'modules' => $modules,
            ];
        }

        return $appModules;
    }

    /**
     * @return array<array<string|array<string, string>>>
     */
    private function formatModules(AppEntity $app): array
    {
        $modules = [];

        /** @var array<string|array<string, string>> $module */
        foreach ($app->getModules() as $module) {
            $queryString = $this->generateQueryString();

            if ($queryString === null) {
                continue;
            }

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

    private function generateQueryString(): ?string
    {
        $date = new \DateTime();

        try {
            $shopId = $this->shopIdProvider->getShopId();
        } catch (AppUrlChangeDetectedException $e) {
            return null;
        }

        return sprintf(
            'shop-id=%s&shop-url=%s&timestamp=%s',
            $shopId,
            urlencode($this->shopUrl),
            $date->getTimestamp()
        );
    }
}
