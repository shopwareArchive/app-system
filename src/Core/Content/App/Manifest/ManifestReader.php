<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest;

use Shopware\Core\Framework\Util\XmlReader;
use Symfony\Component\Config\Util\XmlUtils;

class ManifestReader extends XmlReader
{
    private const FALLBACK_LOCALE = 'en-GB';

    /**
     * @var string
     */
    protected $xsdFile = __DIR__ . '/Schema/manifest-1.0.xsd';

    /**
     * @return array<string, array<string, string|array<string, string>|array<array<string, string|int|bool|array<string, string>|null>>>>
     */
    protected function parseFile(\DOMDocument $xml): array
    {
        /** @var \DOMElement $meta */
        $meta = $xml->getElementsByTagName('meta')->item(0);
        /** @var \DOMElement $admin */
        $admin = $xml->getElementsByTagName('admin')->item(0);

        return [
            'metadata' => $this->parseMetaData($meta),
            'admin' => $this->parseAdmin($admin),
        ];
    }

    /**
     * @return array<string, string|array<string, string>>
     */
    private function parseMetaData(\DOMElement $meta): array
    {
        return $this->mapTagValues(
            $meta,
            ['label', 'description']
        );
    }

    /**
     * @return array<string, array<array<string, string|int|bool|array<string, string>|null>>>
     */
    private function parseAdmin(\DOMElement $admin): array
    {
        $actionButtons = [];
        /** @var \DOMElement $actionButton */
        foreach ($admin->getElementsByTagName('action-button') as $actionButton) {
            $actionButtons[] = array_merge(
                $this->mapTagValues($actionButton, ['label']),
                $this->mapAttributeValues($actionButton)
            );
        }

        return [
            'actionButtons' => $actionButtons,
        ];
    }

    /**
     * @param  array<string> $translatable
     * @return array<string, string|array<string, string>>
     */
    private function mapTagValues(\DOMElement $node, array $translatable = []): array
    {
        $values = [];

        foreach ($node->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            if (in_array($child->tagName, $translatable, true)) {
                if (!array_key_exists($child->tagName, $values)) {
                    $values[$child->tagName] = [];
                }

                // psalm would fail if it can't infer type from nested array
                /** @var array<string, string> $tagValues */
                $tagValues = $values[$child->tagName];
                $tagValues[$this->getLocaleCodeFromElement($child)] = $child->nodeValue;
                $values[$child->tagName] = $tagValues;

                continue;
            }

            $values[$child->tagName] = $child->nodeValue;
        }

        return $values;
    }

    /**
     * @return array<string, string|int|bool|null>
     */
    private function mapAttributeValues(\DOMElement $node): array
    {
        $values = [];

        /** @var \DOMAttr $attribute */
        foreach ($node->attributes as $attribute) {
            $values[$attribute->name] = XmlUtils::phpize($attribute->value);
        }

        return $values;
    }

    private function getLocaleCodeFromElement(\DOMElement $element): string
    {
        return $element->getAttribute('lang') ?: self::FALLBACK_LOCALE;
    }
}
