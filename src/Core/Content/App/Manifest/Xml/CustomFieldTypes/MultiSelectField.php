<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes;

class MultiSelectField extends SingleSelectField
{
    public static function fromXml(\DOMElement $element): CustomFieldType
    {
        return new self(self::parseSelect($element));
    }
}
