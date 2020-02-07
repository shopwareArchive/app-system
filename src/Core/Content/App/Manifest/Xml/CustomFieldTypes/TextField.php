<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use Shopware\Core\System\CustomField\CustomFieldTypes;

class TextField extends CustomFieldType
{
    protected const TRANSLATABLE_FIELDS = ['label', 'help-text', 'placeholder'];

    /**
     * @var array<string, string>
     */
    protected $placeholder = [];

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
        return new self(self::parse($element, self::TRANSLATABLE_FIELDS));
    }

    /**
     * @return array<string, string>
     */
    public function getPlaceholder(): array
    {
        return $this->placeholder;
    }

    /**
     * @return array<string, string|array<string, string|array<string, string>>>
     */
    protected function toEntityArray(): array
    {
        return [
            'type' => CustomFieldTypes::TEXT,
            'config' => [
                'type' => 'text',
                'placeholder' => $this->placeholder,
                'componentName' => 'sw-field',
                'customFieldType' => 'text',
            ],
        ];
    }
}
