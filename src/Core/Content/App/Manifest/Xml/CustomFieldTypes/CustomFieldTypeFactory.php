<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use Swag\SaasConnect\Core\Content\App\Exception\CustomFieldTypeNotFoundException;

class CustomFieldTypeFactory
{
    private const TAG_TO_CLASS_MAPPING = [
        'int' => IntField::class,
        'float' => FloatField::class,
        'text' => TextField::class,
        'text-area' => TextAreaField::class,
        'bool' => BoolField::class,
        'datetime' => DateTimeField::class,
        'single-select' => SingleSelectField::class,
        'multi-select' => MultiSelectField::class,
        'color-picker' => ColorPickerField::class,
        'media-selection' => MediaSelectionField::class,
    ];

    public static function createFromXml(\DOMElement $element): CustomFieldType
    {
        $fieldClass = self::TAG_TO_CLASS_MAPPING[$element->tagName] ?? null;

        if (!$fieldClass) {
            throw new CustomFieldTypeNotFoundException($element->tagName);
        }

        return $fieldClass::fromXml($element);
    }
}
