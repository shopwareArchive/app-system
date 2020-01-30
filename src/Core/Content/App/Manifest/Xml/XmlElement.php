<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml;

use Shopware\Core\Framework\Struct\Struct;

class XmlElement extends Struct
{
    private const FALLBACK_LOCALE = 'en-GB';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    protected static function getLocaleCodeFromElement(\DOMElement $element): string
    {
        return $element->getAttribute('lang') ?: self::FALLBACK_LOCALE;
    }
}
