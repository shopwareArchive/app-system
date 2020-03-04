<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\Plugin;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Plugin\BundleConfigGenerator;
use Shopware\Core\Framework\Plugin\BundleConfigGeneratorInterface;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\SaasConnect\Core\Framework\Plugin\AppConfigGenerator;
use Swag\SaasConnect\Test\AppSystemTestBehaviour;
use Swag\SaasConnect\Test\StorefrontAppRegistryTestBehaviour;

class AppConfigGeneratorTest extends TestCase
{
    use IntegrationTestBehaviour;
    use AppSystemTestBehaviour;
    use StorefrontAppRegistryTestBehaviour;

    /**
     * @var BundleConfigGeneratorInterface
     */
    private $configGenerator;

    public function setUp(): void
    {
        $this->configGenerator = $this->getContainer()->get(BundleConfigGenerator::class);
    }

    public function testGenerateWithThemeAndScriptAndStylePaths(): void
    {
        $appPath = __DIR__ . '/../../Content/App/Manifest/_fixtures/test/';
        $this->loadAppsFromDir($appPath);

        $configs = $this->configGenerator->getConfig();

        static::assertArrayHasKey('SwagApp', $configs);

        $appConfig = $configs['SwagApp'];
        static::assertEquals(
            $appPath,
            $this->getContainer()->getParameter('kernel.project_dir') . '/' . $appConfig['basePath']
        );
        static::assertEquals(['Resources/views'], $appConfig['views']);
        static::assertEquals('swag-app', $appConfig['technicalName']);
        static::assertArrayNotHasKey('administration', $appConfig);

        static::assertArrayHasKey('storefront', $appConfig);
        $storefrontConfig = $appConfig['storefront'];

        static::assertEquals('Resources/app/storefront/src', $storefrontConfig['path']);
        static::assertEquals('Resources/app/storefront/src/main.js', $storefrontConfig['entryFilePath']);
        static::assertNull($storefrontConfig['webpack']);
        static::assertEquals([
            __DIR__ . '/../../Content/App/Manifest/_fixtures/test/Resources/app/storefront/src/scss/base.scss',
            __DIR__ . '/../../Content/App/Manifest/_fixtures/test/Resources/app/storefront/src/scss/overrides.scss',
        ], $storefrontConfig['styleFiles']);
    }

    public function testGeneratedConfigStaysSameWithoutThemeRegistry(): void
    {
        $appPath = __DIR__ . '/../../Content/App/Manifest/_fixtures/test/';
        $this->loadAppsFromDir($appPath);

        $configs = $this->configGenerator->getConfig();

        static::assertArrayHasKey('SwagApp', $configs);
        $configWithThemeRegistry = $configs['SwagApp'];

        // if storefront bundle isn't installed storefrontPluginRegistry isn't available
        // result should be equal in this case
        $configurationGenerator = new AppConfigGenerator(
            new BundleConfigGenerator(
                $this->getContainer()->get('kernel'),
                $this->getContainer()->get('plugin.repository')
            ),
            $this->getContainer()->get('swag_app.repository'),
            null,
            $this->getContainer()->getParameter('kernel.project_dir')
        );
        $configs = $configurationGenerator->getConfig();

        static::assertArrayHasKey('SwagApp', $configs);
        $configWithoutThemeRegistry = $configs['SwagApp'];

        static::assertEquals($configWithThemeRegistry, $configWithoutThemeRegistry);
    }

    public function testGenerateWithWebpackConfig(): void
    {
        $appPath = __DIR__ . '/_fixtures/with-webpack/';
        $this->loadAppsFromDir($appPath);

        $configs = $this->configGenerator->getConfig();

        static::assertArrayHasKey('SwagTest', $configs);

        $appConfig = $configs['SwagTest'];
        static::assertEquals(
            $appPath,
            $this->getContainer()->getParameter('kernel.project_dir') . '/' . $appConfig['basePath']
        );
        static::assertEquals(['Resources/views'], $appConfig['views']);
        static::assertEquals('swag-test', $appConfig['technicalName']);
        static::assertArrayNotHasKey('administration', $appConfig);

        static::assertArrayHasKey('storefront', $appConfig);
        $storefrontConfig = $appConfig['storefront'];

        static::assertEquals('Resources/app/storefront/src', $storefrontConfig['path']);
        static::assertNull($storefrontConfig['entryFilePath']);
        static::assertEquals('Resources/app/storefront/build/webpack.config.js', $storefrontConfig['webpack']);
        static::assertEquals([], $storefrontConfig['styleFiles']);
    }
}
