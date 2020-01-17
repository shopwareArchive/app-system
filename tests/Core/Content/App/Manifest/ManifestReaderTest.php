<?php declare(strict_types=1);

namespace Swag\SaasConect\Test\Core\Content\App\Manifest;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\ManifestReader;

class ManifestReaderTest extends TestCase
{
    /**
     * @var ManifestReader
     */
    private $manifestReader;

    public function setUp(): void
    {
        $this->manifestReader = new ManifestReader();
    }

    public function testRead(): void
    {
        $manifest = $this->manifestReader->read(__DIR__ . '/_fixtures/test/manifest.xml');

        static::assertEquals([
            'metadata' => [
                'name' => 'SwagApp',
                'label' => [
                    'en-GB' => 'Swag App Test',
                    'de-DE' => 'Swag App Test',
                ],
                'description' => [
                    'en-GB' => 'Test for App System',
                    'de-DE' => 'Test für das App System',
                ],
                'author' => 'shopware AG',
                'copyright' => '(c) by shopware AG',
                'version' => '1.0.0',
                'icon' => 'icon.png',
            ],
            'admin' => [
                'actionButtons' => [
                    [
                        'action' => 'viewOrder',
                        'label' => [
                            'en-GB' => 'View Order',
                            'de-DE' => 'Zeige Bestellung',
                        ],
                        'entity' => 'order',
                        'view' => 'detail',
                        'url' => 'https://swag-test.com/your-order',
                        'openNewTab' => true,
                    ],
                    [
                        'action' => 'doStuffWithProducts',
                        'label' => [
                            'en-GB' => 'Do Stuff',
                        ],
                        'entity' => 'product',
                        'view' => 'index',
                        'url' => 'https://swag-test.com/do-stuff',
                    ],
                ],
            ],
            'permissions' => [
                'product' => 'create',
                'category' => 'delete',
                'product_manufacturer' => 'delete'
            ]
        ], $manifest);
    }
}
