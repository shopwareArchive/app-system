<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Content\App\Manifest;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\ActionButton;

class ActionButtonTest extends TestCase
{
    /**
     * @var ActionButton
     */
    private $actionButton;

    public function setUp(): void
    {
        $this->actionButton = (new ActionButton())->assign([
            'action' => 'viewOrder',
            'label' => [
                'en-GB' => 'View Order',
                'de-DE' => 'Zeige Bestellung'
            ],
            'entity' => 'order',
            'view' => 'detail',
            'url' => 'https://swag-test.com/your-order',
            'openNewTab' => true,
        ]);
    }

    public function testIsJsonSerializable(): void
    {
        $expected = [
            'action' => 'viewOrder',
            'label' => [
                'en-GB' => 'View Order',
                'de-DE' => 'Zeige Bestellung'
            ],
            'entity' => 'order',
            'view' => 'detail',
            'url' => 'https://swag-test.com/your-order',
            'openNewTab' => true,
            'extensions' => []
        ];

        $json = json_encode($this->actionButton);

        static::assertEquals($expected, json_decode($json, true));
    }

    public function testGetters(): void
    {
        static::assertEquals('viewOrder', $this->actionButton->getAction());
        static::assertEquals('order', $this->actionButton->getEntity());
        static::assertEquals('detail', $this->actionButton->getView());
        static::assertEquals('https://swag-test.com/your-order', $this->actionButton->getUrl());
        static::assertEquals(true, $this->actionButton->isOpenNewTab());
        static::assertEquals([
            'en-GB' => 'View Order',
            'de-DE' => 'Zeige Bestellung'
        ], $this->actionButton->getLabels());
        static::assertEquals('View Order', $this->actionButton->getLabel('en-GB'));
        static::assertEquals('Zeige Bestellung', $this->actionButton->getLabel('de-DE'));
        static::assertNull($this->actionButton->getLabel('pl-PL'));
    }
}
