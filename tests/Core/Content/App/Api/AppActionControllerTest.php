<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Api;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Swag\SaasConnect\Test\AppSystemTestBehaviour;

class AppActionControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use AdminApiTestBehaviour;
    use AppSystemTestBehaviour;

    public function testGetActionsPerViewEmpty(): void
    {
        $url = '/api/v' . PlatformRequest::API_VERSION . '/app-system/action-button/product/listing';
        $this->getBrowser()->request('GET', $url);
        $response = json_decode($this->getBrowser()->getResponse()->getContent(), true);

        static::assertEquals(200, $this->getBrowser()->getResponse()->getStatusCode());
        static::assertArrayHasKey('actions', $response);
        static::assertEmpty($response['actions']);
    }

    public function testGetActionsPerView(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/../Manifest/_fixtures/test');
        $url = '/api/v' . PlatformRequest::API_VERSION . '/app-system/action-button/order/detail';
        $this->getBrowser()->request('GET', $url);

        static::assertEquals(200, $this->getBrowser()->getResponse()->getStatusCode());

        $result = json_decode($this->getBrowser()->getResponse()->getContent(), true);
        static::assertArrayHasKey('actions', $result);

        $result = $result['actions'];
        static::assertCount(1, $result);
        static::assertTrue(Uuid::isValid($result[0]['id']));
        unset($result[0]['id']);

        static::assertEquals([
            [
                'app' => 'SwagApp',
                'label' => [
                    'en-GB' => 'View Order',
                    'de-DE' => 'Zeige Bestellung'
                ],
                'action' => 'viewOrder',
                'url' => 'https://swag-test.com/your-order',
                'openNewTab' => true
            ]
        ], $result);
    }

    public function testRunAction(): void
    {
        $url = '/api/v' . PlatformRequest::API_VERSION . '/app-system/action-button/run/' . Uuid::randomHex();
        $this->getBrowser()->request('POST', $url);

        static::assertEquals(200, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
