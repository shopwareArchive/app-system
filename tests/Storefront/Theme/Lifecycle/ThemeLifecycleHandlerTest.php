<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Storefront\Theme\Lifecycle;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Storefront\Theme\ThemeCollection;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Storefront\Theme\Lifecycle\ThemeLifecycleHandler;
use Swag\SaasConnect\Test\AppSystemTestBehaviour;
use Swag\SaasConnect\Test\StorefrontAppRegistryTestBehaviour;

class ThemeLifecycleHandlerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use AppSystemTestBehaviour;
    use StorefrontAppRegistryTestBehaviour;

    /**
     * @var ThemeLifecycleHandler
     */
    private $lifecycleManager;

    /**
     * @var EntityRepositoryInterface
     */
    private $themeRepository;

    public function setUp(): void
    {
        $this->lifecycleManager = $this->getThemeLifecycleHandler();
        $this->themeRepository = $this->getContainer()->get('theme.repository');
    }

    public function testHandleInstall(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/../../_fixtures/theme/manifest.xml');

        $this->lifecycleManager->handleAppUpdate($manifest, Context::createDefaultContext());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', $manifest->getMetadata()->getName()));

        /** @var ThemeCollection $themes */
        $themes = $this->themeRepository->search($criteria, Context::createDefaultContext())->getEntities();

        static::assertCount(1, $themes);
        static::assertTrue($themes->first()->isActive());
    }

    public function testHandleUpdate(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/../../_fixtures/theme');
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/../../_fixtures/theme/manifest.xml');

        $this->lifecycleManager->handleAppUpdate($manifest, Context::createDefaultContext());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', $manifest->getMetadata()->getName()));

        /** @var ThemeCollection $themes */
        $themes = $this->themeRepository->search($criteria, Context::createDefaultContext())->getEntities();

        static::assertCount(1, $themes);
        static::assertTrue($themes->first()->isActive());
    }

    public function testHandleUninstallIfNotInstalled(): void
    {
        $this->lifecycleManager->handleUninstall('SwagTheme', Context::createDefaultContext());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', 'SwagTheme'));

        /** @var ThemeCollection $themes */
        $themes = $this->themeRepository->search($criteria, Context::createDefaultContext())->getEntities();

        static::assertCount(0, $themes);
    }

    public function testHandleUninstallDeactivatesTheme(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/../../_fixtures/theme');

        $this->lifecycleManager->handleUninstall('SwagTheme', Context::createDefaultContext());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', 'SwagTheme'));

        /** @var ThemeCollection $themes */
        $themes = $this->themeRepository->search($criteria, Context::createDefaultContext())->getEntities();

        static::assertCount(1, $themes);
        static::assertFalse($themes->first()->isActive());
    }

    private function getThemeLifecycleHandler(): ThemeLifecycleHandler
    {
        return $this->getContainer()->get(ThemeLifecycleHandler::class);
    }
}
