<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml;

use Symfony\Component\Config\Util\XmlUtils;

class Webhook extends XmlElement
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $event;

    /**
     * @param array<string, string|int|bool|null> $data
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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return array<string, string|int|bool|null>
     */
    private static function parse(\DOMElement $element): array
    {
        $values = [];

        /** @var \DOMAttr $attribute */
        foreach ($element->attributes as $attribute) {
            $values[$attribute->name] = XmlUtils::phpize($attribute->value);
        }

        return $values;
    }
}
