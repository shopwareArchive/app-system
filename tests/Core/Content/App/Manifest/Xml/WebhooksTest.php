<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\Webhook;

class WebhooksTest extends TestCase
{
    public function testFromXml(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/../_fixtures/test/manifest.xml');

        static::assertNotNull($manifest->getWebhooks());
        static::assertCount(2, $manifest->getWebhooks()->getWebhooks());

        /** @var Webhook $firstWebhook */
        $firstWebhook = $manifest->getWebhooks()->getWebhooks()[0];
        static::assertEquals('hook1', $firstWebhook->getName());
        static::assertEquals('https://test.com/hook', $firstWebhook->getUrl());
        static::assertEquals('checkout.customer.before.login', $firstWebhook->getEvent());

        /** @var Webhook $secondWebhook */
        $secondWebhook = $manifest->getWebhooks()->getWebhooks()[1];
        static::assertEquals('hook2', $secondWebhook->getName());
        static::assertEquals('https://test.com/hook2', $secondWebhook->getUrl());
        static::assertEquals('checkout.order.placed', $secondWebhook->getEvent());
    }
}
