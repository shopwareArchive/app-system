<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\AppUrlChangeResolver;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLoader;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\AppRegistrationService;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\MoveShopPermanentlyResolver;
use Swag\SaasConnect\Core\Framework\ShopId\AppUrlChangeDetectedException;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;
use Swag\SaasConnect\Test\AppSystemTestBehaviour;
use Swag\SaasConnect\Test\EnvTestBehaviour;
use Swag\SaasConnect\Test\SystemConfigTestBehaviour;

class MoveShopPermanentlyResolverTest extends TestCase
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
        $moveShopPermanentlyResolver = $this->getContainer()->get(MoveShopPermanentlyResolver::class);

        static::assertEquals(
            MoveShopPermanentlyResolver::STRATEGY_NAME,
            $moveShopPermanentlyResolver->getName()
        );
    }

    public function testItReRegistersInstalledApps(): void
    {
        $appDir = __DIR__ . '/../../Content/App/Manifest/_fixtures/test';
        $this->loadAppsFromDir($appDir);

        $app = $this->getInstalledApp($this->context);

        $shopId = $this->changeAppUrl();

        $registrationsService = $this->createMock(AppRegistrationService::class);
        $registrationsService->expects(static::once())
            ->method('registerApp')
            ->with(
                static::callback(static function (Manifest $manifest) use ($appDir): bool {
                    return $manifest->getPath() === $appDir;
                }),
                $app->getId(),
                static::isType('string'),
                static::isInstanceOf(Context::class)
            );

        $moveShopPermanentlyResolver = new MoveShopPermanentlyResolver(
            new AppLoader($appDir),
            $this->getContainer()->get('saas_app.repository'),
            $this->systemConfigService,
            $registrationsService
        );

        $moveShopPermanentlyResolver->resolve($this->context);

        static::assertEquals($shopId, $this->shopIdProvider->getShopId());
        static::assertNull($this->systemConfigService->get(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY));

        // assert secret access key changed
        $updatedApp = $this->getInstalledApp($this->context);
        static::assertNotEquals(
            $app->getIntegration()->getSecretAccessKey(),
            $updatedApp->getIntegration()->getSecretAccessKey()
        );
    }

    public function testItIgnoresAppsWithoutSetup(): void
    {
        $shopId = $this->changeAppUrl();

        $appDir = __DIR__ . '/../../Content/App/Lifecycle/Registration/_fixtures/no-setup';
        $this->loadAppsFromDir($appDir);

        $registrationsService = $this->createMock(AppRegistrationService::class);
        $registrationsService->expects(static::never())
            ->method('registerApp');

        $moveShopPermanentlyResolver = new MoveShopPermanentlyResolver(
            new AppLoader($appDir),
            $this->getContainer()->get('saas_app.repository'),
            $this->systemConfigService,
            $registrationsService
        );

        $moveShopPermanentlyResolver->resolve($this->context);

        static::assertEquals($shopId, $this->shopIdProvider->getShopId());
        static::assertNull($this->systemConfigService->get(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY));
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

    private function getInstalledApp(Context $context): AppEntity
    {
        /** @var EntityRepositoryInterface $appRepo */
        $appRepo = $this->getContainer()->get('saas_app.repository');

        $criteria = new Criteria();
        $criteria->addAssociation('integration');
        $apps = $appRepo->search($criteria, $context);
        static::assertEquals(1, $apps->getTotal());

        return $apps->first();
    }
}
