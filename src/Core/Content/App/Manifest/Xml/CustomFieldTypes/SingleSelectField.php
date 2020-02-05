<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes;

class SingleSelectField extends CustomFieldType
{
    protected const TRANSLATABLE_FIELDS = ['label', 'help-text', 'placeholder'];

    /**
     * @var array<string, string>
     */
    protected $placeholder;

    /**
     * @var array<string, array<string, string>>
     */
    protected $options;

    /**
     * @param array<string, array<string, array<string, string>|string>|string> $data
     */
    protected function __construct(array $data)
    {
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }
    }

    public static function fromXml(\DOMElement $element): CustomFieldType
    {
        return new self(self::parseSelect($element));
    }

    /**
     * @return array<string, string>
     */
    public function getPlaceholder(): array
    {
        return $this->placeholder;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array<string, array<string, array<string, string>|string>|string>
     */
    protected static function parseSelect(\DOMElement $element): array
    {
        $values = [];

        /** @var \DOMAttr $attribute */
        foreach ($element->attributes as $attribute) {
            $values[$attribute->name] = $attribute->value;
        }

        foreach ($element->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            if ($child->tagName === 'options') {
                $values[$child->tagName] = self::parseOptions($child);

                continue;
            }

            $values = self::mapTranslatedTag($child, $values);

            continue;
        }

        return $values;
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected static function parseOptions(\DOMElement $child): array
    {
        $values = [];

        foreach ($child->childNodes as $option) {
            if (!$option instanceof \DOMElement) {
                continue;
            }

            $option = self::parse($option, ['name']);
            /** @var string $key */
            $key = $option['value'];
            /** @var array<string, string> $names */
            $names = $option['name'];

            $values[$key] = $names;
        }

        return $values;
    }
}
