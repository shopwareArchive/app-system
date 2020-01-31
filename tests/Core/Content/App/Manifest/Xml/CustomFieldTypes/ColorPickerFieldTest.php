<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldSet;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes\ColorPickerField;

class ColorPickerFieldTest extends TestCase
{
    public function testCreateFromXml(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/_fixtures/color-picker-field.xml');

        static::assertNotNull($manifest->getCustomFields());
        static::assertCount(1, $manifest->getCustomFields()->getCustomFieldSets());

        /** @var CustomFieldSet $customFieldSet */
        $customFieldSet = $manifest->getCustomFields()->getCustomFieldSets()[0];

        static::assertCount(1, $customFieldSet->getFields());

        $colorPickerField = $customFieldSet->getFields()[0];
        static::assertInstanceOf(ColorPickerField::class, $colorPickerField);
        static::assertEquals('test_color_picker_field', $colorPickerField->getName());
        static::assertEquals([
            'en-GB' => 'Test color-picker field',
        ], $colorPickerField->getLabel());
        static::assertEquals([], $colorPickerField->getHelpText());
        static::assertEquals(1, $colorPickerField->getPosition());
        static::assertFalse($colorPickerField->getRequired());
    }
}
