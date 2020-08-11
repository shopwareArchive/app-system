<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\AppUrlChangeResolver;

use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\SaasConnect\Core\Content\App\AppCollection;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLoaderInterface;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\AppRegistrationService;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;

/**
 * Resolver used when shop is moved from one URL to another
 * and the shopId (and the data in the app backends associated with it) should be kept
 *
 * Will run through the registration process for all apps again
 * with the new appUrl so the apps can save the new URL and generate new Secrets
 * that way communication from the old shop to the app backend will be blocked in the future
 */
class MoveShopPermanentlyResolver implements AppUrlChangeResolverInterface
{
    public const STRATEGY_NAME = 'MoveShopPermanently';

    /**
     * @var AppLoaderInterface
     */
    private $appLoader;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var AppRegistrationService
     */
    private $registrationService;

    public function __construct(
        AppLoaderInterface $appLoader,
        EntityRepositoryInterface $appRepository,
        SystemConfigService $systemConfigService,
        AppRegistrationService $registrationService
    ) {
        $this->appLoader = $appLoader;
        $this->appRepository = $appRepository;
        $this->systemConfigService = $systemConfigService;
        $this->registrationService = $registrationService;
    }

    public function getName(): string
    {
        return self::STRATEGY_NAME;
    }

    public function resolve(Context $context): void
    {
        $shopId = $this->systemConfigService->get(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY)['value'];

        $this->systemConfigService->set(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY, [
            'app_url' => $_SERVER['APP_URL'],
            'value' => $shopId,
        ]);

        $this->registerAppsWithNewUrl($context);

        $this->systemConfigService->delete(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY);
    }

    private function registerAppsWithNewUrl(Context $context): void
    {
        $manifests = $this->appLoader->load();
        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $context)->getEntities();

        /** @var Manifest $manifest */
        foreach ($manifests as $manifest) {
            $app = $this->getAppForManifest($manifest, $apps);

            if (!$app || !$manifest->getSetup()) {
                continue;
            }

            $this->registerAppWithNewUrl($manifest, $app, $context);
        }
    }

    private function getAppForManifest(Manifest $manifest, AppCollection $installedApps): ?AppEntity
    {
        $matchedApps = $installedApps->filter(static function (AppEntity $installedApp) use ($manifest): bool {
            return $installedApp->getName() === $manifest->getMetadata()->getName();
        });

        return $matchedApps->first();
    }

    private function registerAppWithNewUrl(Manifest $manifest, AppEntity $app, Context $context): void
    {
        $secret = AccessKeyHelper::generateSecretAccessKey();

        $this->appRepository->update([
            [
                'id' => $app->getId(),
                'integration' => [
                    'id' => $app->getIntegrationId(),
                    'accessKey' => AccessKeyHelper::generateAccessKey('integration'),
                    'secretAccessKey' => $secret,
                ],
            ],
        ], $context);

        $this->registrationService->registerApp($manifest, $app->getId(), $secret, $context);
    }
}
