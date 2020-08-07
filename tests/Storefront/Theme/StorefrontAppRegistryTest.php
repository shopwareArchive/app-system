<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Storefront\Theme;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Storefront\Theme\StorefrontPluginConfiguration\StorefrontPluginConfiguration;
use Shopware\Storefront\Theme\StorefrontPluginRegistry;
use Swag\SaasConnect\Storefront\Theme\StorefrontAppRegistry;
use Swag\SaasConnect\Test\AppSystemTestBehaviour;
use Swag\SaasConnect\Test\StorefrontAppRegistryTestBehaviour;

/**
 * @group ThemeCompile
 */
class StorefrontAppRegistryTest extends TestCase
{
    use IntegrationTestBehaviour;
    use AppSystemTestBehaviour;
    use StorefrontAppRegistryTestBehaviour;

    public function testDecorationWorks(): void
    {
        static::assertInstanceOf(
            StorefrontAppRegistry::class,
            $this->getContainer()->get(StorefrontPluginRegistry::class)
        );
    }

    public function testConfigIsAddedIfItsATheme(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/../_fixtures/theme');

        $registry = $this->getContainer()
            ->get(StorefrontPluginRegistry::class);

        static::assertInstanceOf(
            StorefrontPluginConfiguration::class,
            $registry->getConfigurations()->getByTechnicalName('SwagTheme')
        );
    }

    public function testConfigIsNotAddedIfAppIsNotActive(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/../_fixtures/theme', false);

        $registry = $this->getContainer()
            ->get(StorefrontPluginRegistry::class);

        static::assertNull(
            $registry->getConfigurations()->getByTechnicalName('SwagTheme')
        );
    }

    public function testConfigIsAddedIfHasResourcesToCompile(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/../_fixtures/noThemeCustomCss');

        $registry = $this->getContainer()
            ->get(StorefrontPluginRegistry::class);

        static::assertInstanceOf(
            StorefrontPluginConfiguration::class,
            $registry->getConfigurations()->getByTechnicalName('SwagNoThemeCustomCss')
        );
    }

    public function testConfigIsNotAddedIfItsNotATheme(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/../_fixtures/noThemeNoCss');

        $registry = $this->getContainer()
            ->get(StorefrontPluginRegistry::class);

        static::assertNull(
            $registry->getConfigurations()->getByTechnicalName('SwagNoThemeNoCss')
        );
    }
}
