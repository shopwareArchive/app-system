<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml;

class CustomFields extends XmlElement
{
    /**
     * @var array<CustomFieldSet>
     */
    protected $customFieldSets = [];

    /**
     * @param array<CustomFieldSet> $customFieldSets
     */
    private function __construct(array $customFieldSets)
    {
        $this->customFieldSets = $customFieldSets;
    }

    public static function fromXml(\DOMElement $element): self
    {
        return new self(self::parseCustomFieldSets($element));
    }

    /**
     * @return array<CustomFieldSet>
     */
    public function getCustomFieldSets(): array
    {
        return $this->customFieldSets;
    }

    /**
     * @return array<CustomFieldSet>
     */
    private static function parseCustomFieldSets(\DOMElement $element): array
    {
        $customFieldSets = [];
        /** @var \DOMElement $customFieldSet */
        foreach ($element->getElementsByTagName('custom-field-set') as $customFieldSet) {
            $customFieldSets[] = CustomFieldSet::fromXml($customFieldSet);
        }

        return $customFieldSets;
    }
}
