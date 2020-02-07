<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use Shopware\Core\System\CustomField\CustomFieldTypes;

class MediaSelectionField extends CustomFieldType
{
    /**
     * @param array<string, string|int|float|bool|array<string, string>> $data
     */
    private function __construct(array $data)
    {
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }
    }

    public static function fromXml(\DOMElement $element): CustomFieldType
    {
        return new self(self::parse($element));
    }

    /**
     * @return array<string, string|array<string, string>>
     */
    protected function toEntityArray(): array
    {
        return [
            'type' => CustomFieldTypes::TEXT,
            'config' => [
                'componentName' => 'sw-media-field',
                'customFieldType' => 'media',
            ],
        ];
    }
}
