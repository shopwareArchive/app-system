<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\ActionButton;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\Module;

class AdminTest extends TestCase
{
    public function testFromXml(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/../_fixtures/test/manifest.xml');

        static::assertNotNull($manifest->getAdmin());
        static::assertCount(2, $manifest->getAdmin()->getActionButtons());
        static::assertCount(2, $manifest->getAdmin()->getModules());

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

        /** @var Module $firstModule */
        $firstModule = $manifest->getAdmin()->getModules()[0];
        static::assertEquals('https://test.com', $firstModule->getSource());
        static::assertEquals('first-module', $firstModule->getName());
        static::assertEquals([
            'en-GB' => 'My first own module',
            'de-DE' => 'Mein erstes eigenes Modul',
        ], $firstModule->getLabel());

        /** @var Module $secondModule */
        $secondModule = $manifest->getAdmin()->getModules()[1];
        static::assertEquals('https://test.com/second', $secondModule->getSource());
        static::assertEquals('second-module', $secondModule->getName());
        static::assertEquals([
            'en-GB' => 'My second module',
        ], $secondModule->getLabel());
    }
}
