<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use Shopware\Core\System\CustomField\CustomFieldTypes;

class DateTimeField extends CustomFieldType
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
     * @return array<string, string|array<string, string|array<string, bool>>>
     */
    protected function toEntityArray(): array
    {
        return [
            'type' => CustomFieldTypes::DATETIME,
            'config' => [
                'type' => 'date',
                'componentName' => 'sw-field',
                'customFieldType' => 'date',
                'config' => [
                    'time_24hr' => true,
                ],
                'dateType' => 'datetime',
            ],
        ];
    }
}
