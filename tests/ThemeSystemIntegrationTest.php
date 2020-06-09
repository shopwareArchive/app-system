<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Storefront\Test\Controller\StorefrontControllerTestBehaviour;
use Shopware\Storefront\Theme\ThemeEntity;
use Shopware\Storefront\Theme\ThemeService;

/**
 * @group ThemeCompile
 */
class ThemeSystemIntegrationTest extends TestCase
{
    use IntegrationTestBehaviour;
    use StorefrontControllerTestBehaviour;
    use AppSystemTestBehaviour;
    use StorefrontAppRegistryTestBehaviour;
    use TwigResetCacheTestBehaviour;

    public function testThemeChangesAreVisibleIfThemeIsActive(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/Storefront/_fixtures/theme');

        $this->activateThemeForDefaultStorefront('SwagTheme');

        $homepage = $this->request('GET', '/', []);

        static::assertEquals(200, $homepage->getStatusCode());
        static::assertStringContainsString('Built with <3 as a theme on Shopware as a Service', $homepage->getContent());
    }

    public function testThemeChangesAreNotVisibleIfThemeIsNotAssigned(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/Storefront/_fixtures/theme');

        $this->activateThemeForDefaultStorefront('Storefront');

        $homepage = $this->request('GET', '/', []);

        static::assertEquals(200, $homepage->getStatusCode());
        static::assertStringNotContainsString('Built with <3 as a theme on Shopware as a Service', $homepage->getContent());
    }

    private function activateThemeForDefaultStorefront(string $technicalName): void
    {
        $storefrontSalesChannelId = $this->getStorefrontSalesChannelId();
        $themeId = $this->getThemeIdByTechnicalName($technicalName);

        $themeService = $this->getContainer()->get(ThemeService::class);
        $themeService->assignTheme($themeId, $storefrontSalesChannelId, Context::createDefaultContext());
    }

    private function getStorefrontSalesChannelId(): string
    {
        /** @var EntityRepositoryInterface $salesChannelRepository */
        $salesChannelRepository = $this->getContainer()->get('sales_channel.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT));

        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $salesChannelRepository->search($criteria, Context::createDefaultContext())->first();

        return $salesChannel->getId();
    }

    private function getThemeIdByTechnicalName(string $technicalName): string
    {
        /** @var EntityRepositoryInterface $themeRepository */
        $themeRepository = $this->getContainer()->get('theme.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', $technicalName));

        /** @var ThemeEntity $theme */
        $theme = $themeRepository->search($criteria, Context::createDefaultContext())->first();

        return $theme->getId();
    }
}
