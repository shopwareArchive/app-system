<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Api;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;

class AppActionControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use AdminApiTestBehaviour;

    public function testGetActionsPerView(): void
    {
        $url = '/api/v' . PlatformRequest::API_VERSION . '/app-system/action-button/product/listing';
        $this->getBrowser()->request('GET', $url);

        static::assertEquals(200, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testRunAction(): void
    {
        $url = '/api/v' . PlatformRequest::API_VERSION . '/app-system/action-button/run/' . Uuid::randomHex();
        $this->getBrowser()->request('POST', $url);

        static::assertEquals(200, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
