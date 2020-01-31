<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldSet;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes\TextAreaField;

class TextAreaFieldTest extends TestCase
{
    public function testCreateFromXml(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/_fixtures/text-area-field.xml');

        static::assertNotNull($manifest->getCustomFields());
        static::assertCount(1, $manifest->getCustomFields()->getCustomFieldSets());

        /** @var CustomFieldSet $customFieldSet */
        $customFieldSet = $manifest->getCustomFields()->getCustomFieldSets()[0];

        static::assertCount(1, $customFieldSet->getFields());

        $textAreaField = $customFieldSet->getFields()[0];
        static::assertInstanceOf(TextAreaField::class, $textAreaField);
        static::assertEquals('test_text_area_field', $textAreaField->getName());
        static::assertEquals([
            'en-GB' => 'Test text-area field',
        ], $textAreaField->getLabel());
        static::assertEquals([], $textAreaField->getHelpText());
        static::assertEquals(1, $textAreaField->getPosition());
        static::assertFalse($textAreaField->getRequired());
    }
}
