<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\AppUrlChangeResolver;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\SaasConnect\Core\Content\App\AppCollection;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;
use Swag\SaasConnect\Storefront\Theme\Lifecycle\ThemeLifecycleHandler;

/**
 * Resolver used when apps should be uninstalled
 * and the shopId should be regenerated, meaning the old shops and old apps work like before
 * apps in the current installation will be uninstalled without informing them about that (as they still run on the old installation)
 */
class UninstallAppsResolver implements AppUrlChangeResolverInterface
{
    public const STRATEGY_NAME = 'UninstallApps';

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var ThemeLifecycleHandler
     */
    private $themeLifecycleHandler;

    public function __construct(
        EntityRepositoryInterface $appRepository,
        SystemConfigService $systemConfigService,
        ThemeLifecycleHandler $themeLifecycleHandler
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->appRepository = $appRepository;
        $this->themeLifecycleHandler = $themeLifecycleHandler;
    }

    public function getName(): string
    {
        return self::STRATEGY_NAME;
    }

    public function getDescription(): string
    {
        return 'Uninstall all apps on this URL, so app communication on the old URLs installation keeps
        working like before.';
    }

    public function resolve(Context $context): void
    {
        $this->systemConfigService->delete(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY);

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $context)->getEntities();

        foreach ($apps as $app) {
            $this->themeLifecycleHandler->handleUninstall($app->getName(), $context);
            $this->appRepository->delete([['id' => $app->getId()]], $context);
        }

        $this->systemConfigService->delete(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY);
    }
}
