<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

class PermissionTest extends TestCase
{
    public function testFromXml(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/../_fixtures/test/manifest.xml');

        static::assertNotNull($manifest->getPermissions());
        static::assertCount(5, $manifest->getPermissions()->getPermissions());
        static::assertEquals([
            'product' => ['create', 'update', 'delete'],
            'category' => ['delete'],
            'product_manufacturer' => ['create', 'delete'],
            'tax' => ['create'],
            'language' => ['read'],
        ], $manifest->getPermissions()->getPermissions());
    }
}
