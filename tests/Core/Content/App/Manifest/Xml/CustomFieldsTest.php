<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldSet;

class CustomFieldsTest extends TestCase
{
    public function testFromXml(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/../_fixtures/test/manifest.xml');

        static::assertNotNull($manifest->getCustomFields());
        static::assertCount(1, $manifest->getCustomFields()->getCustomFieldSets());

        /** @var CustomFieldSet $customFieldSet */
        $customFieldSet = $manifest->getCustomFields()->getCustomFieldSets()[0];
        static::assertEquals('custom_field_test', $customFieldSet->getName());
        static::assertEquals([
            'en-GB' => 'Custom field test',
            'de-DE' => 'Zusatzfeld Test',
        ], $customFieldSet->getLabel());
        static::assertEquals(['product', 'customer'], $customFieldSet->getRelatedEntities());

        static::assertCount(0, $customFieldSet->getFields());
    }
}
