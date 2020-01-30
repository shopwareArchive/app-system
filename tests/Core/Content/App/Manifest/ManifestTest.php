<?php declare(strict_types=1);

namespace Swag\SaasConect\Test\Core\Content\App\Manifest;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\ActionButton;

class ManifestTest extends TestCase
{
    public function testCreateFromXml(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/_fixtures/test/manifest.xml');

        static::assertEquals(__DIR__ . '/_fixtures/test', $manifest->getPath());

        $metaData = $manifest->getMetadata();
        static::assertEquals('SwagApp', $metaData->getName());
        static::assertEquals('shopware AG', $metaData->getAuthor());
        static::assertEquals('(c) by shopware AG', $metaData->getCopyright());
        static::assertEquals('1.0.0', $metaData->getVersion());
        static::assertEquals('icon.png', $metaData->getIcon());

        static::assertEquals([
            'en-GB' => 'Swag App Test',
            'de-DE' => 'Swag App Test',
        ], $metaData->getLabel());
        static::assertEquals([
            'en-GB' => 'Test for App System',
            'de-DE' => 'Test für das App System',
        ], $metaData->getDescription());

        static::assertNotNull($manifest->getAdmin());
        static::assertCount(2, $manifest->getAdmin()->getActionButtons());

        /** @var ActionButton $firstActionButton */
        $firstActionButton = $manifest->getAdmin()->getActionButtons()[0];
        static::assertEquals('viewOrder', $firstActionButton->getAction());
        static::assertEquals('order', $firstActionButton->getEntity());
        static::assertEquals('detail', $firstActionButton->getView());
        static::assertEquals('https://swag-test.com/your-order', $firstActionButton->getUrl());
        static::assertTrue($firstActionButton->isOpenNewTab());
        static::assertEquals([
            'en-GB' => 'View Order',
            'de-DE' => 'Zeige Bestellung',
        ], $firstActionButton->getLabel());

        /** @var ActionButton $secondActionButton */
        $secondActionButton = $manifest->getAdmin()->getActionButtons()[1];
        static::assertEquals('doStuffWithProducts', $secondActionButton->getAction());
        static::assertEquals('product', $secondActionButton->getEntity());
        static::assertEquals('index', $secondActionButton->getView());
        static::assertEquals('https://swag-test.com/do-stuff', $secondActionButton->getUrl());
        static::assertFalse($secondActionButton->isOpenNewTab());
        static::assertEquals([
            'en-GB' => 'Do Stuff',
        ], $secondActionButton->getLabel());

        static::assertNotNull($manifest->getPermissions());
        static::assertCount(3, $manifest->getPermissions()->getPermissions());
        static::assertEquals([
            'product' => ['create', 'delete'],
            'category' => ['delete'],
            'product_manufacturer' => ['delete'],
        ], $manifest->getPermissions()->getPermissions());
    }
}
