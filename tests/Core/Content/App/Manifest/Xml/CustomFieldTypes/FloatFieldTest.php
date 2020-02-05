<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldSet;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes\FloatField;

class FloatFieldTest extends TestCase
{
    public function testCreateFromXml(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/_fixtures/float-field.xml');

        static::assertNotNull($manifest->getCustomFields());
        static::assertCount(1, $manifest->getCustomFields()->getCustomFieldSets());

        /** @var CustomFieldSet $customFieldSet */
        $customFieldSet = $manifest->getCustomFields()->getCustomFieldSets()[0];

        static::assertCount(1, $customFieldSet->getFields());

        $floatField = $customFieldSet->getFields()[0];
        static::assertInstanceOf(FloatField::class, $floatField);
        static::assertEquals('test_float_field', $floatField->getName());
        static::assertEquals([
            'en-GB' => 'Test float field',
            'de-DE' => 'Test Kommazahlenfeld',
        ], $floatField->getLabel());
        static::assertEquals(['en-GB' => 'This is an float field.'], $floatField->getHelpText());
        static::assertEquals(2, $floatField->getPosition());
        static::assertEquals(2.2, $floatField->getSteps());
        static::assertEquals(0.5, $floatField->getMin());
        static::assertEquals(1.6, $floatField->getMax());
        static::assertEquals(['en-GB' => 'Enter an float...'], $floatField->getPlaceholder());
        static::assertFalse($floatField->getRequired());
    }
}
