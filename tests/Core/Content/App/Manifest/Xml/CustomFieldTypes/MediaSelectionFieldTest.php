<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldSet;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes\MediaSelectionField;

class MediaSelectionFieldTest extends TestCase
{
    public function testCreateFromXml(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/_fixtures/media-selection-field.xml');

        static::assertNotNull($manifest->getCustomFields());
        static::assertCount(1, $manifest->getCustomFields()->getCustomFieldSets());

        /** @var CustomFieldSet $customFieldSet */
        $customFieldSet = $manifest->getCustomFields()->getCustomFieldSets()[0];

        static::assertCount(1, $customFieldSet->getFields());

        $mediaSelectionField = $customFieldSet->getFields()[0];
        static::assertInstanceOf(MediaSelectionField::class, $mediaSelectionField);
        static::assertEquals('test_media_selection_field', $mediaSelectionField->getName());
        static::assertEquals([
            'en-GB' => 'Test media-selection field',
        ], $mediaSelectionField->getLabel());
        static::assertEquals([], $mediaSelectionField->getHelpText());
        static::assertEquals(1, $mediaSelectionField->getPosition());
        static::assertFalse($mediaSelectionField->getRequired());
    }
}
