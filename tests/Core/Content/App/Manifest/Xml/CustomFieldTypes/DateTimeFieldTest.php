<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldSet;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes\DateTimeField;

class DateTimeFieldTest extends TestCase
{
    public function testCreateFromXml(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/_fixtures/date-time-field.xml');

        static::assertNotNull($manifest->getCustomFields());
        static::assertCount(1, $manifest->getCustomFields()->getCustomFieldSets());

        /** @var CustomFieldSet $customFieldSet */
        $customFieldSet = $manifest->getCustomFields()->getCustomFieldSets()[0];

        static::assertCount(1, $customFieldSet->getFields());

        $dateTimeField = $customFieldSet->getFields()[0];
        static::assertInstanceOf(DateTimeField::class, $dateTimeField);
        static::assertEquals('test_datetime_field', $dateTimeField->getName());
        static::assertEquals([
            'en-GB' => 'Test datetime field',
        ], $dateTimeField->getLabel());
        static::assertEquals([], $dateTimeField->getHelpText());
        static::assertEquals(1, $dateTimeField->getPosition());
        static::assertFalse($dateTimeField->getRequired());
    }
}
