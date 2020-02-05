<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Exception\CustomFieldTypeNotFoundException;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes\CustomFieldTypeFactory;

class CustomFieldTypeFactoryTest extends TestCase
{
    public function testCreateFromXmlThrowsExceptionOnInvalidTag(): void
    {
        self::expectException(CustomFieldTypeNotFoundException::class);
        CustomFieldTypeFactory::createFromXml(new \DOMElement('invalid'));
    }
}
