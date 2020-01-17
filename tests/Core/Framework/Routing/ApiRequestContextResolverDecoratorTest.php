<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\Routing;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
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

    public function authorizeBrowserWithIntegrationByAppName(KernelBrowser $browser, string $appName): void
    {
        /** @var Connection $connection */
        $connection = $this->getContainer()->get(Connection::class);

        $keys = $connection->fetchAssoc(
            '
            SELECT `access_key`, `access_token`
            FROM `integration`
            INNER JOIN `app` ON `app`.`integration_id` = `integration`.`id`
            WHERE `app`.`name` = :appName',
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
}
