<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\AppUrlChangeResolver;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
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
class MoveShopPermanentlyResolver extends AppUrlChangeResolver
{
    public const STRATEGY_NAME = 'MoveShopPermanently';

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(
        AppLoaderInterface $appLoader,
        EntityRepositoryInterface $appRepository,
        AppRegistrationService $registrationService,
        SystemConfigService $systemConfigService
    ) {
        parent::__construct($appLoader, $appRepository, $registrationService);

        $this->systemConfigService = $systemConfigService;
    }

    public function getName(): string
    {
        return self::STRATEGY_NAME;
    }

    public function getDescription(): string
    {
        return 'Use this URL for communicating with installed apps, this will disable communication to apps on the old
        URLs installation, but the app-data from the old installation will be available in this installation.';
    }

    public function resolve(Context $context): void
    {
        /** @var array<string, string>  $shopIdConfig */
        $shopIdConfig = $this->systemConfigService->get(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY);
        $shopId = $shopIdConfig['value'];

        $this->systemConfigService->set(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY, [
            'app_url' => $_SERVER['APP_URL'],
            'value' => $shopId,
        ]);

        $this->forEachInstalledApp($context, function (Manifest $manifest, AppEntity $app, Context $context): void {
            $this->reRegisterApp($manifest, $app, $context);
        });

        $this->systemConfigService->delete(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY);
    }
}
