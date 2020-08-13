<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\AppUrlChangeResolver;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLoaderInterface;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Event\AppInstalledEvent;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\AppRegistrationService;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Resolver used when apps should be reinstalled
 * and the shopId should be regenerated, meaning the old shops and old apps work like before
 * apps in the current installation may lose historical data
 *
 * Will run through the registration process for all apps again
 * with the new appUrl and new shopId and throw installed events for every app
 */
class ReinstallAppsResolver extends AppUrlChangeResolver
{
    public const STRATEGY_NAME = 'ReinstallApps';

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        AppLoaderInterface $appLoader,
        EntityRepositoryInterface $appRepository,
        AppRegistrationService $registrationService,
        SystemConfigService $systemConfigService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($appLoader, $appRepository, $registrationService);

        $this->systemConfigService = $systemConfigService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getName(): string
    {
        return self::STRATEGY_NAME;
    }

    public function getDescription(): string
    {
        return 'Reinstall all apps anew for the new URL, so app communication on the old URLs installation keeps
        working like before. App-data from the old installation will not be available in this installation.';
    }

    public function resolve(Context $context): void
    {
        $this->systemConfigService->delete(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY);

        $this->forEachInstalledApp($context, function (Manifest $manifest, AppEntity $app, Context $context): void {
            $this->reRegisterApp($manifest, $app, $context);
            $this->eventDispatcher->dispatch(
                new AppInstalledEvent($app->getId(), $manifest, $context)
            );
        });

        $this->systemConfigService->delete(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY);
    }
}
