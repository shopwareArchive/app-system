<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml;

use Symfony\Component\Config\Util\XmlUtils;

class Module extends XmlElement
{
    /**
     * @var array<string, string>
     */
    protected $label;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param array<string, string|int|bool|array<string, string>|null> $data
     */
    private function __construct(array $data)
    {
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }
    }

    public static function fromXml(\DOMElement $element): self
    {
        return new self(self::parse($element));
    }

    /**
     * @return array<string, string>
     */
    public function getLabel(): array
    {
        return $this->label;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, string|int|bool|array<string, string>|null>
     */
    private static function parse(\DOMElement $element): array
    {
        $values = [];

        /** @var \DOMAttr $attribute */
        foreach ($element->attributes as $attribute) {
            $values[$attribute->name] = XmlUtils::phpize($attribute->value);
        }

        foreach ($element->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            if ($child->tagName === 'label') {
                $values = self::mapTranslatedTag($child, $values);

                continue;
            }
        }

        return $values;
    }
}
