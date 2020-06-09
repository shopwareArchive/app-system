<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml;

use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes\CustomFieldType;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\CustomFieldTypes\CustomFieldTypeFactory;

class CustomFieldSet extends XmlElement
{
    /**
     * @var array<string, string>
     */
    protected $label;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array<string>
     */
    protected $relatedEntities;

    /**
     * @var array<CustomFieldType>
     */
    protected $fields = [];

    /**
     * @param array<string, string|int|bool|array<string, string>|array<string>|array<CustomFieldType>|null> $data
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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint
     */
    public function toEntityArray(string $appId): array
    {
        $relations = array_map(static function (string $entity) {
            return ['entityName' => $entity];
        }, $this->relatedEntities);

        $customFields = array_map(static function (CustomFieldType $field) {
            return $field->toEntityPayload();
        }, $this->fields);

        return [
            'name' => $this->name,
            'config' => [
                'label' => $this->label,
                'translated' => true,
            ],
            'relations' => $relations,
            'appId' => $appId,
            'customFields' => $customFields,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getLabel(): array
    {
        return $this->label;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string>
     */
    public function getRelatedEntities(): array
    {
        return $this->relatedEntities;
    }

    /**
     * @return array<CustomFieldType>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string, string|int|bool|array<string, string>|array<string>|array<CustomFieldType>|null>
     */
    private static function parse(\DOMElement $element): array
    {
        $values = [];
        foreach ($element->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            $values = self::parseChild($child, $values);
        }

        return $values;
    }

    /**
     * @param array<string, string|int|bool|array<string, string>|array<string>|array<CustomFieldType>|null> $values
     * @return array<string, string|int|bool|array<string, string>|array<string>|array<CustomFieldType>|null>
     */
    private static function parseChild(\DOMElement $child, array $values): array
    {
        if ($child->tagName === 'label') {
            return self::mapTranslatedTag($child, $values);
        }

        if ($child->tagName === 'fields') {
            $values[$child->tagName] = self::parseChildNodes(
                $child,
                static function (\DOMElement $element): CustomFieldType {
                    return CustomFieldTypeFactory::createFromXml($element);
                }
            );

            return $values;
        }

        if ($child->tagName === 'related-entities') {
            $values[self::snakeCaseToCamelCase($child->tagName)] = self::parseChildNodes(
                $child,
                static function (\DOMElement $element): string {
                    return $element->tagName;
                }
            );

            return $values;
        }

        $values[self::snakeCaseToCamelCase($child->tagName)] = $child->nodeValue;

        return $values;
    }
}
