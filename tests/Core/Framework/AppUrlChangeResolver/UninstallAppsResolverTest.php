<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\AppUrlChangeResolver;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\UninstallAppsResolver;
use Swag\SaasConnect\Core\Framework\ShopId\AppUrlChangeDetectedException;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;
use Swag\SaasConnect\Storefront\Theme\Lifecycle\ThemeLifecycleHandler;
use Swag\SaasConnect\Test\AppSystemTestBehaviour;
use Swag\SaasConnect\Test\EnvTestBehaviour;
use Swag\SaasConnect\Test\SystemConfigTestBehaviour;

class UninstallAppsResolverTest extends TestCase
{
    use IntegrationTestBehaviour;
    use EnvTestBehaviour;
    use AppSystemTestBehaviour;
    use SystemConfigTestBehaviour;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var ShopIdProvider
     */
    private $shopIdProvider;

    /**
     * @var Context
     */
    private $context;

    public function setUp(): void
    {
        $this->systemConfigService = $this->getContainer()->get(SystemConfigService::class);
        $this->shopIdProvider = $this->getContainer()->get(ShopIdProvider::class);
        $this->context = Context::createDefaultContext();
    }

    public function testGetName(): void
    {
        $uninstallAppsResolver = $this->getContainer()->get(UninstallAppsResolver::class);

        static::assertEquals(
            UninstallAppsResolver::STRATEGY_NAME,
            $uninstallAppsResolver->getName()
        );
        static::assertIsString($uninstallAppsResolver->getDescription());
    }

    public function testItReRegistersInstalledApps(): void
    {
        $appDir = __DIR__ . '/../../Content/App/Manifest/_fixtures/test';
        $this->loadAppsFromDir($appDir);

        $app = $this->getInstalledApp($this->context);

        $shopId = $this->changeAppUrl();

        $themeLifecycleHandler = $this->createMock(ThemeLifecycleHandler::class);
        $themeLifecycleHandler->expects(static::once())
            ->method('handleUninstall')
            ->with(
                $app->getName(),
                static::isInstanceOf(Context::class)
            );

        $uninstallAppsResolver = new UninstallAppsResolver(
            $this->getContainer()->get('saas_app.repository'),
            $this->systemConfigService,
            $themeLifecycleHandler
        );

        $uninstallAppsResolver->resolve($this->context);

        static::assertNotEquals($shopId, $this->shopIdProvider->getShopId());
        static::assertNull($this->systemConfigService->get(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY));

        static::assertNull($this->getInstalledApp($this->context));
    }

    private function changeAppUrl(): string
    {
        $shopId = $this->shopIdProvider->getShopId();

        // create AppUrlChange
        $this->setEnvVars(['APP_URL' => 'https://test.new']);
        $wasThrown = false;

        try {
            $this->shopIdProvider->getShopId();
        } catch (AppUrlChangeDetectedException $e) {
            $wasThrown = true;
        }
        static::assertTrue($wasThrown);

        return $shopId;
    }

    private function getInstalledApp(Context $context): ?AppEntity
    {
        /** @var EntityRepositoryInterface $appRepo */
        $appRepo = $this->getContainer()->get('saas_app.repository');

        $criteria = new Criteria();
        $criteria->addAssociation('integration');
        $apps = $appRepo->search($criteria, $context);

        return $apps->first();
    }
}
