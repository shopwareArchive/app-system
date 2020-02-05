<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes;

use Swag\SaasConnect\Core\Content\App\Manifest\Xml\XmlElement;
use Symfony\Component\Config\Util\XmlUtils;

abstract class CustomFieldType extends XmlElement
{
    protected const TRANSLATABLE_FIELDS = ['label', 'help-text'];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * @var int
     */
    protected $position = 1;

    /**
     * @var array<string, string>
     */
    protected $label;

    /**
     * @var array<string, string>
     */
    protected $helpText = [];

    abstract public static function fromXml(\DOMElement $element): self;

    public function getName(): string
    {
        return $this->name;
    }

    public function getRequired(): bool
    {
        return $this->required;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return array<string, string>
     */
    public function getLabel(): array
    {
        return $this->label;
    }

    /**
     * @return array<string, string>
     */
    public function getHelpText(): array
    {
        return $this->helpText;
    }

    /**
     * @param array<string> $translatableFields
     * @return array<string, string|int|float|bool|array<string, string>>
     */
    protected static function parse(\DOMElement $element, ?array $translatableFields = null): array
    {
        if (!$translatableFields) {
            $translatableFields = self::TRANSLATABLE_FIELDS;
        }

        $values = [];

        /** @var \DOMAttr $attribute */
        foreach ($element->attributes as $attribute) {
            $values[$attribute->name] = $attribute->value;
        }

        foreach ($element->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            // translated
            if (in_array($child->tagName, $translatableFields, true)) {
                $values = self::mapTranslatedTag($child, $values);

                continue;
            }

            $values[self::snakeCaseToCamelCase($child->tagName)] = XmlUtils::phpize($child->nodeValue);
        }

        return $values;
    }
}
