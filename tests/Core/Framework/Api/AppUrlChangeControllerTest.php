<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\Api;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\PlatformRequest;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\AppUrlChangeResolverStrategy;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\MoveShopPermanentlyResolver;

class AppUrlChangeControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use AdminApiTestBehaviour;

    public function testGetAvailableStrategies(): void
    {
        $url = '/api/v' . PlatformRequest::API_VERSION . '/app-system/app-url-change/strategies';
        $this->getBrowser()->request('GET', $url);
        $response = json_decode($this->getBrowser()->getResponse()->getContent(), true);

        static::assertEquals(200, $this->getBrowser()->getResponse()->getStatusCode());

        $appUrlChangeResolver = $this->getContainer()->get(AppUrlChangeResolverStrategy::class);
        static::assertEquals($appUrlChangeResolver->getAvailableStrategies(), $response);
    }

    public function testResolveWithExistingStrategy(): void
    {
        $url = '/api/v' . PlatformRequest::API_VERSION . '/app-system/app-url-change/resolve';
        $this->getBrowser()->request(
            'POST',
            $url,
            [],
            [],
            [],
            json_encode(['strategy' => MoveShopPermanentlyResolver::STRATEGY_NAME])
        );
        $response = $this->getBrowser()->getResponse()->getContent();

        static::assertEquals(204, $this->getBrowser()->getResponse()->getStatusCode(), $response);
    }

    public function testResolveWithNotFoundStrategy(): void
    {
        $url = '/api/v' . PlatformRequest::API_VERSION . '/app-system/app-url-change/resolve';
        $this->getBrowser()->request(
            'POST',
            $url,
            [],
            [],
            [],
            json_encode(['strategy' => 'test'])
        );
        $response = json_decode($this->getBrowser()->getResponse()->getContent(), true);

        static::assertEquals(400, $this->getBrowser()->getResponse()->getStatusCode());

        static::assertCount(1, $response['errors']);
        static::assertEquals('Unable to find AppUrlChangeResolver with name: "test".', $response['errors'][0]['detail']);
    }

    public function testResolveWithoutStrategy(): void
    {
        $url = '/api/v' . PlatformRequest::API_VERSION . '/app-system/app-url-change/resolve';
        $this->getBrowser()->request(
            'POST',
            $url
        );
        $response = json_decode($this->getBrowser()->getResponse()->getContent(), true);

        static::assertEquals(400, $this->getBrowser()->getResponse()->getStatusCode());

        static::assertCount(1, $response['errors']);
        static::assertEquals('Parameter "strategy" is missing.', $response['errors'][0]['detail']);
    }
}
