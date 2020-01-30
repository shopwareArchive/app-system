<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml;

class Admin extends XmlElement
{
    /**
     * @var array<ActionButton>
     */
    protected $actionButtons;

    /**
     * @param array<ActionButton> $actionButtons
     */
    private function __construct(array $actionButtons)
    {
        $this->actionButtons = $actionButtons;
    }

    public static function fromXml(\DOMElement $element): self
    {
        return new self(self::parseActionButtons($element));
    }

    /**
     * @return array<ActionButton>
     */
    public function getActionButtons(): array
    {
        return $this->actionButtons;
    }

    /**
     * @return array<ActionButton>
     */
    private static function parseActionButtons(\DOMElement $element): array
    {
        $actionButtons = [];
        /** @var \DOMElement $actionButton */
        foreach ($element->getElementsByTagName('action-button') as $actionButton) {
            $actionButtons[] = ActionButton::fromXml($actionButton);
        }

        return $actionButtons;
    }
}
