<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml;

class Metadata extends XmlElement
{
    /**
     * @var array<string, string>
     */
    protected $label;

    /**
     * @var array<string, string>
     */
    protected $description;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $copyright;

    /**
     * @var string
     */
    protected $license;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string|null
     */
    protected $icon;

    /**
     * @param array<string, string|array<string, string>> $data
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

    /**
     * @return array<string, string>
     */
    public function getDescription(): array
    {
        return $this->description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getCopyright(): string
    {
        return $this->copyright;
    }

    public function getLicense(): string
    {
        return $this->license;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @return array<string, string|array<string, string>>
     */
    private static function parse(\DOMElement $element): array
    {
        $values = [];

        foreach ($element->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            // translated
            if (in_array($child->tagName, ['label', 'description'], true)) {
                $values = self::mapTranslatedTag($child, $values);

                continue;
            }

            $values[$child->tagName] = $child->nodeValue;
        }

        return $values;
    }
}
