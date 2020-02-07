<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use Shopware\Core\System\CustomField\CustomFieldTypes;

class FloatField extends CustomFieldType
{
    protected const TRANSLATABLE_FIELDS = ['label', 'help-text', 'placeholder'];

    /**
     * @var array<string, string>
     */
    protected $placeholder = [];

    /**
     * @var float|null
     */
    protected $steps;

    /**
     * @var float|null
     */
    protected $min;

    /**
     * @var float|null
     */
    protected $max;

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

    public function getSteps(): ?float
    {
        return $this->steps;
    }

    public function getMin(): ?float
    {
        return $this->min;
    }

    public function getMax(): ?float
    {
        return $this->max;
    }

    /**
     * @return array<string, string|array<string, string|float|array<string, string>>>
     */
    protected function toEntityArray(): array
    {
        $entityArray = [
            'type' => CustomFieldTypes::FLOAT,
            'config' => [
                'type' => 'number',
                'placeholder' => $this->placeholder,
                'componentName' => 'sw-field',
                'customFieldType' => 'number',
                'numberType' => 'float',
            ],
        ];

        if ($this->max !== null) {
            $entityArray['config']['max'] = $this->max;
        }

        if ($this->min !== null) {
            $entityArray['config']['min'] = $this->min;
        }

        if ($this->steps !== null) {
            $entityArray['config']['step'] = $this->steps;
        }

        return $entityArray;
    }
}
