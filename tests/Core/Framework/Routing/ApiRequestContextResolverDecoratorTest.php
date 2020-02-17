<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\Routing;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Swag\SaasConnect\Test\AppSystemTestBehaviour;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class ApiRequestContextResolverDecoratorTest extends TestCase
{
    use IntegrationTestBehaviour;
    use AdminApiTestBehaviour;
    use AppSystemTestBehaviour;

    public function testCanReadWithPermission(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/../../Content/App/Manifest/_fixtures/test');

        $browser = $this->createClient();
        $this->authorizeBrowserWithIntegrationByAppName($this->getBrowser(), 'SwagApp');

        $browser->request('GET', '/api/v' . PlatformRequest::API_VERSION . '/product');

        static::assertEquals(200, $browser->getResponse()->getStatusCode());
    }

    public function testCantReadWithoutPermission(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/../../Content/App/Manifest/_fixtures/test');

        $browser = $this->createClient();
        $this->authorizeBrowserWithIntegrationByAppName($browser, 'SwagApp');

        $browser->request('GET', '/api/v' . PlatformRequest::API_VERSION . '/media');

        static::assertEquals(403, $browser->getResponse()->getStatusCode());
    }

    public function testCantReadWithoutAnyPermission(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/../../Content/App/Manifest/_fixtures/minimal');

        $browser = $this->createClient();
        $this->authorizeBrowserWithIntegrationByAppName($browser, 'SwagAppMinimal');

        $browser->request('GET', '/api/v' . PlatformRequest::API_VERSION . '/product');

        static::assertEquals(403, $browser->getResponse()->getStatusCode());
    }

    public function testCanNotWriteWithoutPermissions(): void
    {
        $productId = Uuid::randomHex();
        $context = Context::createDefaultContext();

        $this->loadAppsFromDir(__DIR__ . '/../../Content/App/Manifest/_fixtures/minimal');

        $browser = $this->createClient();
        $this->authorizeBrowserWithIntegrationByAppName($browser, 'SwagAppMinimal');

        $browser->request(
            'POST',
            '/api/v' . PlatformRequest::API_VERSION . '/product',
            [],
            [],
            [],
            \json_encode($this->getProductData($productId, $context))
        );

        static::assertEquals(403, $browser->getResponse()->getStatusCode());
    }

    public function testCanWriteWithPermissionsSet(): void
    {
        /** @var EntityRepositoryInterface $productRepository */
        $productRepository = $this->getContainer()->get('product.repository');
        $productId = Uuid::randomHex();
        $context = Context::createDefaultContext();

        $this->loadAppsFromDir(__DIR__ . '/../../Content/App/Manifest/_fixtures/test');

        $browser = $this->createClient();
        $this->authorizeBrowserWithIntegrationByAppName($browser, 'SwagApp');

        $browser->request(
            'POST',
            '/api/v' . PlatformRequest::API_VERSION . '/product',
            [],
            [],
            [],
            \json_encode($this->getProductData($productId, $context))
        );

        static::assertEquals(204, $browser->getResponse()->getStatusCode());

        $product = $productRepository->search(new Criteria(), $context)->getEntities()->get($productId);

        static::assertNotNull($product);
    }

    public function testItCanUpdateAnExistingProduct(): void
    {
        /** @var EntityRepositoryInterface $productRepository */
        $productRepository = $this->getContainer()->get('product.repository');
        $productId = Uuid::randomHex();
        $newName = 'i got a new name';
        $context = Context::createDefaultContext();

        $productRepository->create([$this->getProductData($productId, $context)], $context);

        $this->loadAppsFromDir(__DIR__ . '/../../Content/App/Manifest/_fixtures/test');

        $browser = $this->createClient();
        $this->authorizeBrowserWithIntegrationByAppName($browser, 'SwagApp');

        $browser->request(
            'PATCH',
            '/api/v' . PlatformRequest::API_VERSION . '/product/' . $productId,
            [],
            [],
            [],
            \json_encode([
                'name' => $newName,
            ])
        );

        static::assertEquals(204, $browser->getResponse()->getStatusCode());

        $product = $productRepository->search(new Criteria(), $context)->getEntities()->get($productId);

        static::assertNotNull($product);
        static::assertEquals($newName, $product->getName());
    }

    public function testDoesntAffectLoggedInUser(): void
    {
        $this->getBrowser()->request('GET', '/api/v' . PlatformRequest::API_VERSION . '/product');

        static::assertEquals(200, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testDoesntAffectIntegrationWithoutApp(): void
    {
        $browser = $this->getBrowserAuthenticatedWithIntegration();
        $browser->request('GET', '/api/v' . PlatformRequest::API_VERSION . '/product');

        static::assertEquals(200, $browser->getResponse()->getStatusCode());
    }

    private function authorizeBrowserWithIntegrationByAppName(KernelBrowser $browser, string $appName): void
    {
        /** @var Connection $connection */
        $connection = $this->getContainer()->get(Connection::class);

        $keys = $connection->fetchAssoc(
            '
            SELECT `access_key`, `access_token`
            FROM `integration`
            INNER JOIN `swag_app` ON `swag_app`.`integration_id` = `integration`.`id`
            WHERE `swag_app`.`name` = :appName',
            ['appName' => $appName]
        );

        if (!$keys) {
            throw new \RuntimeException('No integration found for app with name: ' . $appName);
        }

        $authPayload = [
            'grant_type' => 'client_credentials',
            'client_id' => $keys['access_key'],
            'client_secret' => $keys['access_token'],
        ];

        $browser->request('POST', '/api/oauth/token', $authPayload);

        $data = json_decode($browser->getResponse()->getContent(), true);

        if (!array_key_exists('access_token', $data)) {
            throw new \RuntimeException(
                'No token returned from API: ' . ($data['errors'][0]['detail'] ?? 'unknown error' . print_r($data, true))
            );
        }

        $browser->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['access_token']));
    }

    private function getProductData(string $productId, Context $context)
    {
        return [
            'id' => $productId,
            'name' => 'created by integration',
            'productNumber' => 'SWC-1000',
            'stock' => 100,
            'manufacturer' => [
                'name' => 'app creator',
            ],
            'price' => [
                [
                    'gross' => 100,
                    'net' => 200,
                    'linked' => false,
                    'currencyId' => $context->getCurrencyId(),
                ],
            ],
            'tax' => [
                'name' => 'luxury',
                'taxRate' => '25',
            ],
        ];
    }
}
