<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldSet;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes\MediaSelectionField;
use Swag\SaasConnect\Test\CustomFieldTypeTestBehaviour;

class MediaSelectionFieldTest extends TestCase
{
    use IntegrationTestBehaviour;
    use CustomFieldTypeTestBehaviour;

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

    public function testToEntityArray(): void
    {
        $mediaSelectionField = $this->importCustomField(__DIR__ . '/_fixtures/media-selection-field.xml');

        static::assertEquals('test_media_selection_field', $mediaSelectionField->getName());
        static::assertEquals('text', $mediaSelectionField->getType());
        static::assertTrue($mediaSelectionField->isActive());
        static::assertEquals([
            'label' => [
                'en-GB' => 'Test media-selection field',
            ],
            'helpText' => [],
            'componentName' => 'sw-media-field',
            'customFieldType' => 'media',
            'customFieldPosition' => 1,
        ], $mediaSelectionField->getConfig());
    }
}
