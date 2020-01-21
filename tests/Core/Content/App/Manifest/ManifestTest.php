<?php declare(strict_types=1);

namespace Swag\SaasConect\Test\Core\Content\App\Manifest;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

class ManifestTest extends TestCase
{
    public function testCreateFromXml(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/_fixtures/test/manifest.xml');

        static::assertEquals(__DIR__ . '/_fixtures/test', $manifest->getPath());
        static::assertEquals([
            'name' => 'SwagApp',
            'label' => [
                'en-GB' => 'Swag App Test',
                'de-DE' => 'Swag App Test'
            ],
            'description' => [
                'en-GB' => 'Test for App System',
                'de-DE' => 'Test für das App System'
            ],
            'author' => 'shopware AG',
            'copyright' => '(c) by shopware AG',
            'version' => '1.0.0',
            'icon' => 'icon.png'
        ], $manifest->getMetadata());

        static::assertCount(2, $manifest->getAdmin()['actionButtons']);
        static::assertEquals([
            [
                'label' => [
                    'en-GB' => 'View Order',
                    'de-DE' => 'Zeige Bestellung',
                ],
                'action' => 'viewOrder',
                'entity' => 'order',
                'view' => 'detail',
                'url' => 'https://swag-test.com/your-order',
                'openNewTab' => true
            ],
            [
                'label' => [
                    'en-GB' => 'Do Stuff',
                ],
                'action' => 'doStuffWithProducts',
                'entity' => 'product',
                'view' => 'index',
                'url' => 'https://swag-test.com/do-stuff',
            ]
        ], $manifest->getAdmin()['actionButtons']);
    }
}
