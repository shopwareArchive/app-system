<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Storefront\Test\Controller\StorefrontControllerTestBehaviour;

/**
 * @group ThemeCompile
 */
class TemplateSystemIntegrationTest extends TestCase
{
    use IntegrationTestBehaviour;
    use StorefrontControllerTestBehaviour;
    use AppSystemTestBehaviour;
    use StorefrontAppRegistryTestBehaviour;
    use TwigResetCacheTestBehaviour;

    public function testTemplateChangesAreDisplayed(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/Core/Content/App/Manifest/_fixtures/test');

        $homepage = $this->request('GET', '/', []);

        static::assertEquals(200, $homepage->getStatusCode());
        static::assertStringContainsString('Built with <3 on Shopware as a Service', $homepage->getContent());
    }
}
