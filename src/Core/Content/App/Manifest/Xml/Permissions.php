<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml;

class Permissions extends XmlElement
{
    /**
     * @var array<string, array<string>>
     */
    protected $permissions;

    /**
     * @param array<string, array<string>> $permissions
     */
    private function __construct(array $permissions)
    {
        $this->permissions = $permissions;
    }

    public static function fromXml(\DOMElement $element): self
    {
        return new self(self::parsePermissions($element));
    }

    /**
     * @return array<string, array<string>>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return array<string, array<string>>
     */
    private static function parsePermissions(\DOMElement $element): array
    {
        $permissions = [];

        foreach ($element->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            $permissions[$child->nodeValue][] = $child->tagName;
        }

        return $permissions;
    }
}
