<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml;

use Symfony\Component\Config\Util\XmlUtils;

class ActionButton extends XmlElement
{
    /**
     * @var array<string, string>
     */
    protected $label;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var string
     */
    protected $view;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var bool
     */
    protected $openNewTab = false;

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

    public function getAction(): string
    {
        return $this->action;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getView(): string
    {
        return $this->view;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isOpenNewTab(): bool
    {
        return $this->openNewTab;
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
                if (!array_key_exists($child->tagName, $values)) {
                    $values[$child->tagName] = [];
                }

                // psalm would fail if it can't infer type from nested array
                /** @var array<string, string> $tagValues */
                $tagValues = $values[$child->tagName];
                $tagValues[self::getLocaleCodeFromElement($child)] = $child->nodeValue;
                $values[$child->tagName] = $tagValues;

                continue;
            }
        }

        return $values;
    }
}
